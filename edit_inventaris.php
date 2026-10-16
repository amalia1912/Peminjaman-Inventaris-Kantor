<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = $success = '';

// Cek apakah ID diberikan
if (!isset($_GET['id'])) {
    header('Location: kelola_inventaris.php');
    exit;
}

$id = (int)$_GET['id'];

// Ambil data inventaris
$stmt = $connection->prepare("SELECT * FROM inventaris WHERE id_inventaris = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: kelola_inventaris.php?error=1');
    exit;
}

$inventaris = $result->fetch_assoc();

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nama = trim($_POST['nama'] ?? '');
    $jenis = $_POST['jenis'] ?? '';
    $jumlah = (int)($_POST['jumlah'] ?? 0);

    if (!$nama || !$jenis || $jumlah <= 0) {
        $error = "Semua field wajib diisi.";
    } else {
        // Handle upload foto baru (opsional)
        $foto = $inventaris['foto']; // default: tetap pakai foto lama
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
                // Generate nama unik
                $clean_nama = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $nama);
                $clean_nama = preg_replace('/\s+/', '_', trim($clean_nama));
                $clean_jenis = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $jenis);
                $clean_jenis = preg_replace('/\s+/', '_', trim($clean_jenis));
                $new_file_name = $clean_nama . '_' . $clean_jenis . '.' . $file_ext;

                // Jika file sudah ada, tambahkan angka
                $counter = 1;
                $target_path = $upload_dir . $new_file_name;
                while (file_exists($target_path)) {
                    $new_file_name = $clean_nama . '_' . $clean_jenis . '_' . $counter . '.' . $file_ext;
                    $target_path = $upload_dir . $new_file_name;
                    $counter++;
                }

                if (move_uploaded_file($file_tmp, $target_path)) {
                    // Hapus foto lama jika bukan default
                    if ($inventaris['foto'] !== 'default.jpg') {
                        $old_file = $upload_dir . $inventaris['foto'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    $foto = $new_file_name;
                } else {
                    $error = "Gagal mengupload foto baru.";
                }
            }
        }

        if (!$error) {
            $stmt = $connection->prepare("UPDATE inventaris SET nama = ?, jenis = ?, jumlah_total = ?, jumlah_tersedia = ?, foto = ? WHERE id_inventaris = ?");
            $stmt->bind_param("ssiiis", $nama, $jenis, $jumlah, $jumlah, $foto, $id);
            if ($stmt->execute()) {
                $success = "Inventaris berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui inventaris.";
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
    <title>Edit Inventaris</title>
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
    <h4>Edit Inventaris: <?= htmlspecialchars($inventaris['nama']) ?></h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="nama" class="form-label">Nama Inventaris</label>
                <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($inventaris['nama']) ?>" required>
            </div>
            <div class="col-md-3">
                <label for="jenis" class="form-label">Jenis</label>
                <select name="jenis" id="jenis" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="meja" <?= $inventaris['jenis'] == 'meja' ? 'selected' : '' ?>>Meja</option>
                    <option value="kursi" <?= $inventaris['jenis'] == 'kursi' ? 'selected' : '' ?>>Kursi</option>
                    <option value="kipas_angin" <?= $inventaris['jenis'] == 'kipas_angin' ? 'selected' : '' ?>>Kipas Angin</option>
                    <option value="ruang_rapat" <?= $inventaris['jenis'] == 'ruang_rapat' ? 'selected' : '' ?>>Ruang Rapat</option>
                    <option value="kendaraan" <?= $inventaris['jenis'] == 'kendaraan' ? 'selected' : '' ?>>Kendaraan</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="jumlah" class="form-label">Jumlah Total</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control" value="<?= $inventaris['jumlah_total'] ?>" min="1" required>
            </div>
            <div class="col-md-3">
                <label for="foto" class="form-label">Foto (Opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                <small class="text-muted">Format: JPG/PNG, Max 2MB</small>
                <?php if ($inventaris['foto'] !== 'default.jpg'): ?>
                    <div class="mt-2">
                        <img src="../uploads/inventaris/<?= htmlspecialchars($inventaris['foto']) ?>" 
                             alt="Foto <?= htmlspecialchars($inventaris['nama']) ?>" 
                             width="80" class="img-thumbnail">
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <button type="submit" name="update" class="btn btn-primary">Perbarui Inventaris</button>
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