<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'pegawai') {
    header('Location: ../login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die("ID inventaris tidak valid.");
}

// Ambil data inventaris
$stmt = $connection->prepare("
    SELECT * FROM inventaris WHERE id_inventaris = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$inventaris = $result->fetch_assoc();

if (!$inventaris) {
    die("Inventaris tidak ditemukan.");
}

$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php
if (is_dir("../views/$THEME")) {
    include "../views/$THEME/header.php";
    include "../views/$THEME/sidebar.php";
    include "../views/$THEME/topnav.php";
    include "../views/$THEME/upper_block.php";
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Detail Inventaris</h4>
        <a href="daftar_inventaris.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <?php if ($inventaris['foto'] && file_exists('../uploads/inventaris/' . $inventaris['foto'])): ?>
                        <img src="../uploads/inventaris/<?= htmlspecialchars($inventaris['foto']) ?>" 
                             alt="<?= htmlspecialchars($inventaris['nama']) ?>"
                             class="img-fluid rounded">
                    <?php else: ?>
                        <div class="bg-light text-center py-5">Tidak ada foto</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-7">
                    <h5 class="mb-3"><?= htmlspecialchars($inventaris['nama']) ?></h5>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td><strong>Jenis</strong></td>
                            <td><?= ucfirst(str_replace('_', ' ', $inventaris['jenis'])) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><?= $inventaris['jumlah_total'] ?> unit</td>
                        </tr>
                        <tr>
                            <td><strong>Tersedia</strong></td>
                            <td>
                                <?php if ($inventaris['jumlah_tersedia'] > 0): ?>
                                    <span class="badge bg-success"><?= $inventaris['jumlah_tersedia'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($inventaris['keterangan'])): ?>
                        <tr>
                            <td><strong>Keterangan</strong></td>
                            <td><?= nl2br(htmlspecialchars($inventaris['keterangan'])) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>

                    <!-- Tombol Ajukan -->
                    <div class="mt-3">
                        <?php if ($inventaris['jumlah_tersedia'] > 0): ?>
                            <a href="ajukan.php?id_inventaris=<?= $inventaris['id_inventaris'] ?>" 
                               class="btn btn-primary">Ajukan Peminjaman</a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Stok Habis</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (is_dir("../views/$THEME")) {
    include "../views/$THEME/lower_block.php";
    include "../views/$THEME/footer.php";
}
?>
</body>
</html>