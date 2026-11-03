
<?php
require_once 'config/kuis_db.php';
require_once 'middleware/auth_check.php';

check_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project']);
    $deskripsi = trim($_POST['deskripsi']);
    $link_project = trim($_POST['link_project']);
    $gambar_nama = '';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
        } else {
            $new_name = uniqid('project_', true) . '.' . $ext;
            $target = 'uploads/' . $new_name;

            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
                $error = 'Gagal mengunggah gambar.';
            } else {
                $gambar_nama = $new_name;
            }
        }
    } else {
        $error = 'Gambar wajib diunggah.';
    }

    if (empty($error) && $nama_project !== '' && $deskripsi !== '' && $link_project !== '') {
        $stmt = $pdo->prepare("INSERT INTO projects (user_id, nama_project, deskripsi, gambar, link_project) VALUES (:user_id, :nama, :desk, :img, :link)");
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':nama' => $nama_project,
            ':desk' => $deskripsi,
            ':img' => $gambar_nama,
            ':link' => $link_project,
        ]);

        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Karya</title>
    <link rel="stylesheet" href="assets/css/style.php.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
<div class="form-container">
    <a href="index.php" class="btn btn-secondary" style="margin-bottom:15px;">⬅️ Batal</a>
    <h2>Tambah Karya Digital</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nama Karya</label>
            <input type="text" name="nama_project" value="<?= htmlspecialchars($_POST['nama_project'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" required><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Link Project</label>
            <input type="url" name="link_project" value="<?= htmlspecialchars($_POST['link_project'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Upload Gambar</label>
            <input type="file" name="gambar" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">💾 Simpan Karya</button>
    </form>
</div>
</body>
</html>
