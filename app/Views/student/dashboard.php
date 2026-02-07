<?php $bodyClass = 'has-sidebars'; ?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?= view('layout/partials/sidebar_left', ['role' => 'student']) ?>
<?= view('layout/partials/sidebar_right', ['role' => 'student']) ?>

<div class="dashboard-center">
  <header class="pageHead">
    <div>
      <h1 style="margin:0">Dashboard Siswa</h1>
      <p class="muted" style="margin:6px 0 0">
        Materi, chat ke admin, dan voice (WebRTC).
      </p>
    </div>
  </header>

  <div class="card row between wrap gap" style="align-items:center">
    <div>
      <div style="font-weight:700">
        <?= esc($me['student_name'] ?? session('student_name')) ?>
        <span class="muted">(<?= esc($me['class_name'] ?? session('class_name')) ?>)</span>
      </div>
      <div class="muted tiny" style="margin-top:4px">
        Sesi: <?= esc($session['name'] ?? '-') ?>
      </div>
    </div>

  </div>

  <!-- CENTER: VOICE -->
  <section class="card" style="margin-top:14px">
    <h2 style="margin:0">Voice / Speaker</h2>



    <audio id="remoteAudio" class="audioEl" playsinline></audio>

    <div class="muted tiny" style="margin-top:8px">
      Jika tidak bunyi: klik “Aktifkan Audio” agar browser meminta izin audio, lalu cek apakah speaker kamu dimute admin.
      Mic/speaker bisa dikunci oleh admin.
      <br>
      Catatan: mic/speaker WebRTC butuh HTTPS (atau localhost).
    </div>

    <noscript>
      <p class="danger">JavaScript wajib aktif untuk voice dan polling.</p>
    </noscript>
  </section>

  <section class="card" style="margin-top:14px">
    <div class="row between wrap gap" style="align-items:flex-end">
      <div>
        <h2 style="margin:0">Teks Materi</h2>
        <div class="muted tiny" style="margin-top:4px">
          Menampilkan teks yang sedang dipilih.
        </div>
      </div>
    </div>

    <div style="margin-top:10px">
      <div id="teacherTextBox" class="broadcastBox muted">Belum ada teks.</div>
    </div>
  </section>

  <section class="card" style="margin-top:14px">
    <div class="row between wrap gap" style="align-items:flex-end">
      <div>
        <h2 style="margin:0">Materi</h2>
        <div class="muted tiny" style="margin-top:4px">Materi aktif dari admin akan muncul di sini.</div>
      </div>
      <button id="btnRefreshMaterial" class="btn" type="button">↻ Refresh</button>
    </div>

    <div id="materialViewer" class="materialViewer muted" style="margin-top:10px">
      Belum ada materi.
    </div>
  </section>

  <script>
    window.__LAB_ROLE__ = 'student';
    window.__LAB_PARTICIPANT_ID__ = <?= (int)($me['id'] ?? session('participant_id')) ?>;
    window.__LAB_RTC_CONFIG__ = { iceServers: [{ urls: ['stun:stun.l.google.com:19302'] }] };
    window.__LAB_ALLOW_INSECURE_MEDIA__ = <?= config('App')->allowInsecureMedia ? 'true' : 'false' ?>;
  </script>
  <script src="/assets/js/poll.js"></script>
  <script src="/assets/js/student.js"></script>
</div>

<?= $this->endSection() ?>
