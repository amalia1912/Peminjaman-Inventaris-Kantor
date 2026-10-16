<?php
session_start();
require_once 'lib/functions.php';
require_once 'lib/auth.php';

echo "<h3>🔍 Test Login untuk 'dela'</h3>";

// Coba login
$role = login('dela', '123');

if ($role) {
    echo "✅ LOGIN BERHASIL!<br>";
    echo "Role: " . htmlspecialchars($role) . "<br>";
    echo "Session: ";
    var_dump($_SESSION);
    
    // Coba redirect manual
    echo '<br><a href="peminjaman/index.php">➡️ Klik ke halaman pegawai</a>';
} else {
    echo "❌ LOGIN GAGAL!<br>";
    echo "Pastikan password di database adalah HASH, bukan 'dela123'!";
}
?>