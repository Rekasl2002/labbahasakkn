<?php
$role = $role ?? '';
$roleLabel = $role === 'admin' ? 'Guru' : 'Siswa';
$userName = $role === 'admin'
    ? (session('admin_username') ?: 'Guru')
    : (session('student_name') ?: 'Siswa');
$userMeta = $role === 'student' ? (session('class_name') ?: '') : '';
$state = $state ?? [];
?>

<?php if ($role === 'student'): ?>
  <aside class="lab-sidebar lab-sidebar-right">
    <div class="lab-sidebar-inner">
      <section class="card">
        <h2 style="margin:0 0 8px">Pesan ke Guru</h2>

        <div id="chatLog" class="chatLog"></div>

        <div class="row gap" style="margin-top:10px; align-items:center">
          <input id="chatInput" placeholder="Ketik pesan..." style="flex:1" autocomplete="off">
          <button id="btnSendChat" class="btn" type="button">Kirim</button>
        </div>
      </section>
    </div>
  </aside>
<?php elseif ($role === 'admin'): ?>
  <aside class="lab-sidebar lab-sidebar-right">
    <div class="lab-sidebar-inner">
      <section class="card">
        <div class="row between wrap gap" style="align-items:flex-end">
          <div>
            <h2 style="margin:0">Pesan</h2>
          </div>
        </div>

        <div class="row gap wrap" style="margin-top:10px; align-items:center">
          <label class="muted tiny" for="chatMode" style="min-width:60px">Tujuan</label>
          <select id="chatMode">
            <option value="public">Ke Semua Siswa</option>
            <option value="private_student">Ke Salah Satu Siswa</option>
          </select>

          <label class="muted tiny" for="privateTarget" style="min-width:86px">Nama Siswa</label>
          <select id="privateTarget" disabled></select>
        </div>

        <div id="chatLog" class="chatLog" style="margin-top:10px"></div>

        <div class="row gap" style="margin-top:10px; align-items:center">
          <input id="chatInput" placeholder="Ketik pesan..." style="flex:1" autocomplete="off">
          <button id="btnSendChat" class="btn" type="button">Kirim</button>
        </div>
      </section>

      <section class="card">
        <div class="row between wrap gap" style="align-items:flex-end">
          <div>
            <h2 style="margin:0">Teks dari Guru</h2>
          </div>
        </div>

        <label class="muted tiny" for="broadcastText" style="margin-top:8px;display:block">Pesan singkat</label>
        <input
          id="broadcastText"
          placeholder="Kata/kalimat singkat untuk ditampilkan ke semua siswa..."
          value="<?= esc($state['broadcast_text'] ?? '') ?>"
          data-enabled="<?= isset($state['broadcast_enabled']) ? (int) $state['broadcast_enabled'] : ((isset($state['broadcast_text']) && trim((string) $state['broadcast_text']) !== '') ? 1 : 0) ?>"
          maxlength="255"
          autocomplete="off"
        >
        <div class="row gap wrap" style="margin-top:8px">
          <button id="btnBroadcastText" type="button" class="iconBtn" title="Gunakan teks dari Guru" aria-label="Gunakan teks dari Guru">&#10003;</button>
          <button id="btnClearBroadcastText" type="button" class="iconBtn" title="Tutup atau hapus teks Guru" aria-label="Tutup atau hapus teks Guru">&times;</button>
        </div>
      </section>

      <section class="card">
        <div class="row between wrap gap" style="align-items:flex-end">
          <div>
            <h2 style="margin:0">Materi Aktif</h2>
          </div>
          <div class="btnGroup materialActionGroup">
            <a class="btn materialSwitchBtn js-open-settings" href="/admin/settings?tab=materials&mat=list">Pilih/Ganti Materi</a>
            <button id="btnRefreshMaterial" class="btn materialRefreshBtn" type="button" title="Segarkan daftar materi" aria-label="Segarkan daftar materi">↻</button>
          </div>
        </div>

        <div id="currentMaterialBox" class="materialBox muted" style="margin-top:10px">Belum ada materi.</div>
      </section>
    </div>
  </aside>
<?php endif; ?>

