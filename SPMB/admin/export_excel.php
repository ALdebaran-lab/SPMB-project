<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once __DIR__ . '/../config/database.php';
try {
    $pdo = getDBConnection();
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get all pendaftar data
try {
    $stmt = $pdo->query("SELECT * FROM pendaftar ORDER BY tanggal_daftar DESC");
    $pendaftar = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Data_Pendaftar_SPMB_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Output Excel content
?>
<table border="1">
    <thead>
        <tr style="background-color: #4F81BD; color: white; font-weight: bold;">
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>NISN</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th>Usia</th>
            <th>Jenis Kelamin</th>
            <th>Alamat</th>
            <th>No HP</th>
            <th>Email</th>
            <th>Asal Sekolah</th>
            <th>Kelas</th>
            <th>Tanggal Daftar</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pendaftar as $index => $data): ?>
        <tr>
            <td><?php echo $index + 1; ?></td>
            <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
            <td><?php echo htmlspecialchars($data['nisn']); ?></td>
            <td><?php echo htmlspecialchars($data['tempat_lahir']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($data['tanggal_lahir'])); ?></td>
            <td><?php echo isset($data['usia']) && $data['usia'] ? $data['usia'] . ' tahun' : '-'; ?></td>
            <td><?php echo $data['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
            <td><?php echo htmlspecialchars($data['alamat']); ?></td>
            <td><?php echo htmlspecialchars($data['no_hp']); ?></td>
            <td><?php echo htmlspecialchars($data['email']); ?></td>
            <td><?php echo htmlspecialchars($data['asal_sekolah']); ?></td>
            <td><?php echo htmlspecialchars($data['jurusan']); ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($data['tanggal_daftar'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
