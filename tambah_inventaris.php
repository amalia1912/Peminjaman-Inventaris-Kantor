<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = $success = '';

// Fungsi sanitize nama file
function sanitizeFileName($str) {
    $str = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $str);
    $str = preg_replace('/\s+/', '_', trim($str));
    return strtolower($str);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $nama = trim($_POST['nama'] ?? '');
    $jenis = $_POST['jenis'] ?? '';
    $jumlah = (int)($_POST['jumlah'] ?? 0);

    if (!$nama || !$jenis || $jumlah <= 0) {
        $error = "Semua field wajib diisi.";
    } else {
        $foto = 'default.jpg';
        if (!empty($_FILES['foto']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/inventaris/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = $_FILES['foto']['name'];
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_size = $_FILES['foto']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = ['jpg', 'jpeg', 'png'];
            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Hanya file JPG, JPEG, PNG yang diperbolehkan.";
            } elseif ($file_size > 2097152) {
                $error = "Ukuran file maksimal 2MB.";
            } else {
                $clean_nama = sanitizeFileName($nama);
                $clean_jenis = sanitizeFileName($jenis);
                $new_file_name = $clean_nama . '_' . $clean_jenis . '.' . $file_ext;
                $target_path = $upload_dir . $new_file_name;

                $counter = 1;
                while (file_exists($target_path)) {
                    $new_file_name = $clean_nama . '_' . $clean_jenis . '_' . $counter . '.' . $file_ext;
                    $target_path = $upload_dir . $new_file_name;
                    $counter++;
                }

                if (move_uploaded_file($file_tmp, $target_path)) {
                    $foto = $new_file_name;
                } else {
                    $error = "Gagal mengupload foto.";
                }
            }
        }

        if (!$error) {
            $stmt = $connection->prepare("INSERT INTO inventaris (nama, jenis, jumlah_total, jumlah_tersedia, foto) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiis", $nama, $jenis, $jumlah, $jumlah, $foto);
            if ($stmt->execute()) {
                header("Location: kelola_inventaris.php?success=1");
                exit;
            } else {
                $error = "Gagal menambahkan inventaris.";
            }
        }
    }
}

$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Inventaris</title>
    <link href="../assets/default/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php
include "../views/$THEME/header.php";
include "../views/$THEME/sidebar.php";
include "../views/$THEME/topnav.php";
include "../views/$THEME/upper_block.php";
?>

<div class="container-fluid mt-4">
    <h4>Tambah Inventaris Baru</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nama" class="form-label">Nama Inventaris</label>
                <input type="text" name="nama" id="nama" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="jenis" class="form-label">Jenis</label>
                <select name="jenis" id="jenis" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="meja">Meja</option>
                    <option value="kursi">Kursi</option>
                    <option value="kipas_angin">Kipas Angin</option>
                    <option value="ruang_rapat">Ruang Rapat</option>
                    <option value="kendaraan">Kendaraan</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="jumlah" class="form-label">Jumlah Total</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" required>
            </div>
            <div class="col-md-8">
                <label for="foto" class="form-label">Foto (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                <small class="text-muted">Format: JPG/PNG, Max 2MB. Contoh: mobil_bmw.jpg</small>
            </div>
            <div class="col-12">
                <button type="submit" name="add" class="btn btn-primary">Simpan Inventaris</button>
                <a href="kelola_inventaris.php" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>

<?php
include "../views/$THEME/lower_block.php";
include "../views/$THEME/footer.php";
?>
</body>
</html>