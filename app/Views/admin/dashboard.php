<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

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

    <div class="row wrap gap" style="align-items:center">
      <div class="row wrap gap" style="align-items:center">
        <button id="btnMuteAllMic" type="button" class="danger">🔇 Mute Mic Semua</button>
        <button id="btnUnmuteAllMic" type="button" class="ok">🎙️ Unmute Mic Semua</button>
      </div>

      <div class="row wrap gap" style="align-items:center">
        <button id="btnMuteAllSpk" type="button" class="danger">🔈 Mute Speaker Semua</button>
        <button id="btnUnmuteAllSpk" type="button" class="ok">🔊 Unmute Speaker Semua</button>
      </div>

      <div style="flex:1"></div>

      <a class="btn" href="/admin/materials">📚 Manajemen Materi</a>
    </div>

    <div class="row gap wrap" style="margin-top:12px; align-items:center">
      <label for="broadcastText" class="muted tiny" style="min-width:110px">Quick Broadcast</label>
      <input
        id="broadcastText"
        placeholder="Kata/kalimat singkat untuk ditampilkan ke semua siswa..."
        value="<?= esc($state['broadcast_text'] ?? '') ?>"
        maxlength="255"
        autocomplete="off"
        style="flex:1;min-width:260px"
      >
      <button id="btnBroadcastText" type="button">📢 Broadcast</button>
    </div>

    <p class="muted tiny" style="margin:10px 0 0">
      Broadcast ini untuk teks singkat. Voice memakai tombol Call per siswa.
    </p>
  <?php endif; ?>
</div>

<?php if ($activeSession): ?>
  <div class="grid2" style="margin-top:14px">
    <!-- LEFT: PARTICIPANTS + VOICE -->
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
          <span id="callStatus" class="muted" aria-live="polite">Idle</span>
        </div>

        <div class="row gap wrap" style="align-items:center">
          <button id="btnAdminMic" class="ok" type="button" title="Aktif/nonaktif mic admin">🎙️ Mic Admin: ON</button>
          <button id="btnHangupCall" class="danger" type="button" disabled title="Akhiri panggilan">☎ Hangup</button>
        </div>
      </div>

      <div class="row gap wrap" style="margin-top:10px; align-items:center">
        <label class="muted tiny" for="selAdminMic" style="min-width:90px">Mic Admin</label>
        <select id="selAdminMic" style="flex:1;min-width:220px"></select>

        <label class="muted tiny" for="selAdminSpk" style="min-width:90px">Speaker Admin</label>
        <select id="selAdminSpk" style="flex:1;min-width:220px"></select>
      </div>

      <audio id="adminRemoteAudio" class="audioEl" controls playsinline></audio>

      <div class="muted tiny" style="margin-top:8px">
        Jika audio tidak keluar: klik tombol Call pada peserta, lalu pastikan izin Microphone/Speaker di browser tidak diblok.
      </div>

      <hr>

      <noscript>
        <p class="danger">JavaScript wajib aktif untuk polling peserta dan voice.</p>
      </noscript>

      <div id="participantsGrid" class="gridCards"></div>

      <div class="muted tiny" style="margin-top:10px">
        Tips: kalau peserta banyak, batasi Call hanya ke satu siswa sekaligus agar perangkat admin tidak berat.
      </div>
    </section>

    <!-- RIGHT: CHAT + MATERIAL -->
    <section class="card">
      <div class="row between wrap gap" style="align-items:flex-end">
        <div>
          <h2 style="margin:0">Chat</h2>
          <div class="muted tiny" style="margin-top:4px">
            Public untuk semua, Private untuk siswa tertentu.
          </div>
        </div>
      </div>

      <div class="row gap wrap" style="margin-top:10px; align-items:center">
        <label class="muted tiny" for="chatMode" style="min-width:60px">Mode</label>
        <select id="chatMode">
          <option value="public">Public</option>
          <option value="private_student">Private ke siswa terpilih</option>
        </select>

        <label class="muted tiny" for="privateTarget" style="min-width:86px">Target</label>
        <select id="privateTarget" disabled></select>
      </div>

      <div id="chatLog" class="chatLog" style="margin-top:10px"></div>

      <div class="row gap" style="margin-top:10px; align-items:center">
        <input id="chatInput" placeholder="Ketik pesan..." style="flex:1" autocomplete="off">
        <button id="btnSendChat" type="button">Kirim</button>
      </div>

      <div class="muted tiny" style="margin-top:8px">
        Siswa juga bisa kirim private ke admin dari UI mereka.
      </div>

      <hr>

      <div class="row between wrap gap" style="align-items:flex-end">
        <div>
          <h2 style="margin:0">Materi Aktif</h2>
          <div class="muted tiny" style="margin-top:4px">Ditampilkan di halaman siswa.</div>
        </div>
        <button id="btnRefreshMaterial" class="btn" type="button">↻ Refresh</button>
      </div>

      <div id="currentMaterialBox" class="materialBox muted" style="margin-top:10px">Belum ada materi.</div>
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

<?= $this->endSection() ?>
