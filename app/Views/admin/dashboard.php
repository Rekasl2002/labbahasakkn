<?php $bodyClass = $activeSession ? 'has-sidebars' : ''; ?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php if ($activeSession): ?>
  <?= view('layout/partials/sidebar_left', ['role' => 'admin', 'state' => $state]) ?>
  <?= view('layout/partials/sidebar_right', ['role' => 'admin']) ?>
<?php endif; ?>

<div class="dashboard-center">

<header class="pageHead">
  <div>
    <h1 style="margin:0">Admin Dashboard</h1>
    <p class="muted" style="margin:6px 0 0">
      Kendalikan sesi, peserta, chat, materi, dan voice (WebRTC).
    </p>
  </div>
</header>

<div class="card">
  <?php if (!$activeSession): ?>
    <div class="row between wrap gap">
      <div>
        <h2 style="margin:0 0 6px">Sesi belum aktif</h2>
        <p class="muted" style="margin:0">
          Mulai sesi agar siswa bisa login dan terhubung.
        </p>
      </div>
    </div>

    <hr>

    <form method="post" action="/admin/session/start" class="row wrap gap" style="align-items:center">
      <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
      <input
        name="name"
        placeholder="Nama sesi (opsional), contoh: Lab Bahasa 7B"
        maxlength="80"
        autocomplete="off"
        style="flex:1;min-width:260px"
      >
      <button type="submit" class="ok">▶ Mulai Sesi</button>
    </form>

    <p class="muted tiny" style="margin:10px 0 0">
      Tips: untuk fitur mic/speaker di browser, gunakan HTTPS (atau localhost).
    </p>

  <?php else: ?>
    <div class="row between wrap gap" style="align-items:center">
      <div>
        <div class="row wrap gap" style="align-items:center">
          <span class="badge ok">SESI AKTIF</span>
          <div>
            <div style="font-weight:700">
              <?= esc($activeSession['name'] ?: 'Sesi tanpa nama') ?>
            </div>
            <div class="muted tiny">
              Mulai: <?= esc($activeSession['started_at']) ?>
            </div>
          </div>
        </div>
      </div>

      <form method="post" action="/admin/session/end" onsubmit="return confirm('Tutup sesi dan buat rekap?')">
        <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
        <button class="danger" type="submit">■ Tutup Sesi & Rekap</button>
      </form>
    </div>

    <hr>

  <?php endif; ?>
</div>

<?php if ($activeSession): ?>
  <div style="margin-top:14px">
    <!-- CENTER: PARTICIPANTS + VOICE -->
    <section class="card">
      <div class="row between wrap gap" style="align-items:flex-end">
        <div>
          <h2 style="margin:0">Komputer / Peserta</h2>
          <div class="muted tiny" style="margin-top:4px">
            Online ditentukan dari heartbeat (≤ 35 detik).
          </div>
        </div>
        <div class="muted tiny" style="text-align:right">
          Voice: WebRTC (butuh HTTPS / localhost)
        </div>
      </div>

      <hr>

      <!-- Voice panel -->
      <div class="callBar row between wrap gap" style="align-items:center">
        <div class="row gap wrap" style="align-items:center">
          <span class="badge">VOICE</span>
          <span id="callStatus" class="muted" aria-live="polite">Voice room: idle</span>
        </div>

        <div class="row gap wrap" style="align-items:center">
          <button id="btnHangupCall" class="danger" type="button" disabled title="Putuskan semua koneksi voice">☎ Putuskan Semua</button>
        </div>
      </div>

      <audio id="adminRemoteAudio" class="audioEl" playsinline></audio>

      <div class="muted tiny" style="margin-top:8px">
        Jika audio tidak keluar: klik “Aktifkan Audio, pastikan “Speaker Admin” ON, dan izin audio di browser tidak diblok.
      </div>

      <hr>

      <noscript>
        <p class="danger">JavaScript wajib aktif untuk polling peserta dan voice.</p>
      </noscript>

      <div id="participantsGrid" class="gridCards"></div>

      <div class="muted tiny" style="margin-top:10px">
        Tips: jika peserta banyak, gunakan mute mic siswa untuk mengurangi beban audio di perangkat admin.
      </div>
    </section>
  </div>

  <script>
    window.__LAB_ROLE__ = 'admin';
    window.__LAB_BASE__ = '';
    window.__LAB_RTC_CONFIG__ = { iceServers: [{ urls: ['stun:stun.l.google.com:19302'] }] };
    window.__LAB_ALLOW_INSECURE_MEDIA__ = <?= config('App')->allowInsecureMedia ? 'true' : 'false' ?>;
  </script>
  <script src="/assets/js/poll.js"></script>
  <script src="/assets/js/admin.js"></script>
<?php endif; ?>

</div>

<?= $this->endSection() ?>
