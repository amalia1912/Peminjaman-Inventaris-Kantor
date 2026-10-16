<?php
session_start();
require_once '../lib/auth.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'pegawai') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: riwayat.php');
    exit;
}

$id_peminjaman = (int)$_GET['id'];
$id = $_SESSION['id']; // Ambil ID user yang sedang login

// ✅ PERBAIKAN: Ganti `id` menjadi `id`
$stmt = $connection->prepare("
    SELECT status FROM peminjaman 
    WHERE id_peminjaman = ? AND id = ?
");
$stmt->bind_param("ii", $id_peminjaman, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'diajukan') {
        // Hapus peminjaman
        $connection->query("DELETE FROM peminjaman WHERE id_peminjaman = $id_peminjaman");
        header("Location: riwayat.php?batal=1");
        exit;
    }
}

header("Location: riwayat.php?error=1");
exit;
?>