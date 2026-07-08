import crypto from 'crypto';

const FB_PIXEL_ID = '1472294161134125';

function sha256(str) {
  return crypto.createHash('sha256').update(String(str).trim().toLowerCase()).digest('hex');
}

export default async function handler(req, res) {
  if (req.method !== 'POST') return res.status(405).end();

  const ev     = req.body || {};
  const status = (ev.status || ev.state || '').toUpperCase();

  if (status !== 'PAID' && status !== 'APPROVED') return res.sendStatus(200);

  const FB_CAPI_TOKEN = process.env.FB_CAPI_TOKEN || '';
  if (FB_CAPI_TOKEN) {
    try {
      const payer = ev.payer || ev.customer || {};
      const email = payer.email || '';
      const nameParts = (payer.name || '').trim().split(/\s+/);
      const clientIp = (req.headers['x-forwarded-for'] || '').split(',')[0].trim()
                     || req.headers['x-real-ip'] || '';

      const userData = {
        client_ip_address: clientIp,
        client_user_agent: req.headers['user-agent'] || '',
      };
      if (email)       userData.em = [sha256(email)];
      if (nameParts[0]) userData.fn = [sha256(nameParts[0])];
      if (nameParts[1]) userData.ln = [sha256(nameParts.slice(1).join(' '))];

      const digits = (payer.phone || payer.document || '').replace(/\D/g, '');
      if (digits.length >= 10) userData.ph = [sha256('55' + digits)];

      await fetch(`https://graph.facebook.com/v19.0/${FB_PIXEL_ID}/events`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          data: [{
            event_name:       'Purchase',
            event_time:       Math.floor(Date.now() / 1000),
            event_id:         String(ev.id || ev.payment_id || Date.now()),
            action_source:    'website',
            event_source_url: 'https://privacy-thalita.vercel.app/checkout/pagamento/',
            user_data:        userData,
            custom_data: {
              value:        parseFloat(ev.amount || 0),
              currency:     'BRL',
              content_ids:  ['thalita-xavier-' + String(ev.id || '')],
              content_name: ev.product_name || 'Privacy Thalita Xavier',
              content_type: 'product',
            },
          }],
          access_token: FB_CAPI_TOKEN,
        }),
      });
    } catch (e) {
      console.error('[CAPI webhook] erro:', e.message);
    }
  }

  return res.sendStatus(200);
}
