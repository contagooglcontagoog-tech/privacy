export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, error: 'Método não permitido.' });
  }

  const BUMP_MAISA_PRICE   = 7.90;
  const BUMP_MELODY_PRICE  = 8.90;
  const BUMP_NICOLLE_PRICE = 8.90;

  const {
    base_product_name  = 'Privacy - Plano Mensal',
    base_product_price = '9.90',
    name, email, phone,
    cpf            = '',
    external_id    = '',
    bump_maisa     = '0',
    bump_melody    = '0',
    bump_nicolle   = '0',
  } = req.body || {};

  let totalAmount = parseFloat(base_product_price) || 9.90;
  let productName = base_product_name;
  const addedBumps = [];

  if (bump_maisa   === '1') { totalAmount += BUMP_MAISA_PRICE;   addedBumps.push('Maisa Silva'); }
  if (bump_melody  === '1') { totalAmount += BUMP_MELODY_PRICE;  addedBumps.push('MC Melody'); }
  if (bump_nicolle === '1') { totalAmount += BUMP_NICOLLE_PRICE; addedBumps.push('Nicolle Ex do Gordão'); }
  if (addedBumps.length) productName += ' + ' + addedBumps.join(' + ');

  const cleanPhone = String(phone || '').replace(/\D/g, '');
  const fullPhone  = cleanPhone.startsWith('55') ? cleanPhone : '55' + cleanPhone;
  const cleanCpf   = String(cpf || '').replace(/\D/g, '');
  const extId      = String(external_id || cleanCpf || Date.now());

  const pixgoKey = process.env.PIXGO_API_KEY;
  if (!pixgoKey) {
    return res.status(500).json({ success: false, error: 'Chave da API não configurada.' });
  }

  const payload = {
    amount:         Math.round(totalAmount * 100) / 100,
    description:    productName,
    customer_name:  String(name  || ''),
    customer_cpf:   cleanCpf,
    customer_email: String(email || ''),
    customer_phone: fullPhone,
    external_id:    extId,
  };

  try {
    const pixgoRes = await fetch('https://pixgo.org/api/v1/payment/create', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-API-Key': pixgoKey },
      body:    JSON.stringify(payload),
    });

    const data = await pixgoRes.json();

    if (pixgoRes.status !== 201 || !data.success) {
      return res.status(400).json({
        success: false,
        error:   'Erro ao gerar PIX: ' + (data.message || 'Erro desconhecido'),
      });
    }

    return res.json({
      success:       true,
      qrCodeText:    data.data.qr_code,
      transactionId: data.data.payment_id,
      amount:        totalAmount,
      productName,
    });
  } catch (err) {
    return res.status(500).json({ success: false, error: 'Erro interno: ' + err.message });
  }
}
