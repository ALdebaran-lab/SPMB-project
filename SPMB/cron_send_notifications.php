<?php
/**
 * Cron Job Script untuk Pengiriman Notifikasi Otomatis
 * 
 * Cara penggunaan:
 * 1. Buat cron job yang menjalankan script ini setiap beberapa menit
 * 2. Contoh cron job: */5 * * * * php /path/to/your/project/cron_send_notifications.php
 * 3. Script ini akan mengirim semua notifikasi yang masih pending
 * 
 * Pastikan file ini dapat diakses oleh web server dan memiliki permission yang tepat
 */

// Set time limit untuk mencegah timeout
set_time_limit(300); // 5 menit

// Database connection
$host = 'localhost';
$dbname = 'ppdb_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Cron Job Error - Database Connection: " . $e->getMessage());
    exit(1);
}

// Function to send SMS (using Twilio or similar service)
function sendSMS($phone, $message) {
    // In production, integrate with SMS gateway like Twilio, Nexmo, etc.
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
    error_log("Cron Job - SMS would be sent to {$phone}: {$message}");
    return true;
}

// Function to send Email
function sendEmail($email, $nama, $message) {
    // In production, use proper email library like PHPMailer
    // You need to add your email configuration here
    
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
    error_log("Cron Job - Email would be sent to {$email} ({$nama}): {$message}");
    return true;
}

// Auto-send pending notifications
function sendPendingNotifications() {
    global $pdo;
    
    try {
        // Get all pending notifications
        $stmt = $pdo->query("SELECT n.*, p.nama_lengkap, p.no_hp, p.email, p.status_seleksi 
                             FROM notifikasi_seleksi n 
                             JOIN pendaftar p ON n.pendaftar_id = p.id 
                             WHERE n.status_pengiriman = 'Pending'
                             ORDER BY n.tanggal_kirim ASC");
        $pending = $stmt->fetchAll();
        
        $success_count = 0;
        $failed_count = 0;
        
        foreach ($pending as $notification) {
            $success = false;
            
            try {
                if ($notification['jenis_notifikasi'] == 'SMS') {
                    $success = sendSMS($notification['no_hp'], $notification['pesan']);
                } else {
                    $success = sendEmail($notification['email'], $notification['nama_lengkap'], $notification['pesan']);
                }
                
                $status = $success ? 'Terkirim' : 'Gagal';
                $error_msg = $success ? null : 'Gagal mengirim notifikasi';
                $tanggal_terkirim = $success ? date('Y-m-d H:i:s') : null;
                
                // Update notification status
                $update_stmt = $pdo->prepare("UPDATE notifikasi_seleksi SET status_pengiriman = ?, tanggal_terkirim = ?, error_message = ? WHERE id = ?");
                $update_stmt->execute([$status, $tanggal_terkirim, $error_msg, $notification['id']]);
                
                if ($success) {
                    $success_count++;
                    error_log("Cron Job - Successfully sent {$notification['jenis_notifikasi']} to {$notification['nama_lengkap']}");
                } else {
                    $failed_count++;
                    error_log("Cron Job - Failed to send {$notification['jenis_notifikasi']} to {$notification['nama_lengkap']}");
                }
                
                // Add small delay to prevent overwhelming external services
                usleep(100000); // 0.1 second delay
                
            } catch (Exception $e) {
                $failed_count++;
                error_log("Cron Job - Error processing notification ID {$notification['id']}: " . $e->getMessage());
                
                // Mark as failed
                $update_stmt = $pdo->prepare("UPDATE notifikasi_seleksi SET status_pengiriman = 'Gagal', error_message = ? WHERE id = ?");
                $update_stmt->execute([$e->getMessage(), $notification['id']]);
            }
        }
        
        return [
            'total' => count($pending),
            'success' => $success_count,
            'failed' => $failed_count
        ];
        
    } catch (Exception $e) {
        error_log("Cron Job - Auto-send error: " . $e->getMessage());
        return ['total' => 0, 'success' => 0, 'failed' => 0];
    }
}

// Main execution
try {
    $start_time = microtime(true);
    
    error_log("Cron Job - Starting notification sending process at " . date('Y-m-d H:i:s'));
    
    // Send pending notifications
    $result = sendPendingNotifications();
    
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    // Log results
    error_log("Cron Job - Completed in {$execution_time}s. Total: {$result['total']}, Success: {$result['success']}, Failed: {$result['failed']}");
    
    // Output results for cron job logging
    echo "Notification sending completed at " . date('Y-m-d H:i:s') . "\n";
    echo "Execution time: {$execution_time}s\n";
    echo "Total notifications: {$result['total']}\n";
    echo "Successfully sent: {$result['success']}\n";
    echo "Failed: {$result['failed']}\n";
    
    // Exit with appropriate code
    if ($result['failed'] > 0) {
        exit(1); // Exit with error code if there were failures
    } else {
        exit(0); // Exit successfully
    }
    
} catch (Exception $e) {
    error_log("Cron Job - Critical error: " . $e->getMessage());
    echo "Critical error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

