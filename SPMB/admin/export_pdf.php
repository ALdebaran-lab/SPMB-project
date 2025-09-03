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

// Set headers for PDF download
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftar SPMB - SDN Majasetra 01</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #4F81BD;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .print-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .print-btn:hover {
            background-color: #45a049;
        }
        .back-btn {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #1976D2;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="dashboard.php" class="back-btn">← Kembali ke Dashboard</a>
        <button onclick="window.print()" class="print-btn">🖨️ Print / Download PDF</button>
    </div>

    <div class="header">
        <h1>DATA PENDAFTAR SPMB</h1>
        <p><strong>SDN MAJASETRA 01</strong></p>
        <p>Tahun Ajaran 2025/2026</p>
        <p>Tanggal Export: <?php echo date('d F Y'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Nama Lengkap</th>
                <th style="width: 8%;">NISN</th>
                <th style="width: 10%;">Tempat Lahir</th>
                <th style="width: 8%;">Tanggal Lahir</th>
                <th style="width: 6%;">Usia</th>
                <th style="width: 8%;">Jenis Kelamin</th>
                <th style="width: 20%;">Alamat</th>
                <th style="width: 10%;">No HP</th>
                <th style="width: 12%;">Email</th>
                <th style="width: 15%;">Asal Sekolah</th>
                <th style="width: 8%;">Kelas</th>
                <th style="width: 10%;">Tanggal Daftar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pendaftar)): ?>
            <tr>
                <td colspan="13" style="text-align: center; padding: 20px; color: #666;">
                    Tidak ada data pendaftar
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($pendaftar as $index => $data): ?>
                <tr>
                    <td style="text-align: center;"><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($data['nisn']); ?></td>
                    <td><?php echo htmlspecialchars($data['tempat_lahir']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($data['tanggal_lahir'])); ?></td>
                    <td style="text-align: center;"><?php echo isset($data['usia']) && $data['usia'] ? $data['usia'] . ' tahun' : '-'; ?></td>
                    <td><?php echo $data['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                    <td><?php echo htmlspecialchars($data['alamat']); ?></td>
                    <td><?php echo htmlspecialchars($data['no_hp']); ?></td>
                    <td><?php echo htmlspecialchars($data['email']); ?></td>
                    <td><?php echo htmlspecialchars($data['asal_sekolah']); ?></td>
                    <td><?php echo htmlspecialchars($data['jurusan']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($data['tanggal_daftar'])); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: <?php echo date('d F Y'); ?></p>
        <p>Total Pendaftar: <?php echo count($pendaftar); ?> orang</p>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
