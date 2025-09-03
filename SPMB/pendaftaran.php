<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'ppdb_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = '';
$messageType = '';

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
    $nilai_tk = isset($_POST['nilai_tk']) ? floatval($_POST['nilai_tk']) : null;
    $usia = isset($_POST['usia']) ? intval($_POST['usia']) : null;
    $jarak_rumah = isset($_POST['jarak_rumah']) ? floatval($_POST['jarak_rumah']) : null;
    
    // File upload handling
    $foto = '';
    $dokumen = '';
    $akta_kelahiran = '';
    $kartu_keluarga = '';
    $surat_domisili = '';
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $foto = 'uploads/foto_' . time() . '_' . $filename;
            move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
        }
    }
    
    if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['dokumen']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $dokumen = 'uploads/dokumen_' . time() . '_' . $filename;
            move_uploaded_file($_FILES['dokumen']['tmp_name'], $dokumen);
        }
    }
    
    if (isset($_FILES['akta_kelahiran']) && $_FILES['akta_kelahiran']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['akta_kelahiran']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $akta_kelahiran = 'uploads/akta_kelahiran_' . time() . '_' . $filename;
            move_uploaded_file($_FILES['akta_kelahiran']['tmp_name'], $akta_kelahiran);
        }
    }
    
    if (isset($_FILES['kartu_keluarga']) && $_FILES['kartu_keluarga']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['kartu_keluarga']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $kartu_keluarga = 'uploads/kartu_keluarga_' . time() . '_' . $filename;
            move_uploaded_file($_FILES['kartu_keluarga']['tmp_name'], $kartu_keluarga);
        }
    }
    
    if (isset($_FILES['surat_domisili']) && $_FILES['surat_domisili']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['surat_domisili']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $surat_domisili = 'uploads/surat_domisili_' . time() . '_' . $filename;
            move_uploaded_file($_FILES['surat_domisili']['tmp_name'], $surat_domisili);
        }
    }
    
    try {
        // Validasi server-side untuk semua input
        if (empty($dokumen)) {
            throw new Exception('Ijazah TK wajib diupload dalam format PDF/JPG/PNG');
        }
        if ($nilai_tk === null || $nilai_tk < 0 || $nilai_tk > 100) {
            throw new Exception('Nilai ijazah TK harus di antara 0 hingga 100');
        }
        if ($usia === null || $usia < 4 || $usia > 12) {
            throw new Exception('Usia harus di antara 4 hingga 12 tahun');
        }
        if ($jarak_rumah === null || $jarak_rumah < 0 || $jarak_rumah > 50) {
            throw new Exception('Jarak rumah harus di antara 0 hingga 50 km');
        }
        if (empty($akta_kelahiran)) {
            throw new Exception('Akta Kelahiran wajib diupload dalam format PDF/JPG/PNG');
        }
        if (empty($kartu_keluarga)) {
            throw new Exception('Kartu Keluarga wajib diupload dalam format PDF/JPG/PNG');
        }
        if (empty($surat_domisili)) {
            throw new Exception('Surat Keterangan Domisili RT/RW wajib diupload dalam format PDF/JPG/PNG');
        }

        // Hitung bobot point untuk setiap kriteria
        // 1. Bobot point usia (6 tahun ke atas = 100, di bawah 6 tahun = 50)
        $bobot_usia = ($usia >= 6) ? 100 : 50;
        
        // 2. Bobot point jarak rumah (kurang dari 1 km = 100, 1 km ke atas = 50)
        $bobot_jarak = ($jarak_rumah < 1) ? 100 : 50;
        
        // 3. Bobot point nilai ijazah TK (70 ke atas = 100, di bawah 70 = 50)
        $bobot_nilai_tk = ($nilai_tk >= 70) ? 100 : 50;
        
        // Hitung nilai akhir dengan rata-rata tertimbang (bobot sama untuk setiap kriteria)
        $nilai_akhir = round(($bobot_usia + $bobot_jarak + $bobot_nilai_tk) / 3, 2);

        // Tentukan status otomatis berdasarkan nilai akhir
        $status_seleksi = 'Menunggu';
        $tanggal_seleksi = null;
        $catatan_seleksi = null;
        $admin_seleksi = null;
        if ($nilai_akhir >= 70) {
            $status_seleksi = 'Diterima';
            $tanggal_seleksi = date('Y-m-d H:i:s');
            $catatan_seleksi = "Otomatis diterima berdasarkan nilai akhir {$nilai_akhir} (≥70) - Usia: {$bobot_usia}, Jarak: {$bobot_jarak}, Ijazah: {$bobot_nilai_tk}";
            $admin_seleksi = 'system';
        }

        $stmt = $pdo->prepare("INSERT INTO pendaftar (nama_lengkap, nisn, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_hp, email, asal_sekolah, jurusan, foto, dokumen, tanggal_daftar, usia, jarak_rumah, nilai_tk, nilai_akhir, status_seleksi, tanggal_seleksi, catatan_seleksi, admin_seleksi, akta_kelahiran, kartu_keluarga, surat_domisili) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([$nama_lengkap, $nisn, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $no_hp, $email, $asal_sekolah, $jurusan, $foto, $dokumen, $usia, $jarak_rumah, $nilai_tk, $nilai_akhir, $status_seleksi, $tanggal_seleksi, $catatan_seleksi, $admin_seleksi, $akta_kelahiran, $kartu_keluarga, $surat_domisili]);
        
        $message = ($status_seleksi === 'Diterima')
            ? "Pendaftaran berhasil dan DITERIMA! Nomor pendaftaran Anda: " . $pdo->lastInsertId()
            : "Pendaftaran berhasil! Nomor pendaftaran Anda: " . $pdo->lastInsertId();
        $messageType = 'success';
        
        // Reset form
        $_POST = array();
        
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    } catch(Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran - SPMB SDN Majasetra 01</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-graduation-cap text-2xl"></i>
                    <a href="index.php" class="text-xl font-bold hover:text-blue-200">SPMB SDN Majasetra 01</a>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="index.php" class="hover:text-blue-200 transition">Beranda</a>
                    <a href="index.php#tentang" class="hover:text-blue-200 transition">Tentang</a>
                    <a href="index.php#jadwal" class="hover:text-blue-200 transition">Jadwal</a>
                    <a href="index.php#persyaratan" class="hover:text-blue-200 transition">Persyaratan</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Formulir Pendaftaran Online</h1>
            <p class="text-lg text-gray-600">Silakan lengkapi formulir di bawah ini dengan data yang benar dan lengkap</p>
        </div>

        <?php if ($message): ?>
        <div class="mb-8 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo $message; ?>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-8" id="pendaftaranForm">
            <!-- Data Pribadi -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b border-gray-200 pb-2">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Data Pribadi
                </h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">
                        <span class="text-red-500 text-sm hidden" id="nama_error"></span>
                    </div>
                    
                    <div>
                        <label for="nisn" class="block text-sm font-medium text-gray-700 mb-2">
                            NISN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nisn" name="nisn" required maxlength="10"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo isset($_POST['nisn']) ? htmlspecialchars($_POST['nisn']) : ''; ?>">
                        <span class="text-red-500 text-sm hidden" id="nisn_error"></span>
                    </div>
                    
                    <div>
                        <label for="tempat_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                            Tempat Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo isset($_POST['tempat_lahir']) ? htmlspecialchars($_POST['tempat_lahir']) : ''; ?>">
                        <span class="text-red-500 text-sm hidden" id="tempat_lahir_error"></span>
                    </div>
                    
                    <div>
                        <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo isset($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : ''; ?>">
                        <span class="text-red-500 text-sm hidden" id="tanggal_lahir_error"></span>
                    </div>
                    
                    <div>
                        <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                        <span class="text-red-500 text-sm hidden" id="jenis_kelamin_error"></span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat Lengkap <span class="text-red-500">*</span>
                    </label>
                    <textarea id="alamat" name="alamat" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Masukkan alamat lengkap termasuk RT/RW, Kelurahan, Kecamatan, dan Kota"><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                    <span class="text-red-500 text-sm hidden" id="alamat_error"></span>
                </div>
            </div>

            <!-- Kontak -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b border-gray-200 pb-2">
                    <i class="fas fa-phone mr-2 text-green-600"></i>Data Kontak
                </h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor HP <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" id="no_hp" name="no_hp" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>">
                        <span class="text-red-500 text-sm hidden" id="no_hp_error"></span>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <span class="text-red-500 text-sm hidden" id="email_error"></span>
                    </div>
                </div>
            </div>

            <!-- Data Akademik -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b border-gray-200 pb-2">
                    <i class="fas fa-graduation-cap mr-2 text-purple-600"></i>Data Akademik
                </h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="asal_sekolah" class="block text-sm font-medium text-gray-700 mb-2">
                            Asal Sekolah <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="asal_sekolah" name="asal_sekolah" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Nama TK/PAUD/SD asal"
                               value="<?php echo isset($_POST['asal_sekolah']) ? htmlspecialchars($_POST['asal_sekolah']) : ''; ?>">
                        <span class="text-red-500 text-sm hidden" id="asal_sekolah_error"></span>
                    </div>
                    
                    <div>
                        <label for="jurusan" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilihan Kelas <span class="text-red-500">*</span>
                        </label>
                        <select id="jurusan" name="jurusan" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Kelas</option>
                            <option value="Kelas 1" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Kelas 1') ? 'selected' : ''; ?>>Kelas 1 (Baru)</option>
                            <option value="Kelas 2" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Kelas 2') ? 'selected' : ''; ?>>Kelas 2 (Pindahan)</option>
                            <option value="Kelas 3" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Kelas 3') ? 'selected' : ''; ?>>Kelas 3 (Pindahan)</option>
                            <option value="Kelas 4" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Kelas 4') ? 'selected' : ''; ?>>Kelas 4 (Pindahan)</option>
                            <option value="Kelas 5" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Kelas 5') ? 'selected' : ''; ?>>Kelas 5 (Pindahan)</option>
                            <option value="Kelas 6" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'Kelas 6') ? 'selected' : ''; ?>>Kelas 6 (Pindahan)</option>

                        </select>
                        <span class="text-red-500 text-sm hidden" id="jurusan_error"></span>
                    </div>
                    
                    <div>
                        <label for="nilai_tk" class="block text-sm font-medium text-gray-700 mb-2">
                            Nilai Ijazah TK (0-100) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="nilai_tk" name="nilai_tk" min="0" max="100" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 85.5"
                               value="<?php echo isset($_POST['nilai_tk']) ? htmlspecialchars($_POST['nilai_tk']) : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">Nilai ≥70 = 100 poin, <70 = 50 poin</p>
                        <span class="text-red-500 text-sm hidden" id="nilai_tk_error"></span>
                    </div>
                    
                    <div>
                        <label for="usia" class="block text-sm font-medium text-gray-700 mb-2">
                            Usia (Tahun) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="usia" name="usia" min="4" max="12" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 6"
                               value="<?php echo isset($_POST['usia']) ? htmlspecialchars($_POST['usia']) : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">Usia ≥6 tahun = 100 poin, <6 tahun = 50 poin</p>
                        <span class="text-red-500 text-sm hidden" id="usia_error"></span>
                    </div>
                    
                    <div>
                        <label for="jarak_rumah" class="block text-sm font-medium text-gray-700 mb-2">
                            Jarak Rumah ke Sekolah (Km) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="jarak_rumah" name="jarak_rumah" min="0" max="50" step="0.1" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 0.5"
                               value="<?php echo isset($_POST['jarak_rumah']) ? htmlspecialchars($_POST['jarak_rumah']) : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">Jarak <1 km = 100 poin, ≥1 km = 50 poin</p>
                        <span class="text-red-500 text-sm hidden" id="jarak_rumah_error"></span>
                    </div>
                </div>
                
                <!-- Score Display -->
                <div id="score-display"></div>
            </div>

            <!-- Upload Dokumen -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b border-gray-200 pb-2">
                    <i class="fas fa-upload mr-2 text-orange-600"></i>Upload Dokumen
                </h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="foto" class="block text-sm font-medium text-gray-700 mb-2">
                            Foto 3x4 <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="foto" name="foto" accept="image/*" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Format: JPG, JPEG, PNG. Maksimal 2MB</p>
                        <span class="text-red-500 text-sm hidden" id="foto_error"></span>
                    </div>
                    
                    <div>
                        <label for="dokumen" class="block text-sm font-medium text-gray-700 mb-2">
                            Ijazah TK/PAUD <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="dokumen" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Wajib. Format: PDF, JPG, JPEG, PNG. Maksimal 5MB.</p>
                        <span class="text-red-500 text-sm hidden" id="dokumen_error"></span>
                    </div>
                    
                    <div>
                        <label for="akta_kelahiran" class="block text-sm font-medium text-gray-700 mb-2">
                            Akta Kelahiran <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="akta_kelahiran" name="akta_kelahiran" accept=".pdf,.jpg,.jpeg,.png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Wajib. Format: PDF, JPG, JPEG, PNG. Maksimal 5MB.</p>
                        <span class="text-red-500 text-sm hidden" id="akta_kelahiran_error"></span>
                    </div>
                    
                    <div>
                        <label for="kartu_keluarga" class="block text-sm font-medium text-gray-700 mb-2">
                            Kartu Keluarga <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="kartu_keluarga" name="kartu_keluarga" accept=".pdf,.jpg,.jpeg,.png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Wajib. Format: PDF, JPG, JPEG, PNG. Maksimal 5MB.</p>
                        <span class="text-red-500 text-sm hidden" id="kartu_keluarga_error"></span>
                    </div>
                    
                    <div>
                        <label for="surat_domisili" class="block text-sm font-medium text-gray-700 mb-2">
                            Surat Keterangan Domisili RT/RW <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="surat_domisili" name="surat_domisili" accept=".pdf,.jpg,.jpeg,.png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Wajib. Format: PDF, JPG, JPEG, PNG. Maksimal 5MB.</p>
                        <span class="text-red-500 text-sm hidden" id="surat_domisili_error"></span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Pendaftaran
                </button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2025 SDN Majasetra 01. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Form validation
        document.getElementById('pendaftaranForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Reset all error messages
            document.querySelectorAll('.text-red-500').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Validate Nama Lengkap
            const nama = document.getElementById('nama_lengkap').value.trim();
            if (nama.length < 3) {
                document.getElementById('nama_error').textContent = 'Nama harus minimal 3 karakter';
                document.getElementById('nama_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate NISN
            const nisn = document.getElementById('nisn').value.trim();
            if (nisn.length !== 10 || !/^\d+$/.test(nisn)) {
                document.getElementById('nisn_error').textContent = 'NISN harus 10 digit angka';
                document.getElementById('nisn_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Tempat Lahir
            const tempatLahir = document.getElementById('tempat_lahir').value.trim();
            if (tempatLahir.length < 2) {
                document.getElementById('tempat_lahir_error').textContent = 'Tempat lahir harus minimal 2 karakter';
                document.getElementById('tempat_lahir_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Tanggal Lahir
            const tanggalLahir = document.getElementById('tanggal_lahir').value;
            if (!tanggalLahir) {
                document.getElementById('tanggal_lahir_error').textContent = 'Tanggal lahir harus diisi';
                document.getElementById('tanggal_lahir_error').classList.remove('hidden');
                isValid = false;
            } else {
                const today = new Date();
                const birthDate = new Date(tanggalLahir);
                const age = today.getFullYear() - birthDate.getFullYear();
                if (age < 5 || age > 12) {
                    document.getElementById('tanggal_lahir_error').textContent = 'Usia harus antara 5-12 tahun';
                    document.getElementById('tanggal_lahir_error').classList.remove('hidden');
                    isValid = false;
                }
            }
            
            // Validate Jenis Kelamin
            const jenisKelamin = document.getElementById('jenis_kelamin').value;
            if (!jenisKelamin) {
                document.getElementById('jenis_kelamin_error').textContent = 'Jenis kelamin harus dipilih';
                document.getElementById('jenis_kelamin_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Alamat
            const alamat = document.getElementById('alamat').value.trim();
            if (alamat.length < 10) {
                document.getElementById('alamat_error').textContent = 'Alamat harus minimal 10 karakter';
                document.getElementById('alamat_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate No HP
            const noHp = document.getElementById('no_hp').value.trim();
            if (!/^(\+62|62|0)8[1-9][0-9]{6,9}$/.test(noHp)) {
                document.getElementById('no_hp_error').textContent = 'Format nomor HP tidak valid';
                document.getElementById('no_hp_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Email
            const email = document.getElementById('email').value.trim();
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('email_error').textContent = 'Format email tidak valid';
                document.getElementById('email_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Asal Sekolah
            const asalSekolah = document.getElementById('asal_sekolah').value.trim();
            if (asalSekolah.length < 3) {
                document.getElementById('asal_sekolah_error').textContent = 'Asal sekolah harus minimal 3 karakter';
                document.getElementById('asal_sekolah_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Jurusan
            const jurusan = document.getElementById('jurusan').value;
            if (!jurusan) {
                document.getElementById('jurusan_error').textContent = 'Jurusan harus dipilih';
                document.getElementById('jurusan_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Nilai TK
            const nilaiTkEl = document.getElementById('nilai_tk');
            if (nilaiTkEl) {
                const nilaiTkVal = parseFloat(nilaiTkEl.value);
                if (isNaN(nilaiTkVal) || nilaiTkVal < 0 || nilaiTkVal > 100) {
                    document.getElementById('nilai_tk_error').textContent = 'Nilai TK harus 0-100';
                    document.getElementById('nilai_tk_error').classList.remove('hidden');
                    isValid = false;
                }
            }
            
            // Validate Usia
            const usiaEl = document.getElementById('usia');
            if (usiaEl) {
                const usiaVal = parseInt(usiaEl.value);
                if (isNaN(usiaVal) || usiaVal < 4 || usiaVal > 12) {
                    document.getElementById('usia_error').textContent = 'Usia harus 4-12 tahun';
                    document.getElementById('usia_error').classList.remove('hidden');
                    isValid = false;
                }
            }
            
            // Validate Jarak Rumah
            const jarakEl = document.getElementById('jarak_rumah');
            if (jarakEl) {
                const jarakVal = parseFloat(jarakEl.value);
                if (isNaN(jarakVal) || jarakVal < 0 || jarakVal > 50) {
                    document.getElementById('jarak_rumah_error').textContent = 'Jarak rumah harus 0-50 km';
                    document.getElementById('jarak_rumah_error').classList.remove('hidden');
                    isValid = false;
                }
            }
            
            // Validate Foto
            const foto = document.getElementById('foto').files[0];
            if (!foto) {
                document.getElementById('foto_error').textContent = 'Foto harus diupload';
                document.getElementById('foto_error').classList.remove('hidden');
                isValid = false;
            } else if (foto.size > 2 * 1024 * 1024) { // 2MB
                document.getElementById('foto_error').textContent = 'Ukuran foto maksimal 2MB';
                document.getElementById('foto_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Ijazah TK
            const dokumen = document.getElementById('dokumen').files[0];
            if (!dokumen) {
                document.getElementById('dokumen_error').textContent = 'Ijazah TK wajib diupload';
                document.getElementById('dokumen_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Akta Kelahiran
            const aktaKelahiran = document.getElementById('akta_kelahiran').files[0];
            if (!aktaKelahiran) {
                document.getElementById('akta_kelahiran_error').textContent = 'Akta Kelahiran wajib diupload';
                document.getElementById('akta_kelahiran_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Kartu Keluarga
            const kartuKeluarga = document.getElementById('kartu_keluarga').files[0];
            if (!kartuKeluarga) {
                document.getElementById('kartu_keluarga_error').textContent = 'Kartu Keluarga wajib diupload';
                document.getElementById('kartu_keluarga_error').classList.remove('hidden');
                isValid = false;
            }
            
            // Validate Surat Domisili
            const suratDomisili = document.getElementById('surat_domisili').files[0];
            if (!suratDomisili) {
                document.getElementById('surat_domisili_error').textContent = 'Surat Keterangan Domisili RT/RW wajib diupload';
                document.getElementById('surat_domisili_error').classList.remove('hidden');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = document.querySelector('.text-red-500:not(.hidden)');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // File size validation
        document.getElementById('dokumen').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size > 5 * 1024 * 1024) { // 5MB
                document.getElementById('dokumen_error').textContent = 'Ukuran dokumen maksimal 5MB';
                document.getElementById('dokumen_error').classList.remove('hidden');
                this.value = '';
            } else {
                document.getElementById('dokumen_error').classList.add('hidden');
            }
        });
        
        document.getElementById('akta_kelahiran').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size > 5 * 1024 * 1024) { // 5MB
                document.getElementById('akta_kelahiran_error').textContent = 'Ukuran dokumen maksimal 5MB';
                document.getElementById('akta_kelahiran_error').classList.remove('hidden');
                this.value = '';
            } else {
                document.getElementById('akta_kelahiran_error').classList.add('hidden');
            }
        });
        
        document.getElementById('kartu_keluarga').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size > 5 * 1024 * 1024) { // 5MB
                document.getElementById('kartu_keluarga_error').textContent = 'Ukuran dokumen maksimal 5MB';
                document.getElementById('kartu_keluarga_error').classList.remove('hidden');
                this.value = '';
            } else {
                document.getElementById('kartu_keluarga_error').classList.add('hidden');
            }
        });
        
        document.getElementById('surat_domisili').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size > 5 * 1024 * 1024) { // 5MB
                document.getElementById('surat_domisili_error').textContent = 'Ukuran dokumen maksimal 5MB';
                document.getElementById('surat_domisili_error').classList.remove('hidden');
                this.value = '';
            } else {
                document.getElementById('surat_domisili_error').classList.add('hidden');
            }
        });
        
        // File size validation for foto
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.size > 2 * 1024 * 1024) { // 2MB
                document.getElementById('foto_error').textContent = 'Ukuran foto maksimal 2MB';
                document.getElementById('foto_error').classList.remove('hidden');
                this.value = '';
            } else {
                document.getElementById('foto_error').classList.add('hidden');
            }
        });
        
        // Real-time calculation of final score
        function calculateFinalScore() {
            const nilaiTk = parseFloat(document.getElementById('nilai_tk').value) || 0;
            const usia = parseInt(document.getElementById('usia').value) || 0;
            const jarak = parseFloat(document.getElementById('jarak_rumah').value) || 0;
            
            // Calculate bobot points
            const bobotUsia = (usia >= 6) ? 100 : 50;
            const bobotJarak = (jarak < 1) ? 100 : 50;
            const bobotNilaiTk = (nilaiTk >= 70) ? 100 : 50;
            
            // Calculate final score
            const nilaiAkhir = Math.round(((bobotUsia + bobotJarak + bobotNilaiTk) / 3) * 100) / 100;
            
            // Update display
            const scoreDisplay = document.getElementById('score-display');
            if (scoreDisplay) {
                scoreDisplay.innerHTML = `
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Kalkulasi Nilai Akhir</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Usia (${usia} tahun):</span>
                                <span class="font-semibold ml-2">${bobotUsia} poin</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Jarak (${jarak} km):</span>
                                <span class="font-semibold ml-2">${bobotJarak} poin</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Ijazah TK (${nilaiTk}):</span>
                                <span class="font-semibold ml-2">${bobotNilaiTk} poin</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Nilai Akhir:</span>
                                <span class="font-bold text-lg ml-2 ${nilaiAkhir >= 70 ? 'text-green-600' : 'text-red-600'}">${nilaiAkhir}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            ${nilaiAkhir >= 70 ? '✅ Akan otomatis diterima' : '⏳ Akan menunggu seleksi admin'}
                        </p>
                    </div>
                `;
            }
        }
        
        // Add event listeners for real-time calculation
        document.getElementById('nilai_tk').addEventListener('input', calculateFinalScore);
        document.getElementById('usia').addEventListener('input', calculateFinalScore);
        document.getElementById('jarak_rumah').addEventListener('input', calculateFinalScore);
        
        // Initial calculation
        calculateFinalScore();
    </script>
</body>
</html>
