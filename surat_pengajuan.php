<?php
session_start();
require_once '../config/database.php';

if ($_SESSION['role'] !== 'pegawai') {
    header('Location: ../login.php');
    exit;
}

$id_peminjaman = (int)($_GET['id'] ?? 0);

if (!$id_peminjaman) {
    die("ID peminjaman tidak valid.");
}

// ✅ JOIN dengan `p.id = u.id` (karena pakai kolom `id`)
$stmt = $connection->prepare("
    SELECT p.*, u.nama_lengkap, i.nama AS inventaris_nama, i.jenis
    FROM peminjaman p
    JOIN users u ON p.id = u.id          -- PERBAIKAN UTAMA
    JOIN inventaris i ON p.id_inventaris = i.id_inventaris
    WHERE p.id_peminjaman = ? AND p.id = ?
");
$stmt->bind_param("ii", $id_peminjaman, $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    die("Data peminjaman tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengajuan Peminjaman</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .content { line-height: 1.6; }
        .footer { margin-top: 50px; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="header">
    <h2>SURAT PENGAJUAN PEMINJAMAN INVENTARIS</h2>
    <p>No. Pengajuan: <?= $row['id_peminjaman'] ?></p>
</div>

<div class="content">
    <p>Kepada Yth.<br>
    Admin Inventaris Kantor<br>
    Di Tempat</p>

    <p>Dengan hormat,</p>

    <p>Saya yang bertanda tangan di bawah ini:</p>

    <table>
        <tr><td>Nama</td><td>: <?= htmlspecialchars($row['nama_lengkap']) ?></td></tr>
        <tr><td>Tanggal Pengajuan</td><td>: <?= date('d-m-Y') ?></td></tr>
    </table>

    <p>Bermaksud mengajukan peminjaman inventaris sebagai berikut:</p>

    <table>
        <tr><th>Inventaris</th><th>Jenis</th><th>Jumlah</th><th>Tanggal Pinjam</th></tr>
        <tr>
            <td><?= htmlspecialchars($row['inventaris_nama']) ?></td>
            <td><?= ucfirst(str_replace('_', ' ', $row['jenis'])) ?></td>
            <td><?= $row['jumlah_pinjam'] ?></td>
            <td><?= date('d-m-Y', strtotime($row['tgl_pinjam'])) ?></td>
        </tr>
    </table>

    <p>Demikian surat pengajuan ini saya buat untuk dapat diproses lebih lanjut.</p>

    <div class="footer">
        <p>Hormat saya,<br><br><br>
        <?= htmlspecialchars($row['nama_lengkap']) ?>
        </p>
    </div>
</div>

<div style="margin-top: 30px; text-align: center;">
    <a href="ajukan.php" class="btn btn-primary">Ajukan Lagi</a>
    <a href="riwayat.php" class="btn btn-secondary">Lihat Riwayat</a>
    <button onclick="window.print()" class="btn btn-success">Cetak Surat</button>
</div>

</body>
</html>