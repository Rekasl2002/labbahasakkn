<?php
$embed = !empty($embed);
$warningSoundPath = trim((string) ($warningSoundPath ?? ''));
$warningSoundUrl = trim((string) ($warningSoundUrl ?? ''));
$hasWarningSound = $warningSoundPath !== '' && $warningSoundUrl !== '';
?>

<section class="card">
  <h2 style="margin:0 0 6px">Suara Peringatan</h2>
  <p class="muted tiny" style="margin:0 0 10px">
    Upload suara yang diputar saat Guru menekan tombol "Peringatan + Suara". Jika tidak ada file, sistem memakai bunyi default bawaan.
  </p>

  <?php if ($hasWarningSound): ?>
    <div class="muted tiny" style="margin-bottom:6px">Suara saat ini</div>
    <audio controls preload="none" src="<?= esc($warningSoundUrl) ?>" style="width:100%"></audio>
    <div class="muted tiny" style="margin-top:6px">
      File: <?= esc(basename($warningSoundPath)) ?>
    </div>
  <?php else: ?>
    <div class="muted" style="margin-bottom:10px">Saat ini masih menggunakan suara default bawaan sistem.</div>
  <?php endif; ?>

  <div class="row gap wrap" style="margin-top:10px">
    <button id="btnPreviewWarningCurrent" class="btn" type="button">Tes Suara yang Dipakai</button>
    <button id="btnPreviewWarningDefault" class="btn" type="button">Tes Suara Default</button>
  </div>
  <div id="warningSoundTestInfo" class="muted tiny" style="margin-top:6px">
    Klik tombol tes untuk mencoba suara peringatan.
  </div>
  <?php if ($hasWarningSound): ?>
    <audio id="warningSoundCurrentAudio" preload="auto" src="<?= esc($warningSoundUrl) ?>"></audio>
  <?php endif; ?>

  <form method="post" action="/admin/settings" enctype="multipart/form-data" style="margin-top:10px">
    <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
    <?php if ($embed): ?>
      <input type="hidden" name="embed" value="1">
    <?php endif; ?>
    <input type="hidden" name="setting_group" value="warning-sound">

    <label>Upload suara baru (opsional)</label>
    <input
      type="file"
      name="warning_sound_file"
      accept=".mp3,.wav,.ogg,.webm,.m4a,.mp4,.aac,audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/ogg,audio/webm,audio/mp4,audio/aac,audio/x-m4a"
    >

    <p class="muted tiny" style="margin:6px 0 0">
      Format yang didukung: MP3, WAV, OGG, WEBM, M4A, MP4, AAC (maksimal 8MB).
    </p>

    <button type="submit" class="ok" style="margin-top:10px">Simpan Suara</button>
  </form>

  <form method="post" action="/admin/settings" style="margin-top:8px" onsubmit="return confirm('Kembalikan ke suara default bawaan sistem?')">
    <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
    <?php if ($embed): ?>
      <input type="hidden" name="embed" value="1">
    <?php endif; ?>
    <input type="hidden" name="setting_group" value="warning-sound">
    <input type="hidden" name="warning_sound_remove" value="1">
    <button type="submit" class="danger">Gunakan Suara Default</button>
  </form>

  <hr>

  <h3 style="margin:0 0 6px">Rekomendasi Suara Peringatan</h3>
  <p class="muted tiny" style="margin:0 0 8px">
    Agar siswa cepat sadar saat tidak fokus atau keluar dari halaman sesi, gunakan suara seperti ini:
  </p>
  <ol style="margin:0 0 0 18px; padding:0">
    <li>Durasi singkat, sekitar 2 sampai 3 detik.</li>
    <li>Dapat mengundang perhatian siswa.</li>
    <li>Tidak terlalu keras.</li>
    <li>Hindari musik panjang.</li>
  </ol>
  <p class="muted tiny" style="margin:8px 0 0">

  <p class="muted tiny" style="margin:0 0 8px">
    Untuk suara berupa ucapan (suara orang), direkomendasikan menggunakan:
  </p>
  <ol style="margin:0 0 0 18px; padding:0">
    <li>Durasi singkat, kurang lebih 5 detik.</li>
    <li>Ucapan tegas tapi tetap ramah, jangan terlalu keras.</li>
  </ol>
  <p class="muted tiny" style="margin:8px 0 0"></p>

    Contoh kalimat: "Perhatian, kembali ke halaman sesi sekarang." atau "Ayo fokus lagi, kembali ke halaman sesi."
  </p>
</section>

<script>
(function(){
  const btnCurrent = document.getElementById('btnPreviewWarningCurrent');
  const btnDefault = document.getElementById('btnPreviewWarningDefault');
  const infoEl = document.getElementById('warningSoundTestInfo');
  const currentAudio = document.getElementById('warningSoundCurrentAudio');

  const setInfo = (text)=>{
    if(infoEl) infoEl.textContent = text || '';
  };

  const playDefaultTone = ()=>{
    try{
      const AC = window.AudioContext || window.webkitAudioContext;
      if(!AC){
        setInfo('Perangkat ini tidak mendukung tes suara default.');
        return;
      }
      const ctx = new AC();
      const startAt = ctx.currentTime + 0.02;

      const beep = (offset, freq, dur, gain)=>{
        const osc = ctx.createOscillator();
        const amp = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        amp.gain.setValueAtTime(0.0001, startAt + offset);
        amp.gain.exponentialRampToValueAtTime(gain, startAt + offset + 0.01);
        amp.gain.exponentialRampToValueAtTime(0.0001, startAt + offset + dur);
        osc.connect(amp);
        amp.connect(ctx.destination);
        osc.start(startAt + offset);
        osc.stop(startAt + offset + dur + 0.02);
      };

      ctx.resume().catch(()=>{});
      beep(0.00, 880, 0.18, 0.22);
      beep(0.22, 660, 0.22, 0.18);
      setInfo('Memutar suara default.');
      setTimeout(()=>{
        ctx.close().catch(()=>{});
        setInfo('Selesai memutar suara default.');
      }, 1000);
    }catch(e){
      setInfo('Gagal memutar suara default.');
    }
  };

  const playCurrent = ()=>{
    if(currentAudio && currentAudio.src){
      try{
        currentAudio.pause();
        currentAudio.currentTime = 0;
      }catch(e){}
      setInfo('Memutar suara yang sedang dipakai.');
      const p = currentAudio.play();
      if(p && typeof p.catch === 'function'){
        p.catch(()=>{
          setInfo('Gagal memutar file. Dipindahkan ke suara default.');
          playDefaultTone();
        });
      }
      currentAudio.onended = ()=> setInfo('Selesai memutar suara yang sedang dipakai.');
      return;
    }

    setInfo('Belum ada file khusus. Memutar suara default.');
    playDefaultTone();
  };

  if(btnCurrent){
    btnCurrent.addEventListener('click', playCurrent);
  }
  if(btnDefault){
    btnDefault.addEventListener('click', ()=>{
      setInfo('Memutar suara default.');
      playDefaultTone();
    });
  }
})();
</script>
