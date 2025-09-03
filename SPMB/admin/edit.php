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

$message = '';
$messageType = '';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nisn = trim($_POST['nisn']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = trim($_POST['alamat']);
    $no_hp = trim($_POST['no_hp']);
    $email = trim($_POST['email']);
    $asal_sekolah = trim($_POST['asal_sekolah']);
    $jurusan = $_POST['jurusan'];
    
    // File upload handling
    $foto = $pendaftar['foto']; // Keep existing if no new upload
    $dokumen = $pendaftar['dokumen']; // Keep existing if no new upload
    
    $projectRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Delete old photo if exists (use absolute path)
            if (!empty($pendaftar['foto'])) {
                $oldFotoPath = $projectRoot . ltrim($pendaftar['foto'], '/\\');
                if (file_exists($oldFotoPath)) {
                    @unlink($oldFotoPath);
                }
            }
            $foto = 'uploads/foto_' . time() . '_' . $filename;
            move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
        }
    }
    
    if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['dokumen']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Delete old document if exists (use absolute path)
            if (!empty($pendaftar['dokumen'])) {
                $oldDokumenPath = $projectRoot . ltrim($pendaftar['dokumen'], '/\\');
                if (file_exists($oldDokumenPath)) {
                    @unlink($oldDokumenPath);
                }
            }
            $dokumen = 'uploads/dokumen_' . time() . '_' . $filename;
            move_uploaded_file($_FILES['dokumen']['tmp_name'], $dokumen);
        }
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE pendaftar SET nama_lengkap = ?, nisn = ?, tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, alamat = ?, no_hp = ?, email = ?, asal_sekolah = ?, jurusan = ?, foto = ?, dokumen = ? WHERE id = ?");
        
        $stmt->execute([$nama_lengkap, $nisn, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $no_hp, $email, $asal_sekolah, $jurusan, $foto, $dokumen, $id]);
        
        $message = "Data pendaftar berhasil diperbarui!";
        $messageType = 'success';
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE id = ?");
        $stmt->execute([$id]);
        $pendaftar = $stmt->fetch();
        
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pendaftar - SPMB SDN Majasetra 01</title>
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
                        <i class="fas fa-arrow-left mr-2"></i>Dashboard
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
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Edit Data Pendaftar</h2>
                <p class="text-gray-600">Edit informasi pendaftar #<?php echo $pendaftar['id']; ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="detail.php?id=<?php echo $pendaftar['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-eye mr-2"></i>Lihat Detail
                </a>
                <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="mb-8 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-8">
            <!-- Data Pribadi -->
            <div class="mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b border-gray-200 pb-2">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Data Pribadi
                </h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?>">
                    </div>
                    
                    <div>
                        <label for="nisn" class="block text-sm font-medium text-gray-700 mb-2">
                            NISN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nisn" name="nisn" required maxlength="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo htmlspecialchars($pendaftar['nisn']); ?>">
                    </div>
                    
                    <div>
                        <label for="tempat_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                            Tempat Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo htmlspecialchars($pendaftar['tempat_lahir']); ?>">
                    </div>
                    
                    <div>
                        <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo $pendaftar['tanggal_lahir']; ?>">
                    </div>
                    
                    <div>
                        <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo $pendaftar['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo $pendaftar['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat Lengkap <span class="text-red-500">*</span>
                    </label>
                    <textarea id="alamat" name="alamat" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Masukkan alamat lengkap termasuk RT/RW, Kelurahan, Kecamatan, dan Kota"><?php echo htmlspecialchars($pendaftar['alamat']); ?></textarea>
                </div>
            </div>

            <!-- Kontak -->
            <div class="mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b border-gray-200 pb-2">
                    <i class="fas fa-phone mr-2 text-green-600"></i>Data Kontak
                </h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor HP <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" id="no_hp" name="no_hp" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo htmlspecialchars($pendaftar['no_hp']); ?>">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo htmlspecialchars($pendaftar['email']); ?>">
                    </div>
                </div>
            </div>

            <!-- Data Akademik -->
            <div class="mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b border-gray-200 pb-2">
                    <i class="fas fa-graduation-cap mr-2 text-purple-600"></i>Data Akademik
                </h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="asal_sekolah" class="block text-sm font-medium text-gray-700 mb-2">
                            Asal Sekolah <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="asal_sekolah" name="asal_sekolah" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Nama TK/PAUD/SD asal"
                               value="<?php echo htmlspecialchars($pendaftar['asal_sekolah']); ?>">
                    </div>
                    
                    <div>
                        <label for="jurusan" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilihan Kelas <span class="text-red-500">*</span>
                        </label>
                        <select id="jurusan" name="jurusan" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Kelas</option>
                            <option value="Kelas 1" <?php echo $pendaftar['jurusan'] == 'Kelas 1' ? 'selected' : ''; ?>>Kelas 1 (Baru)</option>
                            <option value="Kelas 2" <?php echo $pendaftar['jurusan'] == 'Kelas 2' ? 'selected' : ''; ?>>Kelas 2 (Pindahan)</option>
                            <option value="Kelas 3" <?php echo $pendaftar['jurusan'] == 'Kelas 3' ? 'selected' : ''; ?>>Kelas 3 (Pindahan)</option>
                            <option value="Kelas 4" <?php echo $pendaftar['jurusan'] == 'Kelas 4' ? 'selected' : ''; ?>>Kelas 4 (Pindahan)</option>
                            <option value="Kelas 5" <?php echo $pendaftar['jurusan'] == 'Kelas 5' ? 'selected' : ''; ?>>Kelas 5 (Pindahan)</option>
                            <option value="Kelas 6" <?php echo $pendaftar['jurusan'] == 'Kelas 6' ? 'selected' : ''; ?>>Kelas 6 (Pindahan)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Upload Dokumen -->
            <div class="mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b border-gray-200 pb-2">
                    <i class="fas fa-upload mr-2 text-orange-600"></i>Upload Dokumen
                </h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="foto" class="block text-sm font-medium text-gray-700 mb-2">
                            Foto 3x4
                        </label>
                        <?php 
                        $fotoPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($pendaftar['foto'], '/\\');
                        if (!empty($pendaftar['foto']) && file_exists($fotoPath)): 
                        ?>
                        <div class="mb-2">
                            <img src="../<?php echo htmlspecialchars($pendaftar['foto']); ?>" alt="Foto saat ini" class="h-20 w-20 object-cover rounded border">
                            <p class="text-xs text-gray-500 mt-1">Foto saat ini</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" id="foto" name="foto" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Format: JPG, JPEG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah foto.</p>
                    </div>
                    
                    <div>
                        <label for="dokumen" class="block text-sm font-medium text-gray-700 mb-2">
                            Dokumen Pendukung
                        </label>
                        <?php 
                        $dokumenPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($pendaftar['dokumen'], '/\\');
                        if (!empty($pendaftar['dokumen']) && file_exists($dokumenPath)): 
                        ?>
                        <div class="mb-2">
                            <a href="../<?php echo htmlspecialchars($pendaftar['dokumen']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-file-alt mr-1"></i>Lihat dokumen saat ini
                            </a>
                        </div>
                        <?php endif; ?>
                        <input type="file" id="dokumen" name="dokumen" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, JPEG, PNG. Maksimal 5MB. Kosongkan jika tidak ingin mengubah dokumen.</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</body>
</html>
