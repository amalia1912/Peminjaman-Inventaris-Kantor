<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = $success = '';

// Ambil daftar user & inventaris — PERBAIKAN DI SINI
$users = $connection->query("SELECT id, username, nama_lengkap FROM users ORDER BY nama_lengkap"); // ✅
$inventaris = $connection->query("SELECT id_inventaris, nama, jenis FROM inventaris ORDER BY nama");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $id = (int)($_POST['id'] ?? 0);
    $id_inventaris = (int)($_POST['id_inventaris'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $tgl_pinjam = $_POST['tgl_pinjam'] ?? '';
    $status = $_POST['status'] ?? 'diajukan';
    $catatan = $_POST['catatan'] ?? '';

    if (!$id || !$id_inventaris || $jumlah <= 0 || !$tgl_pinjam) {
        $error = "Semua field wajib diisi.";
    } else {
        // Cek stok tersedia
        $stmt = $connection->prepare("SELECT jumlah_tersedia FROM inventaris WHERE id_inventaris = ?");
        $stmt->bind_param("i", $id_inventaris);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($jumlah > $row['jumlah_tersedia']) {
                $error = "Jumlah melebihi stok tersedia.";
            } else {
                // Simpan peminjaman — gunakan id
                $stmt = $connection->prepare("INSERT INTO peminjaman (id, id_inventaris, jumlah_pinjam, tgl_pinjam, status, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiisss", $id, $id_inventaris, $jumlah, $tgl_pinjam, $status, $catatan);
                if ($stmt->execute()) {
                    // Kurangi stok jika status disetujui
                    if ($status === 'disetujui') {
                        $connection->query("
                            UPDATE inventaris 
                            SET jumlah_tersedia = jumlah_tersedia - $jumlah 
                            WHERE id_inventaris = $id_inventaris
                        ");
                    }
                    $success = "Peminjaman berhasil ditambahkan.";
                } else {
                    $error = "Gagal menambahkan peminjaman.";
                }
            }
        } else {
            $error = "Inventaris tidak ditemukan.";
        }
    }
}

$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Peminjaman</title>
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tambah Peminjaman Manual</h4>
        <a href="kelola_peminjaman.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="id" class="form-label">Pegawai</label>
                <select name="id" id="id" class="form-select" required>
                    <option value="">-- Pilih Pegawai --</option>
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <!-- PERBAIKAN: gunakan id -->
                        <option value="<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['nama_lengkap'] ?: $row['username']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="id_inventaris" class="form-label">Inventaris</label>
                <select name="id_inventaris" id="id_inventaris" class="form-select" required>
                    <option value="">-- Pilih Inventaris --</option>
                    <?php while ($row = $inventaris->fetch_assoc()): ?>
                        <option value="<?= $row['id_inventaris'] ?>">
                            <?= htmlspecialchars($row['nama']) ?> (<?= ucfirst(str_replace('_', ' ', $row['jenis'])) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" required>
            </div>
            <div class="col-md-4">
                <label for="tgl_pinjam" class="form-label">Tanggal Pinjam</label>
                <input type="date" name="tgl_pinjam" id="tgl_pinjam" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="diajukan">Diajukan</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                    <option value="dikembalikan">Dikembalikan</option>
                </select>
            </div>
            <div class="col-12">
                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                <textarea name="catatan" id="catatan" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-12">
                <button type="submit" name="add" class="btn btn-primary">Simpan Peminjaman</button>
                <a href="kelola_peminjaman.php" class="btn btn-secondary">Batal</a>
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