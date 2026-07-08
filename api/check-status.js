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
  const { tid } = req.query;
  if (!tid) return res.status(400).json({ status: 'ERROR', message: 'ID não fornecido.' });

  try {
    const token = await getDiceToken();
    const r = await fetch(`${DICE_URL}/api/v2/payments/deposit/${encodeURIComponent(tid)}`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (!r.ok) {
      if (r.status === 401) { _token = null; _expiry = 0; }
      return res.status(r.status).json({ status: 'ERROR', message: 'Erro ao consultar pagamento.' });
    }

    const data = await r.json();
    return res.json({ status: data.status || data.state || 'PENDING' });
  } catch (err) {
    return res.status(500).json({ status: 'ERROR', message: err.message });
  }
}
