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

$message = '';
$messageType = '';

// Handle seleksi submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pendaftar_id = $_POST['pendaftar_id'];
    $nilai_tk = $_POST['nilai_tk'];
    $status_seleksi = $_POST['status_seleksi'];
    $catatan_seleksi = trim($_POST['catatan_seleksi']);
    $admin_username = $_SESSION['admin_username'];
    
    try {
        // Update pendaftar dengan nilai TK dan status seleksi
        $stmt = $pdo->prepare("UPDATE pendaftar SET nilai_tk = ?, status_seleksi = ?, catatan_seleksi = ?, admin_seleksi = ?, tanggal_seleksi = NOW() WHERE id = ?");
        $stmt->execute([$nilai_tk, $status_seleksi, $catatan_seleksi, $admin_username, $pendaftar_id]);
        
        // Get pendaftar data for notification
        $stmt = $pdo->prepare("SELECT nama_lengkap, no_hp, email, status_seleksi FROM pendaftar WHERE id = ?");
        $stmt->execute([$pendaftar_id]);
        $pendaftar_data = $stmt->fetch();
        
        // Create notification records
        $pesan = "Halo {$pendaftar_data['nama_lengkap']}, hasil seleksi PPDB Anda: {$pendaftar_data['status_seleksi']}. ";
        if ($status_seleksi == 'Diterima') {
            $pesan .= "Selamat! Anda diterima di SDN Majasetra 01. Silakan lakukan pendaftaran ulang sesuai jadwal yang ditentukan.";
        } else if ($status_seleksi == 'Ditolak') {
            $pesan .= "Mohon maaf, Anda belum dapat diterima pada tahun ini. Tetap semangat!";
        }
        
        // Insert SMS notification
        $stmt = $pdo->prepare("INSERT INTO notifikasi_seleksi (pendaftar_id, jenis_notifikasi, pesan, tanggal_kirim) VALUES (?, 'SMS', ?, NOW())");
        $stmt->execute([$pendaftar_id, $pesan]);
        
        // Insert Email notification
        $stmt = $pdo->prepare("INSERT INTO notifikasi_seleksi (pendaftar_id, jenis_notifikasi, pesan, tanggal_kirim) VALUES (?, 'Email', ?, NOW())");
        $stmt->execute([$pendaftar_id, $pesan]);
        
        $message = "Seleksi berhasil disimpan! Notifikasi akan dikirim ke pendaftar.";
        $messageType = 'success';
        
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get pendaftar data for seleksi
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(nama_lengkap LIKE ? OR nisn LIKE ? OR email LIKE ? OR asal_sekolah LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($status_filter) {
    $where_conditions[] = "status_seleksi = ?";
    $params[] = $status_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get data
try {
    $stmt = $pdo->prepare("SELECT * FROM pendaftar $where_clause ORDER BY tanggal_daftar DESC");
    $stmt->execute($params);
    $pendaftar = $stmt->fetchAll();
} catch(PDOException $e) {
    $pendaftar = [];
    $message = "Error loading data: " . $e->getMessage();
    $messageType = 'error';
}

// Get statistics
try {
    $stats = $pdo->query("SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN status_seleksi = 'Menunggu' THEN 1 END) as menunggu,
        COUNT(CASE WHEN status_seleksi = 'Diterima' THEN 1 END) as diterima,
        COUNT(CASE WHEN status_seleksi = 'Ditolak' THEN 1 END) as ditolak
        FROM pendaftar")->fetch();
} catch(PDOException $e) {
    $stats = ['total' => 0, 'menunggu' => 0, 'diterima' => 0, 'ditolak' => 0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleksi Pendaftar - Admin SPMB</title>
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
                    <a href="dashboard.php" class="bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition">
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Seleksi Pendaftar</h2>
            <p class="text-gray-600">Kelola seleksi dan input nilai TK untuk pendaftar</p>
        </div>

        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pendaftar</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Menunggu Seleksi</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['menunggu']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Diterima</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['diterima']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-times-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Ditolak</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['ditolak']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Cari berdasarkan nama, NISN, email, atau asal sekolah">
                </div>
                <div class="md:w-48">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Filter Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="Menunggu" <?php echo $status_filter === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Diterima" <?php echo $status_filter === 'Diterima' ? 'selected' : ''; ?>>Diterima</option>
                        <option value="Ditolak" <?php echo $status_filter === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Daftar Pendaftar untuk Seleksi</h3>
            </div>
            
            <?php if (empty($pendaftar)): ?>
            <div class="px-6 py-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Tidak ada data pendaftar ditemukan</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NISN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asal Sekolah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pendaftar as $index => $data): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $index + 1; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($data['foto'] && file_exists($data['foto'])): ?>
                                    <img class="h-10 w-10 rounded-full object-cover mr-3" src="../<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto">
                                    <?php else: ?>
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($data['nama_lengkap']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($data['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($data['nisn']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $kelas = $data['jurusan'] ?? '';
                                $kelas_class = '';
                                if (!empty($kelas)) {
                                    switch($kelas) {
                                        case 'Kelas 1': $kelas_class = 'bg-blue-100 text-blue-800'; break;
                                        case 'Kelas 2': $kelas_class = 'bg-green-100 text-green-800'; break;
                                        case 'Kelas 3': $kelas_class = 'bg-purple-100 text-purple-800'; break;
                                        case 'Kelas 4': $kelas_class = 'bg-red-100 text-red-800'; break;
                                        case 'Kelas 5': $kelas_class = 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Kelas 6': $kelas_class = 'bg-indigo-100 text-indigo-800'; break;
                                        default: $kelas_class = 'bg-gray-100 text-gray-800';
                                    }
                                } else {
                                    $kelas_class = 'bg-gray-100 text-gray-800';
                                    $kelas = 'Belum Dipilih';
                                }
                                ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $kelas_class; ?>">
                                    <?php echo htmlspecialchars($kelas); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($data['asal_sekolah']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $status = $data['status_seleksi'] ?? 'Menunggu';
                                $status_class = '';
                                switch($status) {
                                    case 'Diterima': $status_class = 'bg-green-100 text-green-800'; break;
                                    case 'Ditolak': $status_class = 'bg-red-100 text-red-800'; break;
                                    case 'Menunggu': $status_class = 'bg-yellow-100 text-yellow-800'; break;
                                    default: 
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        $status = 'Menunggu';
                                        break;
                                }
                                ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if (isset($data['nilai_akhir']) && $data['nilai_akhir'] !== null): ?>
                                    <span class="font-semibold <?php echo $data['nilai_akhir'] >= 70 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $data['nilai_akhir']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="openSeleksiModal(<?php echo $data['id']; ?>, '<?php echo htmlspecialchars($data['nama_lengkap']); ?>', '<?php echo $data['nilai_tk'] ?? ''; ?>', '<?php echo $data['status_seleksi'] ?? 'Menunggu'; ?>', '<?php echo htmlspecialchars($data['catatan_seleksi'] ?? ''); ?>')" 
                                        class="text-blue-600 hover:text-blue-900" title="Seleksi">
                                    <i class="fas fa-edit"></i> Seleksi
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Seleksi Modal -->
    <div id="seleksiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Seleksi Pendaftar</h3>
                <form id="seleksiForm" method="POST">
                    <input type="hidden" id="pendaftar_id" name="pendaftar_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pendaftar</label>
                        <input type="text" id="nama_pendaftar" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_tk" class="block text-sm font-medium text-gray-700 mb-2">Nilai TK (0-100)</label>
                        <input type="number" id="nilai_tk" name="nilai_tk" min="0" max="100" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Masukkan nilai TK">
                        <p class="text-xs text-gray-500 mt-1">Nilai ≥70 akan otomatis diterima</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="status_seleksi" class="block text-sm font-medium text-gray-700 mb-2">Status Seleksi</label>
                        <select id="status_seleksi" name="status_seleksi" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Menunggu">Menunggu</option>
                            <option value="Diterima">Diterima</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="catatan_seleksi" class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea id="catatan_seleksi" name="catatan_seleksi" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeSeleksiModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Simpan Seleksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openSeleksiModal(id, nama, nilai, status, catatan) {
            document.getElementById('pendaftar_id').value = id;
            document.getElementById('nama_pendaftar').value = nama;
            document.getElementById('nilai_tk').value = nilai;
            document.getElementById('status_seleksi').value = status;
            document.getElementById('catatan_seleksi').value = catatan;
            document.getElementById('seleksiModal').classList.remove('hidden');
        }

        function closeSeleksiModal() {
            document.getElementById('seleksiModal').classList.add('hidden');
        }

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 5000);

        // Close modal when clicking outside
        document.getElementById('seleksiModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSeleksiModal();
            }
        });
    </script>
</body>
</html>

