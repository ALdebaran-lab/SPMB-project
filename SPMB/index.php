<?php
session_start();

// Database connection untuk statistik
$host = 'localhost';
$dbname = 'ppdb_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pendaftar");
    $total_pendaftar = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as hari_ini FROM pendaftar WHERE DATE(tanggal_daftar) = CURDATE()");
    $pendaftar_hari_ini = $stmt->fetchColumn();
    
} catch(PDOException $e) {
    $total_pendaftar = 0;
    $pendaftar_hari_ini = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMB - Sistem Penerimaan Murid Baru SDN Majasetra 01</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#f59e0b'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-graduation-cap text-3xl text-yellow-300"></i>
                    <div>
                        <h1 class="text-xl font-bold">SPMB SDN Majasetra 01</h1>
                        <p class="text-sm text-blue-100">Sistem Penerimaan Murid Baru</p>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#beranda" class="hover:text-yellow-300 transition duration-300">Beranda</a>
                    <a href="#tentang" class="hover:text-yellow-300 transition duration-300">Tentang</a>
                    <a href="#jadwal" class="hover:text-yellow-300 transition duration-300">Jadwal</a>
                    <a href="#persyaratan" class="hover:text-yellow-300 transition duration-300">Persyaratan</a>
                    <a href="admin/login.php" class="text-yellow-300 hover:text-yellow-100 transition text-sm">
                        <i class="fas fa-user-shield mr-1"></i>Admin Login
                    </a>
                    <a href="pendaftaran.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 px-4 py-2 rounded-lg font-semibold transition duration-300">
                        <i class="fas fa-edit mr-2"></i>Daftar Sekarang
                    </a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="#beranda" class="hover:text-yellow-300 transition">Beranda</a>
                    <a href="#tentang" class="hover:text-yellow-300 transition">Tentang</a>
                    <a href="#jadwal" class="hover:text-yellow-300 transition">Jadwal</a>
                    <a href="#persyaratan" class="hover:text-yellow-300 transition">Persyaratan</a>
                    <a href="admin/login.php" class="text-yellow-300 hover:text-yellow-100 transition text-sm">
                        <i class="fas fa-user-shield mr-1"></i>Admin Login
                    </a>
                    <a href="pendaftaran.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 px-4 py-2 rounded-lg font-semibold text-center">
                        <i class="fas fa-edit mr-2"></i>Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                        Selamat Datang di
                        <span class="text-yellow-300">SDN Majasetra 01</span>
                    </h1>
                    <p class="text-xl mb-8 text-blue-100 leading-relaxed">
                        Membentuk generasi unggul dengan pendidikan berkualitas dan karakter yang baik. 
                        Bergabunglah dengan kami untuk masa depan yang cerah!
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="pendaftaran.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 px-8 py-4 rounded-lg font-bold text-lg transition duration-300 transform hover:scale-105">
                            <i class="fas fa-edit mr-2"></i>Daftar Sekarang
                        </a>
                        <a href="#tentang" class="border-2 border-white text-white hover:bg-white hover:text-blue-600 px-8 py-4 rounded-lg font-bold text-lg transition duration-300">
                            <i class="fas fa-info-circle mr-2"></i>Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                        <i class="fas fa-school text-8xl text-yellow-300 mb-6"></i>
                        <div class="grid grid-cols-2 gap-6 text-center">
                            <div class="bg-white/20 rounded-lg p-4">
                                <div class="text-3xl font-bold text-yellow-300"><?php echo $total_pendaftar; ?></div>
                                <div class="text-sm">Total Pendaftar</div>
                            </div>
                            <div class="bg-white/20 rounded-lg p-4">
                                <div class="text-3xl font-bold text-yellow-300"><?php echo $pendaftar_hari_ini; ?></div>
                                <div class="text-sm">Hari Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Tentang SDN Majasetra 01</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Sekolah Dasar Negeri Majasetra 01 adalah lembaga pendidikan unggulan yang berkomitmen 
                    untuk memberikan pendidikan berkualitas dan membentuk karakter siswa yang berakhlak mulia.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-blue-50 rounded-xl hover:shadow-lg transition duration-300">
                    <i class="fas fa-award text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Akreditasi A+</h3>
                    <p class="text-gray-600">Sekolah dengan akreditasi A+yang menjamin kualitas pendidikan terbaik</p>
                </div>
                <div class="text-center p-6 bg-yellow-50 rounded-xl hover:shadow-lg transition duration-300">
                    <i class="fas fa-users text-4xl text-yellow-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Guru Berpengalaman</h3>
                    <p class="text-gray-600">Tim pengajar profesional dan berpengalaman dalam mendidik siswa</p>
                </div>
                <div class="text-center p-6 bg-green-50 rounded-xl hover:shadow-lg transition duration-300">
                    <i class="fas fa-leaf text-4xl text-green-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Lingkungan Nyaman</h3>
                    <p class="text-gray-600">Lingkungan sekolah yang asri dan nyaman untuk proses belajar</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Schedule Section -->
    <section id="jadwal" class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Jadwal Pendaftaran</h2>
                <p class="text-xl text-gray-600">Informasi lengkap tentang jadwal pendaftaran siswa baru</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Pendaftaran Dibuka</h3>
                    <p class="text-blue-600 font-semibold">1 Januari 2025</p>
                    <p class="text-sm text-gray-600 mt-2">Pendaftaran online mulai dibuka</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Batas Pendaftaran</h3>
                    <p class="text-yellow-600 font-semibold">31 Maret 2025</p>
                    <p class="text-sm text-gray-600 mt-2">Batas akhir pendaftaran</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Pengumuman</h3>
                    <p class="text-green-600 font-semibold">15 April 2025</p>
                    <p class="text-sm text-gray-600 mt-2">Pengumuman hasil seleksi</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-lg text-center">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-graduation-cap text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Awal Masuk</h3>
                    <p class="text-purple-600 font-semibold">15 Juli 2025</p>
                    <p class="text-sm text-gray-600 mt-2">Hari pertama masuk sekolah</p>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="pendaftaran.php" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg font-bold text-lg transition duration-300">
                    <i class="fas fa-edit mr-2"></i>Daftar Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Requirements Section -->
    <section id="persyaratan" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Persyaratan Pendaftaran</h2>
                <p class="text-xl text-gray-600">Pastikan semua persyaratan terpenuhi sebelum mendaftar</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-12">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Persyaratan Umum</h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Usia Minimal</h4>
                                <p class="text-gray-600">Usia minimal 6 tahun per 1 Juli 2025</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Domisili</h4>
                                <p class="text-gray-600">Berdomisili di wilayah Jakarta Selatan</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Kesehatan</h4>
                                <p class="text-gray-600">Sehat jasmani dan rohani</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Kemampuan</h4>
                                <p class="text-gray-600">Mampu membaca dan menulis dasar</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Dokumen yang Diperlukan</h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-file-alt text-blue-500 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Akta Kelahiran</h4>
                                <p class="text-gray-600">Fotokopi akta kelahiran yang dilegalisir</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-file-alt text-blue-500 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Kartu Keluarga</h4>
                                <p class="text-gray-600">Fotokopi kartu keluarga</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-file-alt text-blue-500 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Surat Keterangan</h4>
                                <p class="text-gray-600">Surat keterangan domisili dari RT/RW</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-file-alt text-blue-500 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-gray-800">Foto</h4>
                                <p class="text-gray-600">Foto 3x4 sebanyak 1 lembar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Hubungi Kami</h2>
                <p class="text-xl text-gray-600">Informasi kontak dan lokasi sekolah</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-white rounded-xl shadow-lg">
                    <i class="fas fa-map-marker-alt text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Alamat</h3>
                    <p class="text-gray-600">Jl. Majasetra No. 222<br>Babakan, Bandung</p>
                </div>
                
                <div class="text-center p-6 bg-white rounded-xl shadow-lg">
                    <i class="fas fa-phone text-4xl text-green-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Telepon</h3>
                    <p class="text-gray-600">(021) 1234-5678<br>0812-3456-7890</p>
                </div>
                
                <div class="text-center p-6 bg-white rounded-xl shadow-lg">
                    <i class="fas fa-envelope text-4xl text-red-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Email</h3>
                    <p class="text-gray-600">info@majasetra01.sch.id<br>admin@majasetra01.sch.id</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">SDN Majasetra 01</h3>
                    <p class="text-gray-300 mb-4">
                        Membentuk generasi unggul dengan pendidikan berkualitas dan karakter yang baik.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white transition">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#beranda" class="text-gray-300 hover:text-white transition">Beranda</a></li>
                        <li><a href="#tentang" class="text-gray-300 hover:text-white transition">Tentang</a></li>
                        <li><a href="#jadwal" class="text-gray-300 hover:text-white transition">Jadwal</a></li>
                        <li><a href="#persyaratan" class="text-gray-300 hover:text-white transition">Persyaratan</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Pendaftaran</h4>
                    <ul class="space-y-2">
                        <li><a href="pendaftaran.php" class="text-gray-300 hover:text-white transition">Form Pendaftaran</a></li>
                        <li><a href="check_server.php" class="text-gray-300 hover:text-white transition">Status Server</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Kontak</h4>
                    <div class="space-y-2 text-gray-300">
                        <p><i class="fas fa-map-marker-alt mr-2"></i>Jl. Majasetra No. 222</p>
                        <p><i class="fas fa-phone mr-2"></i>(021) 1234-5678</p>
                        <p><i class="fas fa-envelope mr-2"></i>info@majasetra01.sch.id</p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-300">
                    &copy; 2025 SDN Majasetra 01. All rights reserved. | 
                    <a href="#" class="text-blue-400 hover:text-blue-300">Privacy Policy</a> | 
                    <a href="#" class="text-blue-400 hover:text-blue-300">Terms of Service</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-8 right-8 bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg transition duration-300 opacity-0 invisible">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Back to top button
        const backToTopButton = document.getElementById('back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('opacity-0', 'invisible');
                backToTopButton.classList.add('opacity-100', 'visible');
            } else {
                backToTopButton.classList.add('opacity-0', 'invisible');
                backToTopButton.classList.remove('opacity-100', 'visible');
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('section').forEach(section => {
            observer.observe(section);
        });

        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fadeIn 0.6s ease-out;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
