<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php'; // menghasilkan $connection

if ($_SESSION['role'] !== 'pegawai') {
    header('Location: ../login.php');
    exit;
}

$id = $_SESSION['id'];

// Ambil riwayat peminjaman user
$peminjaman = $connection->query("
    SELECT p.*, i.nama AS inventaris_nama, i.jenis
    FROM peminjaman p
    JOIN inventaris i ON p.id_inventaris = i.id_inventaris
    WHERE p.id = $id
    ORDER BY p.tgl_pinjam DESC
");

$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Peminjaman</title>
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
    <h4 class="mb-4">Riwayat Peminjaman Saya</h4>

    <?php if ($peminjaman && $peminjaman->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
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
                    <?php while ($row = $peminjaman->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['inventaris_nama']) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $row['jenis'])) ?></td>
                        <td><?= $row['jumlah_pinjam'] ?></td>
                        <td><?= $row['tgl_pinjam'] ?></td>
                        <td><?= $row['tgl_kembali'] ? $row['tgl_kembali'] : '-' ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $row['status'] == 'disetujui' ? 'success' : 
                                ($row['status'] == 'dikembalikan' ? 'secondary' : 
                                ($row['status'] == 'ditolak' ? 'danger' : 'warning')) 
                            ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'diajukan'): ?>
                                <a href="batal_peminjaman.php?id=<?= $row['id_peminjaman'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Batalkan pengajuan ini?')">
                                   Batal
                                </a>
                            <?php else: ?>
                                <span class="text-muted">–</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Belum ada riwayat peminjaman.</div>
    <?php endif; ?>

    <div class="mt-3">
        <a href="ajukan.php" class="btn btn-primary">Ajukan Baru</a>
        <a href="../index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
</div>

<?php
include "../views/$THEME/lower_block.php";
include "../views/$THEME/footer.php";
?>
</body>
</html>
