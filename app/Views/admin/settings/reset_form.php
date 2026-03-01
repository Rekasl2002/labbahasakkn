<?php
$embed = !empty($embed);
$resetPhrase = trim((string) ($resetPhrase ?? 'RESET TOTAL'));
if ($resetPhrase === '') {
    $resetPhrase = 'RESET TOTAL';
}
?>

<section class="card">
  <h2 style="margin:0 0 6px">Reset Total Aplikasi</h2>
  <p class="muted tiny" style="margin:0 0 10px">
    Fitur ini akan menghapus seluruh data operasional agar aplikasi kembali siap dipakai dari awal.
  </p>

  <div class="alert alert-error" style="margin-bottom:10px">
    <strong>Peringatan:</strong> semua data sesi, materi, serta file upload akan dihapus permanen.
  </div>

  <ol class="muted tiny" style="margin:0 0 10px; padding-left:18px">
    <li>Tutup dulu sesi yang sedang aktif.</li>
    <li>Masukkan kata sandi guru saat ini.</li>
    <li>Ketik frasa konfirmasi sesuai instruksi.</li>
    <li>Centang persetujuan lalu klik reset.</li>
  </ol>

  <form method="post" action="/admin/settings/reset" target="<?= $embed ? '_top' : '_self' ?>" onsubmit="return labConfirmTotalReset();">
    <?php if (function_exists('csrf_field')): ?><?= csrf_field() ?><?php endif; ?>
    <?php if ($embed): ?>
      <input type="hidden" name="embed" value="1">
    <?php endif; ?>

    <label>Kata sandi guru saat ini</label>
    <input name="current_password" type="password" autocomplete="current-password" required>

    <label>Ketik frasa konfirmasi: <code><?= esc($resetPhrase) ?></code></label>
    <input name="reset_phrase" type="text" autocomplete="off" spellcheck="false" required>

    <label class="resetAgreeRow">
      <input type="checkbox" name="reset_agree" value="1" required>
      <span>Saya memahami bahwa setelah reset total seluruh isi aplikasi akan kembali ke awal dan tidak dapat diurungkan.</span>
    </label>

    <button type="submit" class="danger" style="margin-top:10px">Reset Total Sekarang</button>
  </form>
</section>

<script>
function labConfirmTotalReset(){
  const phrase = <?= json_encode($resetPhrase, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const promptText = 'Konfirmasi terakhir: ketik "' + phrase + '" untuk lanjut.';
  const secondCheck = window.prompt(promptText, '');
  if(secondCheck === null){
    return false;
  }
  if(secondCheck.trim() !== phrase){
    window.alert('Frasa konfirmasi terakhir tidak cocok.');
    return false;
  }
  return window.confirm('Semua data operasional akan dihapus permanen. Lanjutkan reset total?');
}
</script>
<style>
.resetAgreeRow{
  display:flex;
  align-items:flex-start;
  justify-content:flex-start;
  gap:8px;
  margin-top:10px;
}
.resetAgreeRow input[type="checkbox"]{
  width:auto;
  min-width:16px;
  height:16px;
  margin:2px 0 0;
  padding:0;
  flex:0 0 auto;
}
.resetAgreeRow span{
  color:var(--text);
  font-size:14px;
  line-height:1.35;
}
</style>
