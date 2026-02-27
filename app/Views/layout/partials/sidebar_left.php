<?php
helper('settings');
$role = $role ?? '';
$roleLabel = $role === 'Guru' ? 'Admin' : 'Siswa';
$userName = $role === 'Guru'
    ? (session('admin_username') ?: 'Admin')
    : (session('student_name') ?: 'Siswa');
$userMeta = $role === 'student' ? (session('class_name') ?: '') : '';
$appName = (lab_app_branding()['app_name'] ?? 'Lab Bahasa');
?>

<?php if ($role === 'student'): ?>
  <aside class="lab-sidebar lab-sidebar-left lab-sidebar-with-audio">
    <div class="lab-sidebar-inner">
      <div class="lab-sidebar-head">
        <div class="lab-sidebar-title"><?= esc($appName) ?></div>
        <div class="lab-sidebar-sub">Sidebar Kiri • <?= esc($roleLabel) ?></div>
      </div>
      <div class="lab-sidebar-user">
        <div class="name"><?= esc($userName) ?></div>
        <?php if ($userMeta !== ''): ?>
          <div class="meta"><?= esc($userMeta) ?></div>
        <?php endif; ?>
      </div>
      <section class="card">
        <h2 style="margin:0 0 8px">Teman Sesi</h2>
        <ul id="peersList" class="list"></ul>
      </section>
    </div>
    <section class="lab-sidebar-audio">
      <div class="lab-sidebar-audio-title">Audio</div>
      <div id="audioStatus" class="lab-audio-status muted" aria-live="polite">
        Tidak ada panggilan.
      </div>
      <div id="studentAudioIndicator" class="audioIndicator idle" aria-live="polite">
        <span class="dot"></span>
        <span class="text">Audio: standby</span>
      </div>
      <button id="btnEnableAudio" type="button" class="ok" title="Klik sekali untuk mengaktifkan output audio browser">
        🔊 Aktifkan Audio
      </button>
      <div class="lab-audio-group">
        <button id="btnMic" type="button" class="btn lab-audio-btn" title="Aktif/nonaktif mic kamu">
          Mic: OFF
        </button>
        <div class="lab-audio-dd" aria-hidden="true">
          <span class="lab-audio-caret">▾</span>
          <select id="selMic" aria-label="Pilih microphone"></select>
        </div>
      </div>
      <div class="lab-audio-group">
        <button id="btnSpk" type="button" class="btn lab-audio-btn" title="Aktif/nonaktif speaker kamu">
          Speaker: ON
        </button>
        <div class="lab-audio-dd" aria-hidden="true">
          <span class="lab-audio-caret">▾</span>
          <select id="selSpk" aria-label="Pilih speaker"></select>
        </div>
      </div>
      <div class="lab-audio-sliders">
        <div class="lab-audio-slider-row">
          <label class="lab-audio-slider-head" for="rngMicVol">
            <span>Volume Mic</span>
            <span id="txtMicVol">100%</span>
          </label>
          <input id="rngMicVol" type="range" min="0" max="100" value="100" step="1" aria-label="Volume mic siswa">
        </div>
        <div class="lab-audio-slider-row">
          <label class="lab-audio-slider-head" for="rngSpkVol">
            <span>Volume Speaker</span>
            <span id="txtSpkVol">100%</span>
          </label>
          <input id="rngSpkVol" type="range" min="0" max="100" value="100" step="1" aria-label="Volume speaker siswa">
        </div>
      </div>
    </section>
  </aside>
<?php elseif ($role === 'admin'): ?>
  <aside class="lab-sidebar lab-sidebar-left lab-sidebar-with-audio">
    <div class="lab-sidebar-inner">
      <div class="lab-sidebar-head">
        <div class="lab-sidebar-title"><?= esc($appName) ?></div>
      </div>
      <div class="lab-sidebar-user">
        <div class="name"><?= esc($userName) ?></div>
      </div>
      <section class="card">
        <h2 style="margin:0 0 6px">Kontrol Cepat</h2>
        <div class="row wrap gap" style="align-items:center">
          <button id="btnMuteAllMic" type="button" class="danger">🔇 Mute Mic Semua</button>
          <button id="btnUnmuteAllMic" type="button" class="ok">🎙️ Unmute Mic Semua</button>
        </div>

        <div class="row wrap gap" style="align-items:center; margin-top:8px">
          <button id="btnMuteAllSpk" type="button" class="danger">🔈 Mute Speaker Semua</button>
          <button id="btnUnmuteAllSpk" type="button" class="ok">🔊 Unmute Speaker Semua</button>
        </div>

        <div class="row gap wrap" style="margin-top:10px; align-items:center">
          <span class="muted tiny" style="min-width:110px">Kontrol Siswa</span>
          <label class="row gap" style="align-items:center">
            <input id="chkAllowStudentMic" type="checkbox">
            <span class="tiny">Siswa boleh atur mic</span>
          </label>
          <label class="row gap" style="align-items:center">
            <input id="chkAllowStudentSpk" type="checkbox">
            <span class="tiny">Siswa boleh atur speaker</span>
          </label>
        </div>
      </section>
    </div>
    <section class="lab-sidebar-audio">
      <div class="lab-sidebar-audio-title">Audio Admin</div>
      <div id="adminAudioIndicator" class="audioIndicator idle" aria-live="polite">
        <span class="dot"></span>
        <span class="text">Audio: standby</span>
      </div>
      <button id="btnEnableAdminAudio" type="button" class="ok" title="Klik sekali untuk mengaktifkan audio pada browser">
        🔊 Aktifkan Audio
      </button>
      <div class="lab-audio-group">
        <button id="btnAdminMic" class="ok btn lab-audio-btn" type="button" title="Aktif/nonaktif mic admin">🎙️ Mic Admin: ON</button>
        <div class="lab-audio-dd" aria-hidden="true">
          <span class="lab-audio-caret">▾</span>
          <select id="selAdminMic" aria-label="Pilih mic admin"></select>
        </div>
      </div>
      <div class="lab-audio-group">
        <button id="btnAdminSpk" class="ok btn lab-audio-btn" type="button" title="Aktif/nonaktif speaker admin">🔊 Speaker Admin: ON</button>
        <div class="lab-audio-dd" aria-hidden="true">
          <span class="lab-audio-caret">▾</span>
          <select id="selAdminSpk" aria-label="Pilih speaker admin"></select>
        </div>
      </div>
      <div class="lab-audio-sliders">
        <div class="lab-audio-slider-row">
          <label class="lab-audio-slider-head" for="rngAdminMicVol">
            <span>Volume Mic</span>
            <span id="txtAdminMicVol">100%</span>
          </label>
          <input id="rngAdminMicVol" type="range" min="0" max="100" value="100" step="1" aria-label="Volume mic admin">
        </div>
        <div class="lab-audio-slider-row">
          <label class="lab-audio-slider-head" for="rngAdminSpkVol">
            <span>Volume Speaker</span>
            <span id="txtAdminSpkVol">100%</span>
          </label>
          <input id="rngAdminSpkVol" type="range" min="0" max="100" value="100" step="1" aria-label="Volume speaker admin">
        </div>
      </div>
    </section>
  </aside>
<?php endif; ?>
