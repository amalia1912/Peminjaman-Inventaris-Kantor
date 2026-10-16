<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'pegawai') {
    header('Location: ../login.php');
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_inventaris = (int)($_POST['id_inventaris'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $tgl_pinjam = $_POST['tgl_pinjam'] ?? '';

    if (!$id_inventaris || $jumlah <= 0 || !$tgl_pinjam) {
        $error = "Semua field wajib diisi.";
    } else {
        $stmt = $connection->prepare("SELECT jumlah_tersedia FROM inventaris WHERE id_inventaris = ?");
        $stmt->bind_param("i", $id_inventaris);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($jumlah > $row['jumlah_tersedia']) {
                $error = "Jumlah melebihi stok tersedia.";
            } else {
                $stmt = $connection->prepare("INSERT INTO peminjaman (id, id_inventaris, jumlah_pinjam, tgl_pinjam, status) VALUES (?, ?, ?, ?, 'diajukan')");
                $stmt->bind_param("iiis", $_SESSION['id'], $id_inventaris, $jumlah, $tgl_pinjam);
                if ($stmt->execute()) {
                    header("Location: riwayat.php?success=1");
                    exit;
                } else {
                    $error = "Gagal mengajukan peminjaman.";
                }
            }
        } else {
            $error = "Inventaris tidak ditemukan.";
        }
    }
}

$inventaris = $connection->query("SELECT * FROM inventaris WHERE jumlah_tersedia > 0 ORDER BY nama");
$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ajukan Peminjaman</title>
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
    <h4>Ajukan Peminjaman Inventaris</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="inventaris" class="form-label">Pilih Inventaris</label>
                <select name="id_inventaris" id="inventaris" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <?php while ($row = $inventaris->fetch_assoc()): ?>
                        <option value="<?= $row['id_inventaris'] ?>">
                            <?= htmlspecialchars($row['nama']) ?> (Tersedia: <?= $row['jumlah_tersedia'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" required>
            </div>
            <div class="col-md-3">
                <label for="tgl_pinjam" class="form-label">Tanggal Pinjam</label>
                <input type="date" name="tgl_pinjam" id="tgl_pinjam" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Ajukan Peminjaman</button>
                <a href="riwayat.php" class="btn btn-secondary">Batal</a>
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