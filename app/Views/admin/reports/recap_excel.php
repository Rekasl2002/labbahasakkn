<?php
$sessionName = trim((string) ($session['name'] ?? ''));
$sessionName = $sessionName !== '' ? $sessionName : 'Sesi tanpa nama';
$isActive = (int) ($session['is_active'] ?? 0) === 1;
$endedAtText = trim((string) ($session['ended_at'] ?? ''));
$endedAtText = $endedAtText !== '' ? $endedAtText : ($isActive ? 'Sedang berlangsung' : '-');
$deadlineAtText = trim((string) ($session['deadline_at'] ?? ''));
$deadlineAtText = $deadlineAtText !== '' ? $deadlineAtText : '-';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="application/vnd.ms-excel; charset=UTF-8">
  <title>Rekap Sesi - <?= esc($sessionName) ?></title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
    h1 { margin: 0 0 10px; font-size: 20px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #444; padding: 6px 8px; text-align: left; vertical-align: top; }
    th { background: #e9edf3; }
    .meta td:first-child { width: 220px; font-weight: 700; }
    .spacer { height: 12px; }
  </style>
</head>
<body>
  <h1>Laporan Rekap Sesi</h1>

  <table class="meta">
    <tbody>
      <tr><td>Nama Sesi</td><td><?= esc($sessionName) ?></td></tr>
      <tr><td>Status</td><td><?= $isActive ? 'SESI AKTIF' : 'SESI SELESAI' ?></td></tr>
      <tr><td>Mulai</td><td><?= esc((string) ($session['started_at'] ?? '-')) ?></td></tr>
      <tr><td>Selesai</td><td><?= esc($endedAtText) ?></td></tr>
      <tr><td>Batas Sesi</td><td><?= esc((string) ($limitText ?? '-')) ?></td></tr>
      <tr><td>Deadline</td><td><?= esc($deadlineAtText) ?></td></tr>
      <tr><td>Durasi</td><td><?= esc((string) ($durationText ?? '-')) ?></td></tr>
      <tr><td>Jumlah Peserta</td><td><?= count($participants) ?></td></tr>
      <tr><td>Total Chat</td><td><?= (int) $messagesCount ?></td></tr>
      <tr><td>Jumlah Materi Digunakan</td><td><?= (int) $materialsUsed ?></td></tr>
      <tr><td>Waktu Generate Laporan</td><td><?= esc((string) ($generatedAt ?? '-')) ?></td></tr>
    </tbody>
  </table>

  <div class="spacer"></div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Nama</th>
        <th>Kelas</th>
        <th>Komputer</th>
        <th>IP</th>
        <th>Join</th>
        <th>Last Seen</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($participants as $i => $p): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= esc((string) ($p['student_name'] ?? '-')) ?></td>
          <td><?= esc((string) ($p['class_name'] ?? '-')) ?></td>
          <td><?= esc((string) ($p['device_label'] ?? '-')) ?></td>
          <td><?= esc((string) ($p['ip_address'] ?? '-')) ?></td>
          <td><?= esc((string) ($p['joined_at'] ?? '-')) ?></td>
          <td><?= esc((string) ($p['last_seen_at'] ?? '-')) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($participants)): ?>
        <tr>
          <td colspan="7">Tidak ada data peserta.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
