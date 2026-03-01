<?php
helper('settings');

$settings = $settings ?? [];
$embed = !empty($embed);
$branding = lab_app_branding($settings);
$appNameValue = trim((string) ($settings['app_name'] ?? ($branding['app_name'] ?? 'Lab Bahasa')));
if ($appNameValue === '') {
    $appNameValue = 'Lab Bahasa';
}
$defaultLogoPath = lab_default_logo_path();
$activeLogoPath = trim((string) ($branding['logo_path'] ?? ''));
$configuredLogoPath = trim((string) ($settings['logo_path'] ?? ''));
$logoIsDefault = $activeLogoPath !== '' && $activeLogoPath === $defaultLogoPath;
$logoSourceText = $logoIsDefault
    ? 'Sumber saat ini: file bawaan aplikasi.'
    : ($configuredLogoPath !== '' ? 'Sumber saat ini: file kustom dari pengaturan.' : 'Sumber saat ini: file bawaan aplikasi.');
$defaultFaviconPath = lab_default_favicon_path();
$activeFaviconPath = trim((string) ($branding['favicon_path'] ?? ''));
$configuredFaviconPath = trim((string) ($settings['favicon_path'] ?? ''));
$faviconIsDefault = $activeFaviconPath !== '' && $activeFaviconPath === $defaultFaviconPath;
$faviconSourceText = $faviconIsDefault
    ? 'Sumber saat ini: file bawaan aplikasi.'
    : ($configuredFaviconPath !== '' ? 'Sumber saat ini: file kustom dari pengaturan.' : 'Sumber saat ini: file bawaan aplikasi.');
?>

<section class="card">
  <h2 style="margin:0 0 6px">Tampilan</h2>
  <p class="muted tiny" style="margin:0 0 10px">
    Atur nama aplikasi, logo tampilan, dan ikon kecil pada halaman web.
  </p>

  <div class="brandingPreview">
    <div class="brandingPreviewCard">
      <div class="brandingPreviewTitle">Logo Saat Ini</div>
      <img src="<?= esc($branding['logo_url']) ?>" alt="Logo aplikasi saat ini" class="brandingPreviewImage">
    </div>
    <div class="brandingPreviewCard">
      <div class="brandingPreviewTitle">Ikon Halaman Web Saat Ini</div>
      <img src="<?= esc($branding['favicon_url']) ?>" alt="Ikon laman saat ini" class="brandingPreviewImage brandingPreviewImageSmall">
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

    <label>Logo aplikasi</label>
    <input
      type="file"
      name="app_logo"
      accept=".png,.jpg,.jpeg,.webp,.svg,.ico,image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon,image/vnd.microsoft.icon"
    >
    <p class="muted tiny" style="margin:6px 0 0"><?= esc($logoSourceText) ?></p>
    <?php if ($activeLogoPath !== ''): ?>
      <p class="muted tiny" style="margin:4px 0 0">Berkas aktif: <?= esc(basename($activeLogoPath)) ?></p>
    <?php endif; ?>
    <label class="muted tiny" style="display:flex; align-items:center; gap:8px; margin-top:8px">
      <input type="checkbox" name="app_logo_reset" value="1" style="width:auto; margin:0; padding:0">
      Gunakan kembali logo bawaan aplikasi
    </label>

    <label>Ubah Ikon Halaman Web</label>
    <input
      type="file"
      name="app_favicon"
      accept=".png,.jpg,.jpeg,.webp,.ico,image/png,image/jpeg,image/webp,image/x-icon,image/vnd.microsoft.icon"
    >
    <p class="muted tiny" style="margin:6px 0 0"><?= esc($faviconSourceText) ?></p>
    <?php if ($activeFaviconPath !== ''): ?>
      <p class="muted tiny" style="margin:4px 0 0">Berkas aktif: <?= esc(basename($activeFaviconPath)) ?></p>
    <?php endif; ?>
    <label class="muted tiny" style="display:flex; align-items:center; gap:8px; margin-top:8px">
      <input type="checkbox" name="app_favicon_reset" value="1" style="width:auto; margin:0; padding:0">
      Gunakan kembali ikon bawaan aplikasi
    </label>

    <p class="muted tiny brandingHint">
      Disarankan ukuran ikon 32x32 atau 64x64 piksel. Format yang didukung: PNG, JPG, WEBP, SVG, ICO (maksimal 2MB).
    </p>

    <button type="submit" class="ok" style="margin-top:10px">Simpan Tampilan</button>
  </form>
</section>

