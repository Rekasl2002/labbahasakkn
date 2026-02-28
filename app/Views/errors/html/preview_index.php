<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview Halaman Error</title>
  <style>
    *{box-sizing:border-box}
    body{
      margin:0;
      min-height:100vh;
      padding:24px 16px;
      font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
      background:#f3faf5;
      color:#163229;
    }
    .auth-choose{
      max-width:960px;
      margin:32px auto;
      padding:24px 16px;
      background:#e8f5e8;
      border:1px solid #cfe6d8;
      border-radius:16px;
      color:#163229;
    }
    .auth-grid{
      display:grid;
      gap:36px;
      max-width:800px;
      margin:0 auto;
    }
    .auth-choose .auth-card{
      padding:32px 26px;
      border-radius:12px;
      box-shadow:0 6px 18px rgba(28,139,142,.10);
      text-align:left;
      min-width:0;
      background:#ffffff;
      border:1px solid #d4e8dd;
    }
    .auth-title{
      font-size:26px;
      margin:0 0 10px;
      font-weight:700;
      color:#163229;
    }
    .hint-muted{
      color:#5f7a6f;
      font-size:14px;
      margin:0 0 14px;
    }
    .muted.tiny{
      font-size:12px;
      color:#5d6a64;
      margin-top:12px;
    }
    .chip{
      display:inline-flex;
      align-items:center;
      padding:6px 10px;
      border-radius:999px;
      background:#f3fbf7;
      border:1px solid #cde9dc;
      color:#2e7d32;
      font-size:12px;
      font-weight:700;
      margin-bottom:10px;
    }
    ul{margin:0;padding-left:18px}
    li{margin:8px 0}
    a{
      color:#1f6f54;
      text-decoration:none;
      border-bottom:1px dashed rgba(31,111,84,.35);
      font-weight:600;
    }
    a:hover{color:#2e7d32;border-bottom-color:#2e7d32}
  </style>
</head>
<body>
  <div class="auth-choose">
    <div class="auth-grid">
      <section class="card auth-card">
        <h1 class="auth-title">Preview Halaman Error</h1>
        <p class="hint-muted">Pilih salah satu halaman di bawah untuk melihat tampilannya.</p>
        <div class="chip">ENV: <?= esc($envName ?? 'unknown') ?></div>

        <ul>
          <?php foreach (($links ?? []) as $link) : ?>
            <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>

        <p class="muted tiny">Catatan: halaman preview ini hanya tersedia di non-production.</p>
      </section>
    </div>
  </div>
</body>
</html>
