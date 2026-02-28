<?php
if (function_exists('helper')) {
  helper('url');
}

$settingFunc = function_exists('setting') ? 'setting' : null;
$settingValue = function (string $key, string $default, ?string $group = null) use ($settingFunc): string {
  if ($settingFunc === null) {
    return $default;
  }

  try {
    if ($group !== null) {
      return (string) call_user_func($settingFunc, $key, $default, $group);
    }

    return (string) call_user_func($settingFunc, $key, $default);
  } catch (\Throwable $t) {
    try {
      return (string) call_user_func($settingFunc, $key);
    } catch (\Throwable $t) {
      return $default;
    }
  }
};

$baseUrl = function (string $path = ''): string {
  if (function_exists('base_url')) {
    return (string) base_url($path);
  }

  $path = ltrim($path, '/');
  return '/' . $path;
};

$normalizeText = function ($value, string $fallback = ''): string {
  if ($value === null) {
    return $fallback;
  }

  if (is_string($value)) {
    return $value;
  }

  if (is_int($value) || is_float($value) || is_bool($value)) {
    return (string) $value;
  }

  if (is_array($value)) {
    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded !== false) {
      return $encoded;
    }

    return trim(print_r($value, true));
  }

  if (is_object($value)) {
    if (method_exists($value, '__toString')) {
      return (string) $value;
    }

    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded !== false) {
      return $encoded;
    }

    return get_class($value);
  }

  return $fallback;
};

$appName = $settingValue('app_name', 'Lab Bahasa', 'general');
$faviconPath = $settingValue('favicon_path', 'assets/images/favicon.ico', 'branding');
$faviconUrl = $baseUrl($faviconPath);

$exceptionObj = $exception ?? null;
$httpCode = (int) ($statusCode ?? $status_code ?? 500);
if ($httpCode <= 0) {
  $httpCode = 500;
}

$errorType = $normalizeText(
  $type ?? ($exceptionObj ? get_class($exceptionObj) : 'Galat'),
  'Galat'
);

$headingText = $normalizeText(
  $heading ?? ($title ?? 'Terjadi Kesalahan Sistem'),
  'Terjadi Kesalahan Sistem'
);

$messageText = $normalizeText(
  $message ?? ($exceptionObj ? $exceptionObj->getMessage() : 'Terjadi kesalahan saat memproses permintaan.'),
  'Terjadi kesalahan saat memproses permintaan.'
);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($appName) ?> - Galat</title>
  <link rel="icon" href="<?= esc($faviconUrl) ?>" type="image/x-icon">
  <style>
    *{box-sizing:border-box}
    body{
      margin:0;
      min-height:100vh;
      padding:24px 16px;
      font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
      background:#f3faf5;
      color:#163229;
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .auth-choose{
      width:100%;
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
    .auth-avatar{
      width:112px;
      height:112px;
      border-radius:50%;
      border:8px solid #cde9dc;
      background:#f3fbf7;
      display:flex;
      margin:0 auto 14px;
      align-items:center;
      justify-content:center;
      color:#2e7d32;
      font-size:34px;
      font-weight:800;
    }
    .auth-title{
      text-align:center;
      font-size:24px;
      margin:6px 0 8px;
      font-weight:700;
      color:#163229;
    }
    .hint-muted{
      text-align:center;
      color:#5f7a6f;
      font-size:14px;
      margin:10px 0;
      word-break:break-word;
    }
    .muted.tiny{
      font-size:12px;
      color:#5d6a64;
      margin-top:12px;
      text-align:center;
      word-break:break-word;
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
      margin:0 auto 14px;
    }
    .chip-wrap{text-align:center}
    .action-row{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:14px}
    .btn-green{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:#3ba776;
      color:#113126;
      border:none;
      padding:10px 18px;
      border-radius:8px;
      cursor:pointer;
      text-decoration:none;
      font-weight:600;
    }
    .btn-soft{background:#d9efe4;color:#21463a}
  </style>
</head>
<body>
  <div class="auth-choose">
    <div class="auth-grid">
      <section class="card auth-card">
        <div class="auth-avatar">500</div>
        <div class="chip-wrap"><span class="chip">Kode Galat <?= esc((string) $httpCode) ?></span></div>
        <h1 class="auth-title"><?= esc($headingText) ?></h1>
        <p class="hint-muted"><?= esc(strip_tags($messageText)) ?></p>
        <p class="muted tiny">Jenis galat: <?= esc($errorType) ?></p>

        <div class="action-row">
          <a class="btn-green" href="<?= esc($baseUrl('/')) ?>">Ke Beranda</a>
          <button class="btn-green btn-soft" type="button" onclick="history.back()">Kembali</button>
        </div>

        <p class="muted tiny">&copy; <?= date('Y') ?> <?= esc($appName) ?></p>
      </section>
    </div>
  </div>
</body>
</html>
