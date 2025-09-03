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

// Get message from URL parameters (for delete operations)
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = $_GET['message'];
    $messageType = $_GET['type'];
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $projectRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        // Get file paths before deleting
        $stmt = $pdo->prepare("SELECT foto, dokumen FROM pendaftar WHERE id = ?");
        $stmt->execute([$id]);
        $files = $stmt->fetch();
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM pendaftar WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete files (use absolute path from project root)
        if (!empty($files['foto'])) {
            $fotoPath = $projectRoot . ltrim($files['foto'], '/\\');
            if (file_exists($fotoPath)) {
                @unlink($fotoPath);
            }
        }
        if (!empty($files['dokumen'])) {
            $dokumenPath = $projectRoot . ltrim($files['dokumen'], '/\\');
            if (file_exists($dokumenPath)) {
                @unlink($dokumenPath);
            }
        }
        
        $message = "Data pendaftar berhasil dihapus!";
        $messageType = 'success';
        
        // Redirect to remove delete parameter from URL
        header('Location: dashboard.php?message=' . urlencode($message) . '&type=' . $messageType);
        exit();
    } catch(PDOException $e) {
        $errorMessage = $e->getMessage();
        $fixed = false;
        
        // Auto-fix for FK error caused by AFTER DELETE trigger logging to log_pendaftar
        if ($e->getCode() === '23000' && strpos($errorMessage, 'log_pendaftar') !== false) {
            try {
                // Ensure correct trigger: BEFORE DELETE (so parent row still exists when logging)
                $pdo->exec("DROP TRIGGER IF EXISTS after_pendaftar_delete");
                $pdo->exec("DROP TRIGGER IF EXISTS before_pendaftar_delete");
                $pdo->exec("CREATE TRIGGER before_pendaftar_delete BEFORE DELETE ON pendaftar FOR EACH ROW INSERT INTO log_pendaftar (pendaftar_id, action, old_data, admin_username) VALUES (OLD.id, 'DELETE', JSON_OBJECT('nama_lengkap', OLD.nama_lengkap, 'nisn', OLD.nisn, 'jurusan', OLD.jurusan, 'tanggal_daftar', OLD.tanggal_daftar), 'system')");
                
                // Retry delete
                $stmt = $pdo->prepare("DELETE FROM pendaftar WHERE id = ?");
                $stmt->execute([$id]);
                
                // Delete files after successful retry (use absolute path)
                if (!empty($files['foto'])) {
                    $fotoPath = $projectRoot . ltrim($files['foto'], '/\\');
                    if (file_exists($fotoPath)) {
                        @unlink($fotoPath);
                    }
                }
                if (!empty($files['dokumen'])) {
                    $dokumenPath = $projectRoot . ltrim($files['dokumen'], '/\\');
                    if (file_exists($dokumenPath)) {
                        @unlink($dokumenPath);
                    }
                }
                
                $fixed = true;
            } catch (Exception $fixE) {
                // fall through to error handling below
            }
        }
        
        if ($fixed) {
            $message = "Data pendaftar berhasil dihapus!";
            $messageType = 'success';
        } else {
            $message = "Error: " . $errorMessage;
            $messageType = 'error';
        }
        
        // Redirect to remove delete parameter from URL
        header('Location: dashboard.php?message=' . urlencode($message) . '&type=' . $messageType);
        exit();
    }
}

// Handle search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$jurusan_filter = isset($_GET['jurusan']) ? $_GET['jurusan'] : '';

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(nama_lengkap LIKE ? OR nisn LIKE ? OR email LIKE ? OR asal_sekolah LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if ($jurusan_filter) {
    $where_conditions[] = "jurusan = ?";
    $params[] = $jurusan_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count
try {
    if (empty($where_clause)) {
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM pendaftar");
        $total_records = $count_stmt->fetchColumn();
    } else {
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM pendaftar $where_clause");
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
    }
} catch(PDOException $e) {
    $total_records = 0;
}

// Pagination
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;
$total_pages = ceil($total_records / $records_per_page);

// Get data
try {
    // Use LIMIT offset, count syntax for better MariaDB compatibility
    $stmt = $pdo->prepare("SELECT * FROM pendaftar $where_clause ORDER BY tanggal_daftar DESC LIMIT ?, ?");
    $params_for_query = $params;
    $params_for_query[] = $offset;
    $params_for_query[] = $records_per_page;
    $stmt->execute($params_for_query);
    $pendaftar = $stmt->fetchAll();
} catch(PDOException $e) {
    $pendaftar = [];
    $message = "Error loading data: " . $e->getMessage();
    $messageType = 'error';
}

// Get unique jurusan for filter
try {
    $jurusan_stmt = $pdo->query("SELECT DISTINCT jurusan FROM pendaftar WHERE jurusan IS NOT NULL AND jurusan != '' ORDER BY jurusan");
    $jurusan_list = $jurusan_stmt->fetchAll();
} catch(PDOException $e) {
    $jurusan_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SPMB SDN Majasetra 01</title>
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
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Data Pendaftar</h2>
                <p class="text-gray-600">Kelola data pendaftar SPMB SDN Majasetra 01</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="seleksi.php" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition">
                    <i class="fas fa-edit mr-2"></i>Seleksi Pendaftar
                </a>
                <a href="kirim_notifikasi.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-bell mr-2"></i>Notifikasi
                </a>
                <a href="export_excel.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
                <a href="export_pdf.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
        <?php endif; ?>

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
                    <label for="jurusan" class="block text-sm font-medium text-gray-700 mb-2">Filter Kelas</label>
                    <select id="jurusan" name="jurusan" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($jurusan_list as $jur): ?>
                        <option value="<?php echo htmlspecialchars($jur['jurusan']); ?>" <?php echo $jurusan_filter === $jur['jurusan'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($jur['jurusan']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pendaftar</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $total_records; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-calendar-day text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Hari Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php 
                            $today = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE DATE(tanggal_daftar) = CURDATE()")->fetchColumn();
                            echo $today;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-calendar-week text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Minggu Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php 
                            $week = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE YEARWEEK(tanggal_daftar) = YEARWEEK(CURDATE())")->fetchColumn();
                            echo $week;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Bulan Ini</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php 
                            $month = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE MONTH(tanggal_daftar) = MONTH(CURDATE()) AND YEAR(tanggal_daftar) = YEAR(CURDATE())")->fetchColumn();
                            echo $month;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Daftar Pendaftar</h3>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Seleksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pendaftar as $index => $data): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $offset + $index + 1; ?>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('d/m/Y H:i', strtotime($data['tanggal_daftar'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewPendaftar(<?php echo $data['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editPendaftar(<?php echo $data['id']; ?>)" 
                                            class="text-green-600 hover:text-green-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deletePendaftar(<?php echo $data['id']; ?>)" 
                                            class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="flex items-center space-x-2">
                <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                   class="px-3 py-2 text-sm font-medium <?php echo $i == $page ? 'text-blue-600 bg-blue-50 border border-blue-300' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-md">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 5000);

        // Function to delete pendaftar with confirmation
        function deletePendaftar(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data pendaftar ini? Tindakan ini tidak dapat dibatalkan.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'GET';
                form.action = 'dashboard.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete';
                input.value = id;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Function to view pendaftar details
        function viewPendaftar(id) {
            window.location.href = 'detail.php?id=' + id;
        }

        // Function to edit pendaftar
        function editPendaftar(id) {
            window.location.href = 'edit.php?id=' + id;
        }
    </script>
</body>
</html>
