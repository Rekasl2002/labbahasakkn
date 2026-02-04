<?php
$role = $role ?? '';
$roleLabel = $role === 'admin' ? 'Admin' : 'Siswa';
$userName = $role === 'admin'
    ? (session('admin_username') ?: 'Admin')
    : (session('student_name') ?: 'Siswa');
$userMeta = $role === 'student' ? (session('class_name') ?: '') : '';
?>

<?php if ($role === 'student'): ?>
  <aside class="lab-sidebar lab-sidebar-right">
    <div class="lab-sidebar-inner">
      <div class="lab-sidebar-head">
        <div class="lab-sidebar-title">Panel Komunikasi</div>
        <div class="lab-sidebar-sub">Sidebar Kanan • <?= esc($roleLabel) ?></div>
      </div>
      <div class="lab-sidebar-user">
        <div class="name"><?= esc($userName) ?></div>
        <?php if ($userMeta !== ''): ?>
          <div class="meta"><?= esc($userMeta) ?></div>
        <?php endif; ?>
      </div>
      <section class="card">
        <h2 style="margin:0 0 8px">Chat ke Admin</h2>

        <div id="chatLog" class="chatLog"></div>

        <div class="row gap" style="margin-top:10px; align-items:center">
          <input id="chatInput" placeholder="Ketik pesan..." style="flex:1" autocomplete="off">
          <button id="btnSendChat" type="button">Kirim</button>
        </div>

        <p class="muted tiny" style="margin:10px 0 0">
          Default: pesan private ke admin.
        </p>
      </section>
    </div>
  </aside>
<?php elseif ($role === 'admin'): ?>
  <aside class="lab-sidebar lab-sidebar-right">
    <div class="lab-sidebar-inner">
      <div class="lab-sidebar-head">
        <div class="lab-sidebar-title">Panel Admin</div>
        <div class="lab-sidebar-sub">Sidebar Kanan • <?= esc($roleLabel) ?></div>
      </div>
      <div class="lab-sidebar-user">
        <div class="name"><?= esc($userName) ?></div>
        <div class="meta">Chat & Materi</div>
      </div>
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
      </section>

      <section class="card">
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
  </aside>
<?php endif; ?>
