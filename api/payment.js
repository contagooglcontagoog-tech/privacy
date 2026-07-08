const DICE_URL = 'https://dev.use-dice.com';
let _token = null;
let _expiry = 0;

async function getDiceToken() {
  if (_token && Date.now() < _expiry) return _token;
  const res = await fetch(`${DICE_URL}/api/v1/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      client_id:     process.env.DICE_CLIENT_ID,
      client_secret: process.env.DICE_CLIENT_SECRET,
    }),
  });
  const data = await res.json();
  _token  = data.token || data.access_token;
  _expiry = Date.now() + 50 * 60 * 1000;
  return _token;
}

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'POST')
    return res.status(405).json({ success: false, error: 'Método não permitido.' });

  const BUMP_MAISA_PRICE   = 7.90;
  const BUMP_MELODY_PRICE  = 8.90;
  const BUMP_NICOLLE_PRICE = 8.90;

  const {
    base_product_name  = 'Privacy - Plano Mensal',
    base_product_price = '9.90',
    name, email, phone,
    bump_maisa   = '0',
    bump_melody  = '0',
    bump_nicolle = '0',
  } = req.body || {};

  if (!name || !email)
    return res.status(400).json({ success: false, error: 'Nome e e-mail são obrigatórios.' });

  let totalAmount = parseFloat(base_product_price) || 9.90;
  let productName = base_product_name;
  const addedBumps = [];

  if (bump_maisa   === '1') { totalAmount += BUMP_MAISA_PRICE;   addedBumps.push('Mel Maia'); }
  if (bump_melody  === '1') { totalAmount += BUMP_MELODY_PRICE;  addedBumps.push('Kamilinha'); }
  if (bump_nicolle === '1') { totalAmount += BUMP_NICOLLE_PRICE; addedBumps.push('Nicolle Ex'); }
  if (addedBumps.length) productName += ' + ' + addedBumps.join(' + ');

  totalAmount = Math.round(totalAmount * 100) / 100;

  try {
    const token = await getDiceToken();

    const webhookUrl = process.env.WEBHOOK_URL || '';

    const payload = {
      product_name: `Thalita Xavier — ${productName}`,
      amount:       totalAmount,
      payer: {
        name:  String(name).trim(),
        email: String(email).trim(),
        ...(phone ? { document: String(phone).replace(/\D/g, '') } : {}),
      },
      ...(webhookUrl ? { clientCallbackUrl: webhookUrl } : {}),
    };

    const diceRes = await fetch(`${DICE_URL}/api/v2/payments/deposit`, {
      method:  'POST',
      headers: {
        'Content-Type':  'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });

    const data = await diceRes.json();

    if (!diceRes.ok) {
      const msg = data.message || data.error || 'Erro ao gerar PIX.';
      if (diceRes.status === 401) { _token = null; _expiry = 0; }
      return res.status(400).json({ success: false, error: msg });
    }

    return res.json({
      success:       true,
      qrCodeText:    data.qr_code_text,
      transactionId: data.id || data.payment_id,
      amount:        totalAmount,
      productName,
    });
  } catch (err) {
    return res.status(500).json({ success: false, error: 'Erro interno: ' + err.message });
  }
}
