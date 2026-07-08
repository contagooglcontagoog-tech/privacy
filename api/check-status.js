export default async function handler(req, res) {
  const { tid } = req.query;

  if (!tid) return res.json({ status: 'ERROR', message: 'ID não fornecido.' });

  const cleanTid = tid.replace(/[^a-zA-Z0-9_\-]/g, '');
  const pixgoKey = process.env.PIXGO_API_KEY;

  if (!pixgoKey) {
    return res.status(500).json({ status: 'ERROR', message: 'Chave da API não configurada.' });
  }

  try {
    const r = await fetch(`https://pixgo.org/api/v1/payment/${cleanTid}/status`, {
      headers: { 'X-API-Key': pixgoKey },
    });
    const data = await r.json();
    return res.json(data);
  } catch (err) {
    return res.status(500).json({ status: 'ERROR', message: err.message });
  }
}
