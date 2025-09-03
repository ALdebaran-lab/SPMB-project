<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$id = $_GET['id'];

// Database connection
require_once __DIR__ . '/../config/database.php';
try {
    $pdo = getDBConnection();
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get pendaftar data
try {
    $stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE id = ?");
    $stmt->execute([$id]);
    $pendaftar = $stmt->fetch();
    
    if (!$pendaftar) {
        header('Location: dashboard.php');
        exit();
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftar - SPMB SDN Majasetra 01</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-user-shield text-2xl"></i>
                    <h1 class="text-xl font-bold">Admin Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-blue-200">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="dashboard.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Detail Pendaftar</h2>
                <p class="text-gray-600">Informasi lengkap pendaftar #<?php echo $pendaftar['id']; ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="edit.php?id=<?php echo $pendaftar['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Data Display -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Photo and Basic Info -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-6">
                    <div class="flex-shrink-0">
                        <?php 
                        $fotoPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($pendaftar['foto'], '/\\');
                        if (!empty($pendaftar['foto']) && file_exists($fotoPath)): 
                        ?>
                        <img class="h-32 w-32 rounded-lg object-cover border-4 border-gray-200" 
                             src="../<?php echo htmlspecialchars($pendaftar['foto']); ?>" alt="Foto Pendaftar">
                        <?php else: ?>
                        <div class="h-32 w-32 rounded-lg bg-gray-300 flex items-center justify-center border-4 border-gray-200">
                            <i class="fas fa-user text-gray-600 text-4xl"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">NISN</p>
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($pendaftar['nisn']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Kelas</p>
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                                    <?php 
                                    switch($pendaftar['jurusan']) {
                                        case 'RPL': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'TKJ': echo 'bg-green-100 text-green-800'; break;
                                        case 'MM': echo 'bg-purple-100 text-purple-800'; break;
                                        case 'TM': echo 'bg-red-100 text-red-800'; break;
                                        case 'TITL': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'TKR': echo 'bg-indigo-100 text-indigo-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($pendaftar['jurusan']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="p-6 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Data Pribadi
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Tempat Lahir</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($pendaftar['tempat_lahir']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Tanggal Lahir</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('d F Y', strtotime($pendaftar['tanggal_lahir'])); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Jenis Kelamin</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?php echo $pendaftar['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Usia</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?php 
                            $birthDate = new DateTime($pendaftar['tanggal_lahir']);
                            $today = new DateTime();
                            $age = $today->diff($birthDate)->y;
                            echo $age . ' tahun';
                            ?>
                        </p>
                    </div>
                </div>
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-600">Alamat Lengkap</label>
                    <p class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($pendaftar['alamat'])); ?></p>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="p-6 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-phone mr-2 text-green-600"></i>Data Kontak
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nomor HP</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($pendaftar['no_hp']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Email</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="mailto:<?php echo htmlspecialchars($pendaftar['email']); ?>" class="text-blue-600 hover:text-blue-800">
                                <?php echo htmlspecialchars($pendaftar['email']); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="p-6 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-graduation-cap mr-2 text-purple-600"></i>Data Akademik
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Asal Sekolah</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($pendaftar['asal_sekolah']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Pilihan Kelas</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($pendaftar['jurusan']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="p-6 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-alt mr-2 text-orange-600"></i>Dokumen
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Foto 3x4</label>
                        <div class="mt-2">
                            <?php if ($pendaftar['foto'] && file_exists($pendaftar['foto'])): ?>
                            <a href="../<?php echo htmlspecialchars($pendaftar['foto']); ?>" target="_blank" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-eye mr-2"></i>Lihat Foto
                            </a>
                            <?php else: ?>
                            <span class="text-sm text-gray-500">Foto tidak tersedia</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Ijazah TK/PAUD</label>
                        <div class="mt-2">
                            <?php 
                        $dokumenPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($pendaftar['dokumen'], '/\\');
                        if (!empty($pendaftar['dokumen']) && file_exists($dokumenPath)): 
                        ?>
                            <a href="../<?php echo htmlspecialchars($pendaftar['dokumen']); ?>" target="_blank" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-download mr-2"></i>Download Ijazah TK/PAUD
                            </a>
                            <?php else: ?>
                            <span class="text-sm text-gray-500">Dokumen tidak tersedia</span>
                            <?php endif; ?>
                        </div>
                        
                        <label class="block text-sm font-medium text-gray-600 mt-4">Akta Kelahiran</label>
                        <div class="mt-2">
                            <?php 
                        $aktaPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($pendaftar['akta_kelahiran'], '/\\');
                        if (!empty($pendaftar['akta_kelahiran']) && file_exists($aktaPath)): 
                        ?>
                            <a href="../<?php echo htmlspecialchars($pendaftar['akta_kelahiran']); ?>" target="_blank" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-download mr-2"></i>Download Akta Kelahiran
                            </a>
                            <?php else: ?>
                            <span class="text-sm text-gray-500">Dokumen tidak tersedia</span>
                            <?php endif; ?>
                        </div>
                        
                        <label class="block text-sm font-medium text-gray-600 mt-4">Kartu Keluarga</label>
                        <div class="mt-2">
                            <?php 
                        $kkPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($pendaftar['kartu_keluarga'], '/\\');
                        if (!empty($pendaftar['kartu_keluarga']) && file_exists($kkPath)): 
                        ?>
                            <a href="../<?php echo htmlspecialchars($pendaftar['kartu_keluarga']); ?>" target="_blank" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-download mr-2"></i>Download Kartu Keluarga
                            </a>
                            <?php else: ?>
                            <span class="text-sm text-gray-500">Dokumen tidak tersedia</span>
                            <?php endif; ?>
                        </div>
                        
                        <label class="block text-sm font-medium text-gray-600 mt-4">Surat Keterangan Domisili RT/RW</label>
                        <div class="mt-2">
                            <?php 
                        $domisiliPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($pendaftar['surat_domisili'], '/\\');
                        if (!empty($pendaftar['surat_domisili']) && file_exists($domisiliPath)): 
                        ?>
                            <a href="../<?php echo htmlspecialchars($pendaftar['surat_domisili']); ?>" target="_blank" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-download mr-2"></i>Download Surat Domisili
                            </a>
                            <?php else: ?>
                            <span class="text-sm text-gray-500">Dokumen tidak tersedia</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Information -->
            <div class="p-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-check mr-2 text-red-600"></i>Informasi Pendaftaran
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nomor Pendaftaran</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">#<?php echo str_pad($pendaftar['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Tanggal Daftar</label>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('d F Y H:i', strtotime($pendaftar['tanggal_daftar'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
