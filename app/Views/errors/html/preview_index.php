<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview Halaman Error</title>
  <style>
    :root{
      --bg:#0f172a;
      --card:#111827;
      --text:#e5e7eb;
      --muted:#9ca3af;
      --accent:#22c55e;
      --radius:16px;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:20px;
      font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
      background:radial-gradient(900px 400px at 10% 0%, rgba(34,197,94,.12), transparent 60%), var(--bg);
      color:var(--text);
    }
    .card{
      width:100%;
      max-width:720px;
      background:var(--card);
      border-radius:var(--radius);
      padding:20px 22px;
      border:1px solid rgba(255,255,255,.08);
      box-shadow:0 18px 40px rgba(0,0,0,.35);
    }
    h1{margin:0 0 6px;font-size:1.4rem}
    p{margin:0 0 14px;color:var(--muted)}
    .env{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:6px 10px;
      border-radius:999px;
      background:rgba(34,197,94,.12);
      color:var(--accent);
      font-weight:700;
      font-size:.85rem;
      margin-bottom:14px;
    }
    ul{margin:0;padding-left:18px}
    li{margin:8px 0}
    a{
      color:#fff;
      text-decoration:none;
      border-bottom:1px dashed rgba(255,255,255,.3);
    }
    a:hover{color:var(--accent);border-bottom-color:var(--accent)}
    .note{margin-top:16px;font-size:.85rem;color:var(--muted)}
  </style>
</head>
<body>
  <div class="card">
    <h1>Preview Halaman Error</h1>
    <p>Pilih salah satu halaman di bawah untuk melihat tampilannya.</p>
    <div class="env">ENV: <?= esc($envName ?? 'unknown') ?></div>

    <ul>
      <?php foreach (($links ?? []) as $link) : ?>
        <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
      <?php endforeach; ?>
    </ul>

    <div class="note">Catatan: halaman preview ini hanya tersedia di non-production.</div>
  </div>
</body>
</html>
