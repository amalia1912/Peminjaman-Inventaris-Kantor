<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = $success = '';

// Proses hapus inventaris
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $connection->prepare("SELECT foto FROM inventaris WHERE id_inventaris = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['foto'] !== 'default.jpg') {
        $file_to_delete = __DIR__ . '/../uploads/inventaris/' . $row['foto'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }

    $connection->query("DELETE FROM inventaris WHERE id_inventaris = $id");
    header("Location: kelola_inventaris.php?success=1");
    exit;
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "Inventaris berhasil ditambahkan.";
}

$inventaris = $connection->query("SELECT * FROM inventaris ORDER BY nama");
$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Inventaris</title>
    <link href="../assets/default/css/bootstrap.min.css" rel="stylesheet">
    <!-- Opsional: Font Awesome -->
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
    <!-- 🔥 Judul + Tombol Tambah (Hanya Sekali!) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Kelola Inventaris Kantor</h4>
        <a href="tambah_inventaris.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Tambah Inventaris
        </a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Daftar Inventaris -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Total</th>
                    <th>Tersedia</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $inventaris->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="/peminjaman-inventaris/uploads/inventaris/<?= htmlspecialchars($row['foto']) ?>" 
                             alt="<?= htmlspecialchars($row['nama']) ?>"
                             width="80" class="img-thumbnail">
                    </td>
                    <td>
                        <a href="detail_inventaris.php?id=<?= $row['id_inventaris'] ?>" 
                           class="text-decoration-none">
                            <?= htmlspecialchars($row['nama']) ?>
                        </a>
                    </td>
                    <td><?= ucfirst(str_replace('_', ' ', $row['jenis'])) ?></td>
                    <td><?= $row['jumlah_total'] ?></td>
                    <td><?= $row['jumlah_tersedia'] ?></td>
                    <td>
                        <a href="edit_inventaris.php?id=<?= $row['id_inventaris'] ?>" 
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="?delete=<?= $row['id_inventaris'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Hapus inventaris ini? Foto juga akan dihapus.')">
                            <i class="fas fa-trash"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
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