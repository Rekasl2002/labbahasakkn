<?php $bodyClass = 'has-sidebars'; ?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?= view('layout/partials/sidebar_left', ['role' => 'student']) ?>
<?= view('layout/partials/sidebar_right', ['role' => 'student']) ?>

<div class="dashboard-center dashboard-center-with-sidebars">
  <header class="pageHead">
    <div>
      <h1 style="margin:0">Beranda Siswa</h1>
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
    <div class="row between wrap gap" style="align-items:flex-end">
      <div>
        <h2 style="margin:0">Teks Materi</h2>
        <div class="muted tiny" style="margin-top:4px">
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
        <h2 style="margin:0">Materi <span id="materialTitleLabel" class="muted"></span></h2>
      </div>
      <button id="btnRefreshMaterial" class="btn" type="button">↻ Muat Ulang</button>
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

