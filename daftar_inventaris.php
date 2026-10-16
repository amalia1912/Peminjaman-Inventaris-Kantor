<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'pegawai') {
    header('Location: ../login.php');
    exit;
}

// Ambil data inventaris
$inventaris = $connection->query("
    SELECT id_inventaris, nama, jenis, jumlah_tersedia, jumlah_total, foto
    FROM inventaris
    ORDER BY nama
");

// Ambil data untuk grafik
$stokData = $connection->query("
    SELECT jenis, SUM(jumlah_tersedia) as tersedia, SUM(jumlah_total) as total
    FROM inventaris
    GROUP BY jenis
");

$THEME = $_ENV['THEME'] ?? 'default';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <h4 class="mb-4">Daftar Inventaris Kantor</h4>

    <!-- Grafik Stok -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Grafik Ketersediaan Stok per Jenis</h5>
        </div>
        <div class="card-body">
            <canvas id="stokChart" height="100"></canvas>
        </div>
    </div>

    <!-- Tabel Inventaris -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="bg-light">
                <tr>
                    <th>Foto</th>
                    <th>Nama Inventaris</th>
                    <th>Jenis</th>
                    <th>Stok Tersedia</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $inventaris->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($row['foto'] && file_exists('../uploads/inventaris/' . $row['foto'])): ?>
                            <img src="../uploads/inventaris/<?= htmlspecialchars($row['foto']) ?>" 
                                 alt="<?= htmlspecialchars($row['nama']) ?>"
                                 width="60" class="img-thumbnail">
                        <?php else: ?>
                            <span class="text-muted">–</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="detail_inventaris.php?id=<?= $row['id_inventaris'] ?>" 
                           class="text-decoration-none">
                            <?= htmlspecialchars($row['nama']) ?>
                        </a>
                    </td>
                    <td><?= ucfirst(str_replace('_', ' ', $row['jenis'])) ?></td>
                    <td>
                        <?php if ($row['jumlah_tersedia'] > 0): ?>
                            <span class="badge bg-success"><?= $row['jumlah_tersedia'] ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger">Habis</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['jumlah_tersedia'] > 0): ?>
                            <a href="ajukan.php?id_inventaris=<?= $row['id_inventaris'] ?>" 
                               class="btn btn-sm btn-primary">
                                Ajukan
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled>Habis</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Data untuk grafik
const ctx = document.getElementById('stokChart').getContext('2d');
const labels = [<?php 
    $labels = [];
    $tersedia = [];
    $total = [];
    while ($row = $stokData->fetch_assoc()) {
        $labels[] = '"' . ucfirst(str_replace('_', ' ', $row['jenis'])) . '"';
        $tersedia[] = $row['tersedia'];
        $total[] = $row['total'];
    }
    echo implode(', ', $labels);
?>];
const data = {
    labels: labels,
    datasets: [
        {
            label: 'Tersedia',
            data: [<?= implode(', ', $tersedia) ?>],
            backgroundColor: '#28a745'
        },
        {
            label: 'Total',
            data: [<?= implode(', ', $total) ?>],
            backgroundColor: '#6c757d',
            borderWidth: 1
        }
    ]
};
new Chart(ctx, {
    type: 'bar',
    data: data,
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
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