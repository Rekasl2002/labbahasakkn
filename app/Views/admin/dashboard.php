<?php
$bodyClass = $activeSession ? 'has-sidebars' : '';
$sessionTiming = $sessionTiming ?? null;
$sessionHistory = $sessionHistory ?? [];
?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php if ($activeSession): ?>
  <?= view('layout/partials/sidebar_left', ['role' => 'admin', 'state' => $state]) ?>
  <?= view('layout/partials/sidebar_right', ['role' => 'admin', 'state' => $state]) ?>
<?php endif; ?>

<div class="dashboard-center<?= $activeSession ? ' dashboard-center-with-sidebars' : '' ?>">

<header class="pageHead">
  <div>
    <h1 style="margin:0">Beranda Guru</h1>
  </div>
</header>

<div class="card">
  <?php if (!$activeSession): ?>
    <div class="row between wrap gap">
      <div>
        <h2 style="margin:0 0 6px">Sesi belum aktif</h2>
        <p class="muted" style="margin:0">
          Mulai sesi agar siswa bisa masuk.
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
      <input
        type="number"
        name="duration_minutes"
        min="15"
        max="1440"
        value="<?= esc((string) old('duration_minutes', '90')) ?>"
        style="width:130px"
        title="Batas durasi sesi dalam menit"
        placeholder="Menit"
      >
      <button type="submit" class="ok">▶ Mulai Sesi</button>
    </form>

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
            <?php if (is_array($sessionTiming) && !empty($sessionTiming['has_limit'])): ?>
              <?php
              $baseLimit = (int) ($sessionTiming['duration_limit_minutes'] ?? 0);
              $extensionMinutes = (int) ($sessionTiming['extension_minutes'] ?? 0);
              ?>
              <div
                id="sessionTimerMeta"
                class="muted tiny"
                data-deadline="<?= esc($sessionTiming['deadline_at'] ?? '') ?>"
                data-warning-seconds="<?= (int) ($sessionTiming['warning_seconds'] ?? 600) ?>"
                style="margin-top:4px"
              >
                Batas: <?= $baseLimit ?> menit<?= $extensionMinutes > 0 ? ' (+' . $extensionMinutes . ' menit)' : '' ?>.
                Sisa waktu: <b><span id="sessionRemainingLabel">menghitung...</span></b>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="row gap wrap">
        <?php if (is_array($sessionTiming) && !empty($sessionTiming['has_limit'])): ?>
          <form method="post" action="/admin/session/extend">
            <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
            <button class="btn" type="submit">+30 Menit</button>
          </form>
        <?php endif; ?>

        <form method="post" action="/admin/session/end" onsubmit="return confirm('Tutup sesi dan buat rekap?')">
          <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
          <button class="danger" type="submit">■ Tutup Sesi</button>
        </form>
      </div>
    </div>

    <?php if (is_array($sessionTiming) && !empty($sessionTiming['has_limit'])): ?>
      <div
        id="sessionLimitWarning"
        class="alert alert-error"
        style="margin-top:12px;<?= !empty($sessionTiming['is_near_limit']) || !empty($sessionTiming['is_expired']) ? '' : 'display:none;' ?>"
      >
        <div class="row between wrap gap">
          <div>
            Batas waktu sesi akan habis dalam <b id="sessionWarningRemaining">menghitung...</b>.
            <div class="muted tiny">Jika perlu, perpanjang sesi selama 30 menit.</div>
          </div>
          <form method="post" action="/admin/session/extend">
            <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
            <button class="ok" type="submit">Perpanjang +30 Menit</button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <hr>

  <?php endif; ?>
</div>

<?php if ($activeSession): ?>
  <div style="margin-top:14px">
    <!-- CENTER: PARTICIPANTS + SUARA -->
    <section class="card">
      <div class="row between wrap gap" style="align-items:flex-end">
        <div>
          <h2 style="margin:0">Komputer / Peserta</h2>
        </div>
      </div>

      <hr>

      <!-- Panel suara -->
      <div class="callBar row between wrap gap" style="align-items:center">
        <div class="row gap wrap" style="align-items:center">
          <span class="badge">Audio</span>
          <span id="callStatus" class="muted" aria-live="polite">Voice Room: menunggu/standby</span>
        </div>
      </div>

      <audio id="adminRemoteAudio" class="audioEl" playsinline></audio>
      <hr>

      <noscript>
        <p class="danger">Fitur skrip peramban wajib aktif agar daftar peserta dan suara berjalan.</p>
      </noscript>

      <div id="participantsGrid" class="gridCards"></div>
    </section>
  </div>

<?php endif; ?>

<?php if (!$activeSession): ?>
  <section class="card" style="margin-top:14px">
    <div>
      <h2 style="margin:0">Riwayat Sesi</h2>
      <div class="muted tiny" style="margin-top:4px">
        Buka rincian untuk melihat ringkasan sesi.
      </div>
    </div>

    <hr>

    <?php if (empty($sessionHistory)): ?>
      <p class="muted" style="margin:0">Belum ada sesi yang tersimpan.</p>
    <?php else: ?>
      <div class="tableWrap">
        <table class="table">
          <thead>
            <tr>
              <th>Nama Sesi</th>
              <th>Status</th>
              <th>Mulai</th>
              <th>Selesai</th>
              <th>Batas</th>
              <th>Detail</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sessionHistory as $row): ?>
              <?php
              $isActive = (int) ($row['is_active'] ?? 0) === 1;
              $endedAt = trim((string) ($row['ended_at'] ?? ''));
              $endedLabel = $endedAt !== '' ? $endedAt : ($isActive ? 'Sedang berlangsung' : '-');
              $baseLimit = (int) ($row['duration_limit_minutes'] ?? 0);
              $extensionMinutes = (int) ($row['extension_minutes'] ?? 0);
              $limitLabel = '-';
              if ($baseLimit > 0) {
                  $limitLabel = $baseLimit . ' menit';
                  if ($extensionMinutes > 0) {
                      $limitLabel .= ' (+' . $extensionMinutes . ')';
                  }
              }
              ?>
              <tr>
                <td><?= esc($row['name'] ?: 'Sesi tanpa nama') ?></td>
                <td>
                  <?php if ($isActive): ?>
                    <span class="badge ok">AKTIF</span>
                  <?php else: ?>
                    <span class="badge">SELESAI</span>
                  <?php endif; ?>
                </td>
                <td><?= esc($row['started_at'] ?? '-') ?></td>
                <td><?= esc($endedLabel) ?></td>
                <td><?= esc($limitLabel) ?></td>
                <td>
                  <div class="row gap wrap">
                    <a class="btn tiny" href="/admin/session/<?= (int) ($row['id'] ?? 0) ?>/recap">Detail</a>
                    <a class="btn tiny" href="/admin/session/<?= (int) ($row['id'] ?? 0) ?>/report/excel">Excel</a>
                    <a class="btn tiny" href="/admin/session/<?= (int) ($row['id'] ?? 0) ?>/report/pdf">PDF</a>
                    <?php if (!$isActive): ?>
                      <form
                        method="post"
                        action="/admin/session/<?= (int) ($row['id'] ?? 0) ?>/delete"
                        onsubmit="return confirm('Hapus riwayat sesi ini secara permanen? Data peserta, pesan, catatan aktivitas, dan ringkasan akan ikut terhapus.')"
                      >
                        <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
                        <button class="btn tiny danger" type="submit">Hapus</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
<?php endif; ?>

<?php if ($activeSession): ?>
  <script>
    (function () {
      const meta = document.getElementById('sessionTimerMeta');
      if (!meta) return;

      const remainingLabel = document.getElementById('sessionRemainingLabel');
      const warningBox = document.getElementById('sessionLimitWarning');
      const warningRemaining = document.getElementById('sessionWarningRemaining');
      const deadlineRaw = (meta.dataset.deadline || '').trim();
      const warningSeconds = Number(meta.dataset.warningSeconds || 600);
      if (!deadlineRaw) return;

      const deadlineDate = new Date(deadlineRaw.replace(' ', 'T'));
      if (Number.isNaN(deadlineDate.getTime())) return;
      let reloadQueued = false;

      function formatRemaining(totalSec) {
        const sec = Math.max(0, Math.floor(totalSec));
        const h = Math.floor(sec / 3600);
        const m = Math.floor((sec % 3600) / 60);
        const s = sec % 60;
        if (h > 0) return `${h}j ${m}m ${s}d`;
        if (m > 0) return `${m}m ${s}d`;
        return `${s}d`;
      }

      function tick() {
        let remain = Math.floor((deadlineDate.getTime() - Date.now()) / 1000);
        if (remain < 0) remain = 0;

        if (remainingLabel) {
          remainingLabel.textContent = `${formatRemaining(remain)} (batas waktu: ${deadlineRaw})`;
        }
        if (warningRemaining) {
          warningRemaining.textContent = formatRemaining(remain);
        }
        if (warningBox) {
          warningBox.style.display = remain <= warningSeconds ? '' : 'none';
        }

        if (remain === 0 && !reloadQueued) {
          reloadQueued = true;
          setTimeout(() => window.location.reload(), 1500);
        }
      }

      tick();
      setInterval(tick, 1000);
    })();
  </script>
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

