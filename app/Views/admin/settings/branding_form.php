<?php
helper('settings');

$settings = $settings ?? [];
$embed = !empty($embed);
$branding = lab_app_branding($settings);
$appNameValue = trim((string) ($settings['app_name'] ?? ($branding['app_name'] ?? 'Lab Bahasa')));
if ($appNameValue === '') {
    $appNameValue = 'Lab Bahasa';
}
?>

<section class="card">
  <h2 style="margin:0 0 6px">Branding Aplikasi</h2>
  <p class="muted tiny" style="margin:0 0 10px">
    Atur nama aplikasi, logo tampilan, dan ikon tab browser (favicon).
  </p>

  <div class="brandingPreview">
    <div class="brandingPreviewCard">
      <div class="brandingPreviewTitle">Logo Saat Ini</div>
      <img src="<?= esc($branding['logo_url']) ?>" alt="Logo aplikasi saat ini" class="brandingPreviewImage">
    </div>
    <div class="brandingPreviewCard">
      <div class="brandingPreviewTitle">Favicon Saat Ini</div>
      <img src="<?= esc($branding['favicon_url']) ?>" alt="Favicon saat ini" class="brandingPreviewImage brandingPreviewImageSmall">
    </div>
  </div>

  <form method="post" action="/admin/settings" enctype="multipart/form-data">
    <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
    <?php if ($embed): ?>
      <input type="hidden" name="embed" value="1">
    <?php endif; ?>
    <input type="hidden" name="setting_group" value="branding">

    <label>Nama aplikasi</label>
    <input name="app_name" maxlength="80" required value="<?= esc($appNameValue) ?>">

    <label>Logo aplikasi (opsional, jika ingin diganti)</label>
    <input
      type="file"
      name="app_logo"
      accept=".png,.jpg,.jpeg,.webp,.svg,.ico,image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon,image/vnd.microsoft.icon"
    >

    <label>Favicon tab browser (opsional)</label>
    <input
      type="file"
      name="app_favicon"
      accept=".png,.jpg,.jpeg,.webp,.ico,image/png,image/jpeg,image/webp,image/x-icon,image/vnd.microsoft.icon"
    >

    <p class="muted tiny brandingHint">
      Disarankan ukuran favicon 32x32 atau 64x64 piksel. Format yang didukung: PNG, JPG, WEBP, SVG, ICO (maks 2MB).
    </p>

    <button type="submit" class="ok" style="margin-top:10px">Simpan Branding</button>
  </form>
</section>
