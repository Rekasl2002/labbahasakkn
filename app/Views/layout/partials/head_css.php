<?php
helper('settings');

$settings = lab_load_settings();
$branding = lab_app_branding($settings);
$appName = trim((string) ($branding['app_name'] ?? 'Lab Bahasa'));
if ($appName === '') {
    $appName = 'Lab Bahasa';
}

$request = service('request');
$path = '';
if (function_exists('uri_string')) {
    $path = trim((string) uri_string(), '/');
}
if ($path === '' && $request) {
    $path = trim((string) $request->getUri()->getPath(), '/');
}
if ($path !== '') {
    $indexPhpPrefix = 'index.php/';
    $prefixPos = stripos($path, $indexPhpPrefix);
    if ($prefixPos !== false) {
        $path = trim((string) substr($path, $prefixPos + strlen($indexPhpPrefix)), '/');
    } elseif (strcasecmp($path, 'index.php') === 0) {
        $path = '';
    }
}
$tab = trim((string) ($request ? $request->getGet('tab') : ''));
$materialsTab = trim((string) ($request ? $request->getGet('mat') : ''));

/**
 * Semua mapping judul tab ada di sini.
 * Format final: Nama Aplikasi | Nama Halaman
 */
$pageTitles = [
    '' => 'Masuk',
    'login' => 'Masuk',
    'login/student' => 'Menunggu Sesi',
    'about' => 'Tentang',
    'waiting' => 'Menunggu Sesi',
    'waiting/profile' => 'Menunggu Sesi',
    'admin' => 'Sesi Guru',
    'admin/session/end' => 'Rekap Sesi',
    'student' => 'Sesi Siswa',
    'student/settings' => 'Pengaturan Siswa',
    'errors/preview' => 'Pratinjau Galat',
];

$pageTitle = $pageTitles[$path] ?? '';

if (preg_match('#^admin/session/\d+/recap$#', $path) === 1) {
    $pageTitle = 'Rekap Sesi';
}

if ($path === 'admin/settings') {
    $tabTitles = [
        'branding' => 'Tampilan Aplikasi',
        'warning-sound' => 'Suara Peringatan',
        'tutorial' => 'Panduan Tutorial',
        'auto-detect' => 'Deteksi Otomatis Komputer',
        'password' => 'Ubah Kata Sandi Guru',
    ];

    if ($tab === 'materials') {
        $materialsTitles = [
            'list' => 'Manajemen Materi',
            'add' => 'Tambah Materi',
            'edit' => 'Ubah Materi',
        ];
        $pageTitle = $materialsTitles[$materialsTab] ?? 'Manajemen Materi';
    } else {
        $pageTitle = $tabTitles[$tab] ?? 'Pengaturan Guru';
    }
}

if ($pageTitle === '' && isset($title) && trim((string) $title) !== '') {
    $pageTitle = trim((string) $title);
}

$docTitle = $appName;
if ($pageTitle !== '' && strcasecmp($pageTitle, $appName) !== 0) {
    $docTitle = $appName . ' | ' . $pageTitle;
}
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($docTitle) ?></title>
<link rel="icon" href="<?= esc($branding['favicon_url']) ?>" type="image/x-icon">
<link rel="apple-touch-icon" href="<?= esc($branding['logo_url']) ?>">
<link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">

