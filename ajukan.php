<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'pegawai') {
    header('Location: ../login.php');
    exit;
}

// ✅ PERBAIKAN: Ambil ID user dari session yang benar
// Di login(), kita simpan $_SESSION['id'] = users.id
$id = $_SESSION['id'];

$error = $success = '';

// Jika dikirim dari daftar inventaris
$preselected_id = (int)($_GET['id_inventaris'] ?? 0);

// Validasi preselected ID
if ($preselected_id) {
    $check = $connection->prepare("SELECT id_inventaris FROM inventaris WHERE id_inventaris = ? AND jumlah_tersedia > 0");
    $check->bind_param("i", $preselected_id);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        $preselected_id = 0; // reset jika tidak valid
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_inventaris = (int)($_POST['id_inventaris'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? 0);
    $tgl_pinjam = $_POST['tgl_pinjam'] ?? '';

    // Validasi tanggal: tidak boleh sebelum hari ini
    $today = date('Y-m-d');
    if (!$id_inventaris || $jumlah <= 0 || !$tgl_pinjam) {
        $error = "Semua field wajib diisi.";
    } elseif ($tgl_pinjam < $today) {
        $error = "Tanggal pinjam tidak boleh di masa lalu.";
    } else {
        // Cek stok
        $stmt = $connection->prepare("SELECT jumlah_tersedia, nama FROM inventaris WHERE id_inventaris = ?");
        $stmt->bind_param("i", $id_inventaris);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($jumlah > $row['jumlah_tersedia']) {
                $error = "Jumlah melebihi stok tersedia (tersedia: {$row['jumlah_tersedia']} unit).";
            } else {
                // ✅ INSERT dengan kolom `id` (foreign key ke users.id)
                $stmt = $connection->prepare("INSERT INTO peminjaman (id, id_inventaris, jumlah_pinjam, tgl_pinjam, status) VALUES (?, ?, ?, ?, 'diajukan')");
                $stmt->bind_param("iiis", $id, $id_inventaris, $jumlah, $tgl_pinjam);
                if ($stmt->execute()) {
                    $id_peminjaman = $connection->insert_id;
                    header("Location: surat_pengajuan.php?id=$id_peminjaman");
                    exit;
                } else {
                    $error = "Gagal mengajukan peminjaman. Silakan coba lagi.";
                }
            }
        } else {
            $error = "Inventaris tidak ditemukan.";
        }
    }
}

// Ambil daftar inventaris (hanya yang tersedia)
$inventaris = $connection->query("
    SELECT id_inventaris, nama, jenis, jumlah_tersedia 
    FROM inventaris 
    WHERE jumlah_tersedia > 0
    ORDER BY nama
");

$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ajukan Peminjaman</title>
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
        <h4 class="mb-0">Ajukan Peminjaman Inventaris</h4>
        <a href="daftar_inventaris.php" class="btn btn-outline-primary">
            <i class="fas fa-list"></i> Lihat Daftar Inventaris
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label for="inventaris" class="form-label">Pilih Inventaris <span class="text-danger">*</span></label>
            <select name="id_inventaris" id="inventaris" class="form-select" required onchange="updateStatus(this.value)">
                <option value="">-- Pilih Inventaris --</option>
                <?php while ($row = $inventaris->fetch_assoc()): ?>
                    <option value="<?= $row['id_inventaris'] ?>" 
                            data-stok="<?= $row['jumlah_tersedia'] ?>"
                            <?= ($row['id_inventaris'] == $preselected_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nama']) ?> 
                        (<?= ucfirst(str_replace('_', ' ', $row['jenis'])) ?>, Tersedia: <?= $row['jumlah_tersedia'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
            <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" placeholder="Contoh: 1" required>
        </div>

        <div class="col-md-3">
            <label for="tgl_pinjam" class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
            <input type="date" name="tgl_pinjam" id="tgl_pinjam" class="form-control" min="<?= date('Y-m-d') ?>" required>
        </div>

        <!-- Status Ketersediaan -->
        <div class="col-12">
            <div id="statusBox" class="alert alert-info" style="display:none;">
                <strong>Status:</strong> <span id="statusText"></span>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Ajukan Peminjaman</button>
            <a href="riwayat.php" class="btn btn-secondary">Lihat Riwayat</a>
        </div>
    </form>
</div>

<script>
// Set today as min date
document.getElementById('tgl_pinjam').min = new Date().toISOString().split('T')[0];

function updateStatus(inventarisId) {
    const select = document.getElementById('inventaris');
    const option = select.querySelector(`option[value="${inventarisId}"]`);
    const stok = option ? parseInt(option.getAttribute('data-stok')) : 0;
    const statusBox = document.getElementById('statusBox');
    const statusText = document.getElementById('statusText');

    if (stok > 0) {
        statusText.innerHTML = `Barang tersedia (${stok} unit)`;
        statusBox.className = 'alert alert-success';
    } else {
        statusText.innerHTML = 'Barang tidak tersedia';
        statusBox.className = 'alert alert-danger';
    }
    statusBox.style.display = 'block';
}

// Trigger on page load if preselected
window.addEventListener('load', function() {
    const preselected = <?= json_encode($preselected_id) ?>;
    if (preselected) {
        updateStatus(preselected);
    }
});
</script>

<?php
if (is_dir("../views/$THEME")) {
    include "../views/$THEME/lower_block.php";
    include "../views/$THEME/footer.php";
}
?>
</body>
</html>