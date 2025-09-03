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
$pendaftar_data = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nisn = trim($_POST['nisn']);
    $email = trim($_POST['email']);
    
    if (empty($nisn) || empty($email)) {
        $message = "Mohon isi NISN dan email dengan lengkap!";
        $messageType = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE nisn = ? AND email = ?");
            $stmt->execute([$nisn, $email]);
            $pendaftar_data = $stmt->fetch();
            
            if (!$pendaftar_data) {
                $message = "Data pendaftar tidak ditemukan! Pastikan NISN dan email sudah benar.";
                $messageType = 'error';
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran - SPMB SDN Majasetra 01</title>
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
                    <a href="pendaftaran.php" class="hover:text-blue-200 transition">Daftar</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Cek Status Pendaftaran</h1>
            <p class="text-xl text-gray-600">Masukkan NISN dan email untuk melihat status pendaftaran Anda</p>
        </div>

        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <div class="flex items-center justify-center">
                <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <form method="POST" class="space-y-6">
                <div>
                    <label for="nisn" class="block text-sm font-medium text-gray-700 mb-2">NISN (Nomor Induk Siswa Nasional)</label>
                    <input type="text" id="nisn" name="nisn" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Masukkan NISN Anda"
                           value="<?php echo isset($_POST['nisn']) ? htmlspecialchars($_POST['nisn']) : ''; ?>">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Masukkan email yang didaftarkan"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="text-center">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                        <i class="fas fa-search mr-2"></i>Cek Status
                    </button>
                </div>
            </form>
        </div>

        <!-- Result Display -->
        <?php if ($pendaftar_data): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 text-white">
                <h2 class="text-xl font-semibold">Status Pendaftaran</h2>
            </div>
            
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Informasi Pribadi</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-user text-blue-600 w-5 mr-3"></i>
                                <span class="text-gray-700"><?php echo htmlspecialchars($pendaftar_data['nama_lengkap']); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-id-card text-blue-600 w-5 mr-3"></i>
                                <span class="text-gray-700"><?php echo htmlspecialchars($pendaftar_data['nisn']); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar text-blue-600 w-5 mr-3"></i>
                                <span class="text-gray-700"><?php echo date('d F Y', strtotime($pendaftar_data['tanggal_lahir'])); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-venus-mars text-blue-600 w-5 mr-3"></i>
                                <span class="text-gray-700"><?php echo $pendaftar_data['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Academic Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Informasi Akademik</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <i class="fas fa-school text-blue-600 w-5 mr-3"></i>
                                <span class="text-gray-700"><?php echo htmlspecialchars($pendaftar_data['asal_sekolah']); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-graduation-cap text-blue-600 w-5 mr-3"></i>
                                <span class="text-gray-700"><?php echo htmlspecialchars($pendaftar_data['jurusan']); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt text-blue-600 w-5 mr-3"></i>
                                <span class="text-gray-700"><?php echo date('d F Y H:i', strtotime($pendaftar_data['tanggal_daftar'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Selection Status -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Status Seleksi</h3>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">Status:</span>
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                                <?php 
                                switch($pendaftar_data['status_seleksi'] ?? 'Menunggu') {
                                    case 'Menunggu': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'Diterima': echo 'bg-green-100 text-green-800'; break;
                                    case 'Ditolak': echo 'bg-red-100 text-red-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?php echo htmlspecialchars($pendaftar_data['status_seleksi'] ?? 'Menunggu'); ?>
                            </span>
                        </div>
                        
                        <?php if (isset($pendaftar_data['nilai_tk']) && $pendaftar_data['nilai_tk'] !== null): ?>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">Nilai TK:</span>
                            <span class="font-semibold text-lg <?php echo $pendaftar_data['nilai_tk'] >= 70 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $pendaftar_data['nilai_tk']; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($pendaftar_data['catatan_seleksi']) && !empty($pendaftar_data['catatan_seleksi'])): ?>
                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <?php echo htmlspecialchars($pendaftar_data['catatan_seleksi']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Next Steps -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="text-lg font-medium text-blue-900 mb-3">
                        <i class="fas fa-info-circle mr-2"></i>Langkah Selanjutnya
                    </h3>
                    <?php if (($pendaftar_data['status_seleksi'] ?? 'Menunggu') == 'Diterima'): ?>
                        <div class="text-blue-800">
                            <p class="mb-2"><strong>Selamat! Anda diterima di SDN Majasetra 01.</strong></p>
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                <li>Silakan lakukan pendaftaran ulang sesuai jadwal yang ditentukan</li>
                                <li>Siapkan dokumen yang diperlukan (KK, Akta Kelahiran, dll.)</li>
                                <li>Hadir ke sekolah pada waktu yang ditentukan</li>
                                <li>Jika ada pertanyaan, hubungi pihak sekolah</li>
                            </ul>
                        </div>
                    <?php elseif (($pendaftar_data['status_seleksi'] ?? 'Menunggu') == 'Ditolak'): ?>
                        <div class="text-blue-800">
                            <p class="mb-2"><strong>Mohon maaf, Anda belum dapat diterima pada tahun ini.</strong></p>
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                <li>Jangan berkecil hati, tetap semangat belajar</li>
                                <li>Anda dapat mencoba mendaftar lagi di tahun berikutnya</li>
                                <li>Fokus pada pengembangan kemampuan akademik</li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="text-blue-800">
                            <p class="mb-2"><strong>Pendaftaran Anda sedang dalam proses seleksi.</strong></p>
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                <li>Tim admin sedang memproses pendaftaran Anda</li>
                                <li>Hasil seleksi akan diumumkan dalam waktu dekat</li>
                                <li>Pastikan data yang didaftarkan sudah benar</li>
                                <li>Pantau status pendaftaran secara berkala</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Additional Information -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-yellow-900 mb-3">
                <i class="fas fa-lightbulb mr-2"></i>Informasi Tambahan
            </h3>
            <div class="text-yellow-800 text-sm space-y-2">
                <p><strong>Nilai TK ≥70:</strong> Pendaftar dengan nilai TK 70 atau lebih akan otomatis diterima.</p>
                <p><strong>Notifikasi:</strong> Hasil seleksi akan dikirim melalui SMS dan email yang didaftarkan.</p>
                <p><strong>Pendaftaran Ulang:</strong> Siswa yang diterima harus melakukan pendaftaran ulang sesuai jadwal.</p>
                <p><strong>Kontak:</strong> Untuk informasi lebih lanjut, hubungi pihak sekolah.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2024 SPMB SDN Majasetra 01. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>

