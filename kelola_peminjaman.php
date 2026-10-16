<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Proses aksi (approve, return, reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_peminjaman = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $connection->query("
            UPDATE inventaris i
            JOIN peminjaman p ON i.id_inventaris = p.id_inventaris
            SET i.jumlah_tersedia = i.jumlah_tersedia - p.jumlah_pinjam
            WHERE p.id_peminjaman = $id_peminjaman AND p.status = 'diajukan'
        ");
        $connection->query("UPDATE peminjaman SET status = 'disetujui' WHERE id_peminjaman = $id_peminjaman");
    } 
    elseif ($action === 'return') {
        $connection->query("
            UPDATE inventaris i
            JOIN peminjaman p ON i.id_inventaris = p.id_inventaris
            SET i.jumlah_tersedia = i.jumlah_tersedia + p.jumlah_pinjam
            WHERE p.id_peminjaman = $id_peminjaman AND p.status = 'disetujui'
        ");
        $connection->query("UPDATE peminjaman SET status = 'dikembalikan', tgl_kembali = CURDATE() WHERE id_peminjaman = $id_peminjaman");
    } 
    elseif ($action === 'reject') {
        $connection->query("UPDATE peminjaman SET status = 'ditolak' WHERE id_peminjaman = $id_peminjaman");
    }

    header("Location: kelola_peminjaman.php?success=1");
    exit;
}

$peminjaman = $connection->query("
    SELECT p.*, u.nama_lengkap, i.nama AS inventaris_nama, i.jenis
    FROM peminjaman p
    JOIN users u ON p.id = u.id
    JOIN inventaris i ON p.id_inventaris = i.id_inventaris
    ORDER BY p.tgl_pinjam DESC
");

$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Peminjaman</title>
    <link href="../assets/default/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        <h4 class="mb-0">Kelola Peminjaman Inventaris</h4>
        <a href="tambah_peminjaman.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Tambah Peminjaman
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Aksi berhasil diproses.</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Pegawai</th>
                    <th>Inventaris</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($peminjaman && $peminjaman->num_rows > 0): ?>
                    <?php while ($row = $peminjaman->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['inventaris_nama']) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $row['jenis'])) ?></td>
                        <td><?= $row['jumlah_pinjam'] ?></td>
                        <td><?= $row['tgl_pinjam'] ?></td>
                        <td>
                            <?php if ($row['tgl_kembali']): ?>
                                <span class="text-success"><?= $row['tgl_kembali'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">–</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= 
                                $row['status'] == 'disetujui' ? 'success' : 
                                ($row['status'] == 'dikembalikan' ? 'secondary' : 
                                ($row['status'] == 'ditolak' ? 'danger' : 'warning')) 
                            ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'diajukan'): ?>
                                <a href="?action=approve&id=<?= $row['id_peminjaman'] ?>" class="btn btn-sm btn-success">✓ Setujui</a>
                                <a href="?action=reject&id=<?= $row['id_peminjaman'] ?>" class="btn btn-sm btn-danger">✗ Tolak</a>
                            <?php elseif ($row['status'] == 'disetujui'): ?>
                                <a href="?action=return&id=<?= $row['id_peminjaman'] ?>" class="btn btn-sm btn-info">↩️ Kembalikan</a>
                            <?php else: ?>
                                <span class="text-muted">–</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">Belum ada data peminjaman.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include "../views/$THEME/lower_block.php";
include "../views/$THEME/footer.php";
?>
</body>
</html>
