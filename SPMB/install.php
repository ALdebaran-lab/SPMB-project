<?php
/**
 * PPDB Installer
 * SMK Negeri 1 Jakarta
 * 
 * File ini digunakan untuk setup awal sistem PPDB
 * Jalankan file ini sekali saja untuk setup database dan direktori
 */

session_start();

// Check if already installed
if (file_exists('config/installed.txt')) {
    die('Sistem PPDB sudah terinstall. Hapus file config/installed.txt jika ingin reinstall.');
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Database configuration
$db_config = [
    'host' => 'localhost',
    'name' => 'ppdb_db',
    'user' => 'root',
    'password' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == 1) {
        // Step 1: Database configuration
        $db_config['host'] = $_POST['db_host'];
        $db_config['name'] = $_POST['db_name'];
        $db_config['user'] = $_POST['db_user'];
        $db_config['password'] = $_POST['db_password'];
        
        // Test database connection
        try {
            $pdo = new PDO("mysql:host={$db_config['host']}", $db_config['user'], $db_config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $step = 2;
        } catch(PDOException $e) {
            $error = "Database connection failed: " . $e->getMessage();
        }
    } elseif ($step == 2) {
        // Step 2: Create tables
        try {
            $pdo = new PDO("mysql:host={$db_config['host']};dbname={$db_config['name']}", $db_config['user'], $db_config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Read and execute SQL file
            $sql_file = 'database.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                
                // Remove CREATE DATABASE and USE statements
                $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
                $sql = preg_replace('/USE.*?;/i', '', $sql);
                
                // Split and execute statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
                        $pdo->exec($statement);
                    }
                }
            }
            
            $step = 3;
        } catch(Exception $e) {
            $error = "Error creating tables: " . $e->getMessage();
        }
    } elseif ($step == 3) {
        // Step 3: Create directories and config files
        try {
            // Create uploads directory
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            // Create config directory
            if (!is_dir('config')) {
                mkdir('config', 0755, true);
            }
            
            // Create database config file
            $config_content = "<?php\n";
            $config_content .= "// Database configuration\n";
            $config_content .= "define('DB_HOST', '{$db_config['host']}');\n";
            $config_content .= "define('DB_NAME', '{$db_config['name']}');\n";
            $config_content .= "define('DB_USER', '{$db_config['user']}');\n";
            $config_content .= "define('DB_PASS', '{$db_config['password']}');\n";
            $config_content .= "\n";
            $config_content .= "// Database connection function\n";
            $config_content .= "function getDBConnection() {\n";
            $config_content .= "    try {\n";
            $config_content .= "        \$pdo = new PDO(\n";
            $config_content .= "            \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\",\n";
            $config_content .= "            DB_USER,\n";
            $config_content .= "            DB_PASS,\n";
            $config_content .= "            [\n";
            $config_content .= "                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
            $config_content .= "                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
            $config_content .= "                PDO::ATTR_EMULATE_PREPARES => false,\n";
            $config_content .= "            ]\n";
            $config_content .= "        );\n";
            $config_content .= "        return \$pdo;\n";
            $config_content .= "    } catch(PDOException \$e) {\n";
            $config_content .= "        die(\"Database connection failed: \" . \$e->getMessage());\n";
            $config_content .= "    }\n";
            $config_content .= "}\n";
            $config_content .= "?>";
            
            file_put_contents('config/database.php', $config_content);
            
            // Create installed marker
            file_put_contents('config/installed.txt', date('Y-m-d H:i:s'));
            
            $success = "Installation completed successfully!";
            $step = 4;
            
        } catch(Exception $e) {
            $error = "Error creating files: " . $e->getMessage();
        }
    }
}

// Update database config for current step
if ($step >= 2) {
    try {
        $pdo = new PDO("mysql:host={$db_config['host']};dbname={$db_config['name']}", $db_config['user'], $db_config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(Exception $e) {
        // Ignore connection errors in later steps
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB Installer - SMK Negeri 1 Jakarta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-graduation-cap text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">PPDB Installer</h1>
            <p class="text-lg text-gray-600">Sistem Penerimaan Murid Baru - SMK Negeri 1 Jakarta</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Step <?php echo $step; ?> of 4</span>
                <span class="text-sm font-medium text-gray-700"><?php echo round(($step / 4) * 100); ?>% Complete</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: <?php echo ($step / 4) * 100; ?>%"></div>
            </div>
        </div>

        <!-- Error/Success Messages -->
        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Step Content -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <?php if ($step == 1): ?>
            <!-- Step 1: Database Configuration -->
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-database mr-2 text-blue-600"></i>Database Configuration
            </h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="db_host" class="block text-sm font-medium text-gray-700 mb-2">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($db_config['host']); ?>" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="db_name" class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($db_config['name']); ?>" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="db_user" class="block text-sm font-medium text-gray-700 mb-2">Database Username</label>
                        <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($db_config['user']); ?>" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="db_password" class="block text-sm font-medium text-gray-700 mb-2">Database Password</label>
                        <input type="password" id="db_password" name="db_password" value="<?php echo htmlspecialchars($db_config['password']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-arrow-right mr-2"></i>Next Step
                    </button>
                </div>
            </form>
            
            <?php elseif ($step == 2): ?>
            <!-- Step 2: Creating Tables -->
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-table mr-2 text-green-600"></i>Creating Database Tables
            </h2>
            
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-lg text-gray-600">Creating database tables...</p>
                <p class="text-sm text-gray-500 mt-2">Please wait while we set up your database structure.</p>
            </div>
            
            <form method="POST" class="text-center">
                <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-700 transition">
                    <i class="fas fa-arrow-right mr-2"></i>Continue
                </button>
            </form>
            
            <?php elseif ($step == 3): ?>
            <!-- Step 3: Creating Files and Directories -->
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                <i class="fas fa-folder-plus mr-2 text-purple-600"></i>Creating Files and Directories
            </h2>
            
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-600 mx-auto mb-4"></div>
                <p class="text-lg text-gray-600">Setting up file structure...</p>
                <p class="text-sm text-gray-500 mt-2">Creating necessary directories and configuration files.</p>
            </div>
            
            <form method="POST" class="text-center">
                <button type="submit" class="bg-purple-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-purple-700 transition">
                    <i class="fas fa-arrow-right mr-2"></i>Continue
                </button>
            </form>
            
            <?php elseif ($step == 4): ?>
            <!-- Step 4: Installation Complete -->
            <div class="text-center py-8">
                <div class="mx-auto w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-check text-white text-3xl"></i>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Installation Complete!</h2>
                <p class="text-lg text-gray-600 mb-8">Sistem PPDB berhasil diinstall dan siap digunakan.</p>
                
                <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Next Steps:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-600">
                        <li>Hapus file <code class="bg-gray-200 px-2 py-1 rounded">install.php</code> untuk keamanan</li>
                        <li>Akses website utama: <a href="index.php" class="text-blue-600 hover:underline">index.php</a></li>
                        <li>Login admin: <a href="admin/login.php" class="text-blue-600 hover:underline">admin/login.php</a></li>
                        <li>Default credentials: <strong>admin</strong> / <strong>admin123</strong></li>
                    </ol>
                </div>
                
                <div class="space-x-4">
                    <a href="index.php" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition inline-block">
                        <i class="fas fa-home mr-2"></i>Go to Website
                    </a>
                    <a href="admin/login.php" class="bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-green-700 transition inline-block">
                        <i class="fas fa-user-shield mr-2"></i>Admin Login
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500">
            <p>&copy; 2024 SMK Negeri 1 Jakarta. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
