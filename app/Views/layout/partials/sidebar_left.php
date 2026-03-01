<?php
helper('settings');
$role = $role ?? '';
$roleLabel = $role === 'admin' ? 'Guru' : 'Siswa';
$userName = $role === 'admin'
    ? (session('admin_username') ?: 'Guru')
    : (session('student_name') ?: 'Siswa');
$userMeta = $role === 'student' ? (session('class_name') ?: '') : '';
$appName = (lab_app_branding()['app_name'] ?? 'Lab Bahasa');
$tutorialItems = lab_tutorial_items_for_role($role === 'admin' ? 'admin' : 'student');
?>

<?php if ($role === 'student'): ?>
  <aside class="lab-sidebar lab-sidebar-left lab-sidebar-with-audio">
    <div class="lab-sidebar-inner">
      <section class="card">
        <h2 style="margin:0 0 8px">Teman Sesi</h2>
        <ul id="peersList" class="list"></ul>
      </section>
      <?php if (!empty($tutorialItems)): ?>
        <section class="card" style="margin-top:10px">
          <h2 style="margin:0 0 8px">Panduan Aplikasi</h2>
          <?php foreach ($tutorialItems as $item): ?>
            <a
              href="#"
              class="btn tiny"
              style="margin-right:6px; margin-bottom:6px"
              data-preview-url="<?= esc($item['url'] ?? '') ?>"
              data-preview-title="<?= esc($item['label'] ?? 'Panduan') ?>"
            >
              <?= esc($item['label'] ?? 'Panduan') ?>
            </a>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>
    </div>
    <section class="lab-sidebar-audio">
      <div class="lab-sidebar-audio-title">Audio</div>
      <div id="audioStatus" class="lab-audio-status muted" aria-live="polite">
        Tidak ada panggilan.
      </div>
      <div id="studentAudioIndicator" class="audioIndicator idle" aria-live="polite">
        <span class="dot"></span>
        <span class="text">Audio: menunggu/standby</span>
      </div>
      <button id="btnEnableAudio" type="button" class="ok" title="Klik sekali untuk mengaktifkan keluaran suara di peramban">
        🔊 Aktifkan Suara
      </button>
      <div class="lab-audio-group">
        <button id="btnMic" type="button" class="btn lab-audio-btn" title="Aktif/nonaktif mikrofon kamu">
          Mikrofon: Mati
        </button>
        <div class="lab-audio-dd" aria-hidden="true">
          <span class="lab-audio-caret">▾</span>
          <select id="selMic" aria-label="Pilih mikrofon"></select>
        </div>
      </div>
      <div class="lab-audio-group">
        <button id="btnSpk" type="button" class="btn lab-audio-btn" title="Aktif/nonaktif speaker kamu">
          Speaker: Hidup
        </button>
        <div class="lab-audio-dd" aria-hidden="true">
          <span class="lab-audio-caret">▾</span>
          <select id="selSpk" aria-label="Pilih speaker"></select>
        </div>
      </div>
      <div class="lab-audio-sliders">
        <div class="lab-audio-slider-row">
          <label class="lab-audio-slider-head" for="rngMicVol">
            <span>Volume Mikrofon</span>
            <span id="txtMicVol">100%</span>
          </label>
          <input id="rngMicVol" type="range" min="0" max="100" value="100" step="1" aria-label="Volume mikrofon siswa">
        </div>
        <div class="lab-audio-slider-row">
          <label class="lab-audio-slider-head" for="rngSpkVol">
            <span>Volume Speaker</span>
            <span id="txtSpkVol">100%</span>
          </label>
          <input id="rngSpkVol" type="range" min="0" max="100" value="100" step="1" aria-label="Volume speaker siswa">
        </div>
      </div>
      <audio id="remoteAudio" class="audioEl" playsinline></audio>
    </section>
  </aside>
<?php elseif ($role === 'admin'): ?>
  <aside class="lab-sidebar lab-sidebar-left lab-sidebar-with-audio">
    <div class="lab-sidebar-inner">
      <section class="card">
        <h2 style="margin:0 0 6px">Kontrol Cepat</h2>
        <div class="row wrap gap" style="align-items:center">
          <button id="btnMuteAllMic" type="button" class="danger">🔇 Matikan Mikrofon Semua</button>
          <button id="btnUnmuteAllMic" type="button" class="ok">🎙️ Nyalakan Mikrofon Semua</button>
        </div>

        <div class="row wrap gap" style="align-items:center; margin-top:8px">
          <button id="btnMuteAllSpk" type="button" class="danger">🔈 Matikan Speaker Semua</button>
          <button id="btnUnmuteAllSpk" type="button" class="ok">🔊 Nyalakan Speaker Semua</button>
        </div>

        <div class="row gap wrap" style="margin-top:10px; align-items:center">
          <span class="muted tiny" style="min-width:110px">Kontrol Siswa</span>
          <button id="btnAllowStudentMic" type="button" class="ok btn" title="Aktif/nonaktif izin siswa mengatur mikrofon">🎙️ Mikrofon Siswa: Boleh</button>
          <button id="btnAllowStudentSpk" type="button" class="ok btn" title="Aktif/nonaktif izin siswa mengatur speaker">🔊 Speaker Siswa: Boleh</button>
        </div>
      </section>
      <?php if (!empty($tutorialItems)): ?>
        <section class="card" style="margin-top:10px">
          <h2 style="margin:0 0 8px">Panduan Aplikasi</h2>
          <?php foreach ($tutorialItems as $item): ?>
            <a
              href="#"
              class="btn tiny"
              style="margin-right:6px; margin-bottom:6px"
              data-preview-url="<?= esc($item['url'] ?? '') ?>"
              data-preview-title="<?= esc($item['label'] ?? 'Panduan') ?>"
            >
              <?= esc($item['label'] ?? 'Panduan') ?>
            </a>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>
    </div>
    <section class="lab-sidebar-audio">
      <div class="lab-sidebar-audio-title">Audio Guru</div>
      <div id="adminAudioIndicator" class="audioIndicator idle" aria-live="polite">
        <span class="dot"></span>
        <span class="text">Audio: menunggu/standby</span>
      </div>
      <button id="btnEnableAdminAudio" type="button" class="ok" title="Klik sekali untuk mengaktifkan suara di peramban">
        🔊 Aktifkan Suara
      </button>
      <div class="lab-audio-group">
        <button id="btnAdminMic" class="ok btn lab-audio-btn" type="button" title="Aktif/nonaktif mikrofon guru">🎙️ Mikrofon Guru: Hidup</button>
        <div class="lab-audio-dd" aria-hidden="true">
          <span class="lab-audio-caret">▾</span>
          <select id="selAdminMic" aria-label="Pilih mikrofon guru"></select>
        </div>
      </div>
      <div class="lab-audio-group">
        <button id="btnAdminSpk" class="ok btn lab-audio-btn" type="button" title="Aktif/nonaktif speaker guru">🔊 Speaker Guru: Hidup</button>
        <div class="lab-audio-dd" aria-hidden="true">
          <span class="lab-audio-caret">▾</span>
          <select id="selAdminSpk" aria-label="Pilih speaker guru"></select>
        </div>
      </div>
      <div class="lab-audio-sliders">
        <div class="lab-audio-slider-row">
          <label class="lab-audio-slider-head" for="rngAdminMicVol">
            <span>Volume Mikrofon</span>
            <span id="txtAdminMicVol">100%</span>
          </label>
          <input id="rngAdminMicVol" type="range" min="0" max="100" value="100" step="1" aria-label="Volume mikrofon guru">
        </div>
        <div class="lab-audio-slider-row">
          <label class="lab-audio-slider-head" for="rngAdminSpkVol">
            <span>Volume Speaker</span>
            <span id="txtAdminSpkVol">100%</span>
          </label>
          <input id="rngAdminSpkVol" type="range" min="0" max="100" value="100" step="1" aria-label="Volume speaker guru">
        </div>
      </div>
    </section>
  </aside>
<?php endif; ?>
