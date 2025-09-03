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

// Handle manual notification sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pendaftar_id = $_POST['pendaftar_id'];
    $jenis_notifikasi = $_POST['jenis_notifikasi'];
    
    try {
        // Get pendaftar data
        $stmt = $pdo->prepare("SELECT nama_lengkap, no_hp, email, status_seleksi, nilai_tk FROM pendaftar WHERE id = ?");
        $stmt->execute([$pendaftar_id]);
        $pendaftar_data = $stmt->fetch();
        
        if ($pendaftar_data) {
            // Create message
            $pesan = "Halo {$pendaftar_data['nama_lengkap']}, hasil seleksi PPDB Anda: {$pendaftar_data['status_seleksi']}. ";
            if ($pendaftar_data['status_seleksi'] == 'Diterima') {
                $pesan .= "Selamat! Anda diterima di SDN Majasetra 01. ";
                if ($pendaftar_data['nilai_tk'] >= 70) {
                    $pesan .= "Anda diterima berdasarkan nilai TK yang memenuhi syarat (≥70). ";
                }
                $pesan .= "Silakan lakukan pendaftaran ulang sesuai jadwal yang ditentukan.";
            } else if ($pendaftar_data['status_seleksi'] == 'Ditolak') {
                $pesan .= "Mohon maaf, Anda belum dapat diterima pada tahun ini. Tetap semangat!";
            }
            
            // Send notification based on type
            if ($jenis_notifikasi == 'SMS') {
                $success = sendSMS($pendaftar_data['no_hp'], $pesan);
            } else {
                $success = sendEmail($pendaftar_data['email'], $pendaftar_data['nama_lengkap'], $pesan);
            }
            
            // Update notification status
            $status = $success ? 'Terkirim' : 'Gagal';
            $error_msg = $success ? null : 'Gagal mengirim notifikasi';
            
            $stmt = $pdo->prepare("UPDATE notifikasi_seleksi SET status_pengiriman = ?, tanggal_terkirim = ?, error_message = ? WHERE pendaftar_id = ? AND jenis_notifikasi = ? AND status_pengiriman = 'Pending'");
            $stmt->execute([$status, $success ? date('Y-m-d H:i:s') : null, $error_msg, $pendaftar_id, $jenis_notifikasi]);
            
            if ($success) {
                $message = "Notifikasi {$jenis_notifikasi} berhasil dikirim ke {$pendaftar_data['nama_lengkap']}";
                $messageType = 'success';
            } else {
                $message = "Gagal mengirim notifikasi {$jenis_notifikasi} ke {$pendaftar_data['nama_lengkap']}";
                $messageType = 'error';
            }
        }
        
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get pending notifications
try {
    $stmt = $pdo->query("SELECT n.*, p.nama_lengkap, p.no_hp, p.email, p.status_seleksi 
                         FROM notifikasi_seleksi n 
                         JOIN pendaftar p ON n.pendaftar_id = p.id 
                         ORDER BY n.tanggal_kirim DESC");
    $notifications = $stmt->fetchAll();
} catch(PDOException $e) {
    $notifications = [];
    $message = "Error loading notifications: " . $e->getMessage();
    $messageType = 'error';
}

// Function to send SMS (using Twilio or similar service)
function sendSMS($phone, $message) {
    // In production, integrate with SMS gateway like Twilio, Nexmo, etc.
    // For demo purposes, we'll simulate success
    // You need to add your SMS API credentials here
    
    /*
    // Example with Twilio
    require_once 'vendor/autoload.php';
    $account_sid = 'YOUR_ACCOUNT_SID';
    $auth_token = 'YOUR_AUTH_TOKEN';
    $twilio_number = 'YOUR_TWILIO_NUMBER';
    
    $client = new Twilio\Rest\Client($account_sid, $auth_token);
    
    try {
        $client->messages->create(
            $phone,
            [
                'from' => $twilio_number,
                'body' => $message
            ]
        );
        return true;
    } catch (Exception $e) {
        error_log("SMS Error: " . $e->getMessage());
        return false;
    }
    */
    
    // Simulate SMS sending for demo
    error_log("SMS would be sent to {$phone}: {$message}");
    return true;
}

// Function to send Email
function sendEmail($email, $nama, $message) {
    // In production, use proper email library like PHPMailer
    // For demo purposes, we'll simulate success
    
    /*
    // Example with PHPMailer
    require_once 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com';
        $mail->Password = 'your-app-password';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('noreply@school.com', 'SDN Majasetra 01');
        $mail->addAddress($email, $nama);
        $mail->Subject = 'Hasil Seleksi PPDB SDN Majasetra 01';
        $mail->Body = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
    */
    
    // Simulate email sending for demo
    error_log("Email would be sent to {$email} ({$nama}): {$message}");
    return true;
}

// Auto-send pending notifications (can be called via cron job)
function sendPendingNotifications() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT n.*, p.nama_lengkap, p.no_hp, p.email, p.status_seleksi 
                             FROM notifikasi_seleksi n 
                             JOIN pendaftar p ON n.pendaftar_id = p.id 
                             WHERE n.status_pengiriman = 'Pending'");
        $pending = $stmt->fetchAll();
        
        foreach ($pending as $notification) {
            $success = false;
            
            if ($notification['jenis_notifikasi'] == 'SMS') {
                $success = sendSMS($notification['no_hp'], $notification['pesan']);
            } else {
                $success = sendEmail($notification['email'], $notification['nama_lengkap'], $notification['pesan']);
            }
            
            $status = $success ? 'Terkirim' : 'Gagal';
            $error_msg = $success ? null : 'Gagal mengirim notifikasi';
            
            $update_stmt = $pdo->prepare("UPDATE notifikasi_seleksi SET status_pengiriman = ?, tanggal_terkirim = ?, error_message = ? WHERE id = ?");
            $update_stmt->execute([$status, $success ? date('Y-m-d H:i:s') : null, $error_msg, $notification['id']]);
        }
        
        return count($pending);
    } catch (Exception $e) {
        error_log("Auto-send error: " . $e->getMessage());
        return 0;
    }
}

// Handle auto-send request
if (isset($_GET['auto_send'])) {
    $count = sendPendingNotifications();
    $message = "Berhasil mengirim {$count} notifikasi otomatis";
    $messageType = 'success';
}

// Handle create all notifications request
if (isset($_GET['create_all_notifications'])) {
    try {
        // Get all selected participants (not 'Menunggu')
        $stmt = $pdo->query("SELECT id, nama_lengkap, no_hp, email, status_seleksi, nilai_tk FROM pendaftar WHERE status_seleksi != 'Menunggu'");
        $selected_participants = $stmt->fetchAll();
        
        $created_count = 0;
        
        foreach ($selected_participants as $participant) {
            // Check if notification already exists
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifikasi_seleksi WHERE pendaftar_id = ?");
            $check_stmt->execute([$participant['id']]);
            $exists = $check_stmt->fetchColumn() > 0;
            
            if (!$exists) {
                // Create message
                $pesan = "Halo {$participant['nama_lengkap']}, hasil seleksi PPDB Anda: {$participant['status_seleksi']}. ";
                if ($participant['status_seleksi'] == 'Diterima') {
                    $pesan .= "Selamat! Anda diterima di SDN Majasetra 01. ";
                    if ($participant['nilai_tk'] >= 70) {
                        $pesan .= "Anda diterima berdasarkan nilai TK yang memenuhi syarat (≥70). ";
                    }
                    $pesan .= "Silakan lakukan pendaftaran ulang sesuai jadwal yang ditentukan.";
                } else if ($participant['status_seleksi'] == 'Ditolak') {
                    $pesan .= "Mohon maaf, Anda belum dapat diterima pada tahun ini. Tetap semangat!";
                }
                
                // Create SMS notification
                $stmt = $pdo->prepare("INSERT INTO notifikasi_seleksi (pendaftar_id, jenis_notifikasi, pesan, tanggal_kirim) VALUES (?, 'SMS', ?, NOW())");
                $stmt->execute([$participant['id'], $pesan]);
                
                // Create Email notification
                $stmt = $pdo->prepare("INSERT INTO notifikasi_seleksi (pendaftar_id, jenis_notifikasi, pesan, tanggal_kirim) VALUES (?, 'Email', ?, NOW())");
                $stmt->execute([$participant['id'], $pesan]);
                
                $created_count += 2; // SMS + Email
            }
        }
        
        $message = "Berhasil membuat {$created_count} notifikasi untuk " . count($selected_participants) . " peserta yang telah diseleksi";
        $messageType = 'success';
        
        // Redirect to remove parameter from URL
        header('Location: kirim_notifikasi.php?message=' . urlencode($message) . '&type=' . $messageType);
        exit();
        
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
    <title>Kirim Notifikasi - Admin SPMB</title>
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
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Kirim Notifikasi</h2>
                <p class="text-gray-600">Kelola pengiriman notifikasi hasil seleksi ke pendaftar</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button onclick="createAllNotifications()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-bell mr-2"></i>Buat Notifikasi Semua
                </button>
                <a href="?auto_send=1" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Semua Otomatis
                </a>
                <a href="seleksi.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-edit mr-2"></i>Kembali ke Seleksi
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

        <!-- Statistics -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <?php
            $stats = [
                'total' => count($notifications),
                'pending' => count(array_filter($notifications, fn($n) => $n['status_pengiriman'] == 'Pending')),
                'sent' => count(array_filter($notifications, fn($n) => $n['status_pengiriman'] == 'Terkirim')),
                'failed' => count(array_filter($notifications, fn($n) => $n['status_pengiriman'] == 'Gagal'))
            ];
            ?>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-bell text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Notifikasi</p>
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
                        <p class="text-sm font-medium text-gray-600">Menunggu</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['pending']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Terkirim</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['sent']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-times-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Gagal</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['failed']; ?></p>
                    </div>
                </div>
            </div>
        </div>

                <!-- Peserta yang Telah Diseleksi -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Peserta yang Telah Diseleksi</h3>
                <p class="text-sm text-gray-600 mt-1">Daftar peserta yang sudah diseleksi dan siap dikirim notifikasi</p>
            </div>
            
            <?php
            // Get peserta yang sudah diseleksi (tidak lagi 'Menunggu')
            try {
                $stmt = $pdo->query("SELECT p.*, 
                                    CASE 
                                        WHEN p.status_seleksi = 'Diterima' AND p.nilai_tk >= 70 THEN 'Otomatis Diterima'
                                        WHEN p.status_seleksi = 'Diterima' THEN 'Diterima Manual'
                                        WHEN p.status_seleksi = 'Ditolak' THEN 'Ditolak Manual'
                                        ELSE p.status_seleksi
                                    END as keterangan_seleksi
                                    FROM pendaftar p 
                                    WHERE p.status_seleksi != 'Menunggu' 
                                    ORDER BY p.tanggal_seleksi DESC, p.tanggal_daftar DESC");
                $peserta_seleksi = $stmt->fetchAll();
            } catch(PDOException $e) {
                $peserta_seleksi = [];
            }
            ?>
            
            <?php if (empty($peserta_seleksi)): ?>
            <div class="px-6 py-12 text-center">
                <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Belum ada peserta yang diseleksi</p>
                <p class="text-sm text-gray-400 mt-2">Silakan lakukan seleksi di halaman Seleksi Pendaftar</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendaftar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Seleksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Seleksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($peserta_seleksi as $index => $peserta): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $index + 1; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($peserta['foto'] && file_exists('../' . $peserta['foto'])): ?>
                                    <img class="h-10 w-10 rounded-full object-cover mr-3" src="../<?php echo htmlspecialchars($peserta['foto']); ?>" alt="Foto">
                                    <?php else: ?>
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($peserta['nama_lengkap']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($peserta['nisn']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $kelas = $peserta['jurusan'] ?? '';
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
                                <?php if (isset($peserta['nilai_akhir']) && $peserta['nilai_akhir'] !== null): ?>
                                    <span class="font-semibold <?php echo $peserta['nilai_akhir'] >= 70 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $peserta['nilai_akhir']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php 
                                        switch($peserta['status_seleksi']) {
                                            case 'Diterima': echo 'bg-green-100 text-green-800'; break;
                                            case 'Ditolak': echo 'bg-red-100 text-red-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($peserta['status_seleksi']); ?>
                                    </span>
                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($peserta['keterangan_seleksi']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($peserta['tanggal_seleksi']): ?>
                                    <?php echo date('d/m/Y H:i', strtotime($peserta['tanggal_seleksi'])); ?>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($peserta['admin_seleksi'] && $peserta['admin_seleksi'] != 'system'): ?>
                                    <span class="text-blue-600"><?php echo htmlspecialchars($peserta['admin_seleksi']); ?></span>
                                <?php else: ?>
                                    <span class="text-gray-500">System</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="detail.php?id=<?php echo $peserta['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="seleksi.php?search=<?php echo urlencode($peserta['nama_lengkap']); ?>" 
                                       class="text-green-600 hover:text-green-900" title="Edit Seleksi">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="createNotification(<?php echo $peserta['id']; ?>, '<?php echo htmlspecialchars($peserta['nama_lengkap']); ?>')" 
                                            class="text-purple-600 hover:text-purple-900" title="Buat Notifikasi">
                                        <i class="fas fa-bell"></i>
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

        <!-- Notifications Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Daftar Notifikasi</h3>
                <p class="text-sm text-gray-600 mt-1">Riwayat pengiriman notifikasi yang telah dilakukan</p>
            </div>
            
            <?php if (empty($notifications)): ?>
            <div class="px-6 py-12 text-center">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Tidak ada notifikasi ditemukan</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendaftar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Kirim</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($notifications as $index => $notification): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $index + 1; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($notification['nama_lengkap']); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo $notification['jenis_notifikasi'] == 'SMS' ? $notification['no_hp'] : $notification['email']; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $notification['jenis_notifikasi'] == 'SMS' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo $notification['jenis_notifikasi']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php 
                                    switch($notification['status_pengiriman']) {
                                        case 'Pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Terkirim': echo 'bg-green-100 text-green-800'; break;
                                        case 'Gagal': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo $notification['status_pengiriman']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('d/m/Y H:i', strtotime($notification['tanggal_kirim'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($notification['status_pengiriman'] == 'Pending'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="pendaftar_id" value="<?php echo $notification['pendaftar_id']; ?>">
                                    <input type="hidden" name="jenis_notifikasi" value="<?php echo $notification['jenis_notifikasi']; ?>">
                                    <button type="submit" class="text-blue-600 hover:text-blue-900" title="Kirim Sekarang">
                                        <i class="fas fa-paper-plane"></i> Kirim
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Setup Instructions -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-blue-900 mb-3">
                <i class="fas fa-info-circle mr-2"></i>Setup Notifikasi
            </h3>
            <div class="text-blue-800 text-sm space-y-2">
                <p><strong>SMS Gateway:</strong> Untuk mengirim SMS otomatis, Anda perlu mengintegrasikan dengan layanan SMS seperti Twilio, Nexmo, atau SMS Gateway lokal.</p>
                <p><strong>Email SMTP:</strong> Untuk mengirim email otomatis, gunakan library PHPMailer dengan konfigurasi SMTP server.</p>
                <p><strong>Cron Job:</strong> Untuk pengiriman otomatis, buat cron job yang memanggil <code>sendPendingNotifications()</code> secara berkala.</p>
                <p><strong>File:</strong> Edit file ini dan uncomment kode untuk SMS/Email yang sesuai dengan layanan yang Anda gunakan.</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 5000);

        // Function to create notification for selected participant
        function createNotification(pendaftarId, namaPendaftar) {
            if (confirm(`Buat notifikasi untuk ${namaPendaftar}?\n\nIni akan membuat notifikasi SMS dan Email yang siap dikirim.`)) {
                // Create a form to submit the notification creation request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'kirim_notifikasi.php';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'pendaftar_id';
                inputId.value = pendaftarId;
                
                const inputType = document.createElement('input');
                inputType.type = 'hidden';
                inputType.name = 'jenis_notifikasi';
                inputType.value = 'SMS';
                
                form.appendChild(inputId);
                form.appendChild(inputType);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Function to create notifications for all selected participants
        function createAllNotifications() {
            if (confirm('Buat notifikasi untuk semua peserta yang telah diseleksi?\n\nIni akan membuat notifikasi SMS dan Email untuk semua peserta.')) {
                window.location.href = '?create_all_notifications=1';
            }
        }
    </script>
</body>
</html>

