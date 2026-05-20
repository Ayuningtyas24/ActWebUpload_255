<?php

$target_dir = "uploads/";

if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// DELETE FILE
if(isset($_GET['delete'])){
    $delete_file = $target_dir . basename($_GET['delete']);
    if(file_exists($delete_file)){
        unlink($delete_file);
    }
    header("Location: upload.php?msg=File berhasil dihapus.&ok=1");
    exit;
}

// PROSES UPLOAD
$message = "";
$msgOk = false;

if(isset($_POST["upload"])){
    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "gif"];
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);

    if($check === false){
        $message = "Upload ditolak! File harus berupa foto.";
    } elseif(!in_array($imageFileType, $allowed)){
        $message = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
    } elseif(file_exists($target_file)){
        $message = "File sudah ada.";
    } elseif(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)){
        $message = "Foto berhasil diupload.";
        $msgOk = true;
    } else {
        $message = "Upload gagal.";
    }
}

if(isset($_GET['msg'])){
    $message = $_GET['msg'];
    $msgOk = isset($_GET['ok']);
}

// AMBIL FILE
$files = array_diff(scandir($target_dir), array('.', '..'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Web Upload Foto</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrap">

  <!-- HEADER -->
  <div class="header">
    <div class="header-icon"><i class="ti ti-photo"></i></div>
    <div>
      <h1>Web Upload Foto</h1>
      <p class="header-sub">Upload, lihat, dan kelola foto kamu</p>
    </div>
  </div>

  <!-- NOTIF -->
  <?php if($message != ""): ?>
  <div class="notif <?= $msgOk ? 'ok' : 'err' ?>">
    <i class="ti <?= $msgOk ? 'ti-check' : 'ti-alert-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
  </div>
  <?php endif; ?>

  <!-- FORM UPLOAD -->
  <div class="upload-card">
    <form action="upload.php" method="POST" enctype="multipart/form-data">

      <div class="upload-zone" id="dropZone" onclick="document.getElementById('fileToUpload').click()">
        <i class="ti ti-cloud-upload"></i>
        <p>Klik atau drag & drop foto ke sini</p>
        <span>Format: JPG, PNG, GIF &nbsp;·&nbsp; Maks. 5 MB</span>
      </div>

      <input type="file" name="fileToUpload" id="fileToUpload"
             accept="image/*" style="display:none"
             onchange="previewImage(event)" required>

      <!-- PREVIEW -->
      <div class="preview-wrap" id="previewWrap">
        <img id="preview" src="" alt="Preview">
        <p class="preview-label">Preview foto</p>
        <button type="button" class="btn-clear-preview" onclick="clearPreview()">
          <i class="ti ti-x"></i> Batal
        </button>
      </div>

      <!-- INFO FILE -->
      <div class="file-info-row" id="fileInfoRow" style="display:none">
        <i class="ti ti-photo" style="color:#185fa5;"></i>
        <span id="fileName"></span>
        <span class="size" id="fileSize"></span>
      </div>

      <button type="submit" name="upload" class="btn-upload" id="btnUpload" disabled>
        <i class="ti ti-upload"></i>
        Unggah Foto
      </button>
    </form>
  </div>

  <!-- DAFTAR FILE -->
  <div class="list-header">
    <p class="section-label">
      Foto terupload
      <span class="file-count">(<?= count($files) ?>)</span>
    </p>
  </div>

  <?php if(empty($files)): ?>
  <div class="empty">
    <i class="ti ti-photo-off"></i>
    Belum ada foto yang diupload
  </div>
  <?php else: ?>
  <div class="grid">
    <?php $no = 1; foreach($files as $file):
      $file_path = $target_dir . $file;
      $bytes = filesize($file_path);
      $size = $bytes < 1048576 ? round($bytes/1024,1).' KB' : round($bytes/1048576,1).' MB';
      $date = date('d M Y, H:i', filemtime($file_path));
    ?>
    <div class="grid-card">
      <div class="grid-img-wrap">
        <img class="grid-img" src="<?= $file_path ?>" alt="<?= htmlspecialchars($file) ?>"
             onclick="openLightbox('<?= $file_path ?>')">
        <div class="grid-overlay">
          <button class="overlay-btn" onclick="openLightbox('<?= $file_path ?>')">
            <i class="ti ti-eye"></i>
          </button>
        </div>
      </div>
      <div class="grid-info">
        <p class="grid-name"><?= htmlspecialchars($file) ?></p>
        <p class="grid-meta"><?= $size ?> &nbsp;·&nbsp; <?= $date ?></p>
        <div class="grid-actions">
          <a class="btn-action btn-dl" href="<?= $file_path ?>" download="<?= htmlspecialchars($file) ?>">
            <i class="ti ti-download"></i> Download
          </a>
          <a class="btn-action btn-del"
             href="?delete=<?= urlencode($file) ?>"
             onclick="return confirm('Hapus foto \'<?= addslashes($file) ?>\'?')">
            <i class="ti ti-trash"></i> Hapus
          </a>
        </div>
      </div>
    </div>
    <?php $no++; endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">
    <i class="ti ti-x"></i>
  </button>
  <img id="lightboxImg" src="" alt="Preview">
</div>

<script>
// Preview sebelum upload
function previewImage(event) {
  const file = event.target.files[0];
  if (!file) return;
  const preview = document.getElementById('preview');
  const previewWrap = document.getElementById('previewWrap');
  const fileInfoRow = document.getElementById('fileInfoRow');
  const btnUpload   = document.getElementById('btnUpload');

  preview.src = URL.createObjectURL(file);
  previewWrap.style.display = 'block';

  document.getElementById('fileName').textContent = file.name;
  const kb = file.size / 1024;
  document.getElementById('fileSize').textContent = kb < 1024
    ? kb.toFixed(1)+' KB' : (kb/1024).toFixed(1)+' MB';
  fileInfoRow.style.display = 'flex';
  btnUpload.disabled = false;
}

function clearPreview() {
  document.getElementById('fileToUpload').value = '';
  document.getElementById('preview').src = '';
  document.getElementById('previewWrap').style.display = 'none';
  document.getElementById('fileInfoRow').style.display = 'none';
  document.getElementById('btnUpload').disabled = true;
}

// Drag & drop
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => {
  e.preventDefault();
  dropZone.classList.add('drag-over');
});
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file) {
    const dt = new DataTransfer();
    dt.items.add(file);
    const input = document.getElementById('fileToUpload');
    input.files = dt.files;
    previewImage({ target: input });
  }
});

// Lightbox
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('show');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('show');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeLightbox(); });
</script>
</body>
</html>