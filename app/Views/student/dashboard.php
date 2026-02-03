<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

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

  <div class="row gap wrap" style="align-items:center">
    <!-- Penting: btnMic diperlukan oleh student.js -->
    <button id="btnMic" type="button" class="btn" title="Aktif/nonaktif mic kamu">
      Mic: OFF
    </button>

    <a class="btn" href="/student/settings">⚙ Pengaturan</a>
  </div>
</div>

<div class="grid3" style="margin-top:14px">
  <!-- LEFT: PEERS -->
  <aside class="card">
    <h2 style="margin:0 0 8px">Teman Sesi</h2>
    <ul id="peersList" class="list"></ul>
    <p class="muted tiny" style="margin:10px 0 0">Update lewat event polling.</p>
  </aside>

  <!-- CENTER: VOICE + BROADCAST -->
  <section class="card">
    <h2 style="margin:0">Voice / Speaker</h2>

    <div class="row gap wrap" style="margin-top:10px; align-items:center">
      <button id="btnEnableAudio" type="button" class="ok" title="Klik sekali untuk mengaktifkan output audio browser">
        🔊 Aktifkan Speaker
      </button>
      <span id="audioStatus" class="muted" aria-live="polite">Tidak ada panggilan.</span>
    </div>

    <div class="row gap wrap" style="margin-top:10px; align-items:center">
      <label class="muted tiny" for="selMic" style="min-width:70px">Mic</label>
      <select id="selMic" style="flex:1;min-width:220px"></select>

      <label class="muted tiny" for="selSpk" style="min-width:70px">Speaker</label>
      <select id="selSpk" style="flex:1;min-width:220px"></select>
    </div>

    <audio id="remoteAudio" class="audioEl" controls playsinline></audio>

    <div class="muted tiny" style="margin-top:8px">
      Jika tidak bunyi: klik “Aktifkan Speaker”, lalu cek apakah speaker kamu dimute admin.
      Mic hanya bisa aktif jika browser memberi izin.
      <br>
      Catatan: mic/speaker WebRTC butuh HTTPS (atau localhost).
    </div>

    <hr>

    <h2 style="margin:0">Quick Broadcast</h2>
    <div id="broadcastBox" class="broadcastBox" style="margin-top:10px">
      <?= esc($state['broadcast_text'] ?? '') ?>
    </div>

    <noscript>
      <p class="danger">JavaScript wajib aktif untuk voice dan polling.</p>
    </noscript>
  </section>

  <!-- RIGHT: CHAT -->
  <aside class="card">
    <h2 style="margin:0 0 8px">Chat ke Admin</h2>

    <div id="chatLog" class="chatLog"></div>

    <div class="row gap" style="margin-top:10px; align-items:center">
      <input id="chatInput" placeholder="Ketik pesan..." style="flex:1" autocomplete="off">
      <button id="btnSendChat" type="button">Kirim</button>
    </div>

    <p class="muted tiny" style="margin:10px 0 0">
      Default: pesan private ke admin.
    </p>
  </aside>
</div>

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

<?= $this->endSection() ?>
