<?php
// Nama : Ibnu Hanafi Assalam
// NIM   : A12.2023.06994
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Ambil user_id dari session
$user_id = $_SESSION["user_id"];

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_validasi_form";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mendapatkan data pengguna berdasarkan user_id
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $username = $user["name"];
    $email = $user["email"];
} else {
    echo "Pengguna tidak ditemukan!";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
        }

        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover {
            transform: scale(1.02);
        }

        .card-header-custom {
            background: linear-gradient(45deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-icon {
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .quick-actions .btn {
            margin: 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .quick-actions .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        #live-clock {
            font-size: 1rem;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.8);
        }

        .activity-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .activity-card .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .activity-card .card-title {
            margin-bottom: 10px;
        }

        #dashboardModal,
        #settingsModal,
        #logoutModal .modal-content {
            border-radius: 15px;
        }

        #inactivityModal .modal-body {
            text-align: center;
            font-size: 1.2rem;
        }

        #countdownTimer {
            font-size: 2rem;
            font-weight: bold;
            color: red;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card dashboard-card">
                    <div class="card-header-custom">
                        <div>
                            <h2 class="mb-1">Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h2>
                            <small><?php echo htmlspecialchars($email); ?></small>
                            <div id="live-clock" class="mt-2"></div>
                        </div>
                        <i class="bi bi-person-circle profile-icon"></i>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions text-center">
                            <div class="row">
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-primary w-100" id="dashboardBtn">
                                        <i class="bi bi-graph-up-arrow me-2"></i>Dashboard
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-success w-100" id="settingsBtn">
                                        <i class="bi bi-gear me-2"></i>Pengaturan
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-danger w-100" id="logoutBtn">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="activity-summary">
                            <h4 class="text-center mb-3">Ringkasan Aktivitas</h4>
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="card bg-light mb-3 activity-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Login Terakhir</h5>
                                            <p id="last-login" class="card-text"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light mb-3 activity-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Status Akun</h5>
                                            <p class="card-text text-success">Aktif</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light mb-3 activity-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Hak Akses</h5>
                                            <p class="card-text">Pengguna Biasa</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Pesan Dashboard -->
    <div class="modal fade" id="dashboardModal" tabindex="-1" aria-labelledby="dashboardModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dashboardModalLabel">Dashboard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Fitur dashboard akan diupdate suatu hari.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Pesan Pengaturan -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">Pengaturan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Fitur pengaturan akan diupdate suatu hari.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Anda yakin ingin keluar dari halaman ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="logout.php" class="btn btn-danger">Keluar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Inaktivitas -->
    <div class="modal fade" id="inactivityModal" tabindex="-1" aria-labelledby="inactivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inactivityModalLabel">Peringatan Inaktivitas</h5>
                </div>
                <div class="modal-body">
                    Anda akan dikeluarkan dalam
                    <span id="countdownTimer">60</span>
                    detik karena tidak ada aktivitas.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="continueActivityBtn">Lanjutkan Aktivitas</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk memformat angka dengan leading zero
        function padZero(num) {
            return num.toString().padStart(2, '0');
        }

        // Fungsi untuk update waktu secara real-time
        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const day = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            const hours = padZero(now.getHours());
            const minutes = padZero(now.getMinutes());
            const seconds = padZero(now.getSeconds());

            const fullDateTime = `${day}, ${date} ${month} ${year} - ${hours}:${minutes}:${seconds}`;

            document.getElementById('live-clock').textContent = fullDateTime;
            document.getElementById('last-login').textContent = fullDateTime;
        }

        // Update clock immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);

        // Event listener untuk tombol dashboard
        document.getElementById('dashboardBtn').addEventListener('click', function(e) {
            e.preventDefault();
            var dashboardModal = new bootstrap.Modal(document.getElementById('dashboardModal'));
            dashboardModal.show();
        });

        // Event listener untuk tombol pengaturan
        document.getElementById('settingsBtn').addEventListener('click', function(e) {
            e.preventDefault();
            var settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));
            settingsModal.show();
        });

        // Event listener untuk tombol logout
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        });

        // Inactivity Timeout
        let inactivityTimer;
        let countdownTimer;
        let inactivityModal;
        const INACTIVITY_TIME = 60000; // 1 menit
        const COUNTDOWN_TIME = 60; // 60 detik

        function resetInactivityTimer() {
            // Clear existing timers
            clearTimeout(inactivityTimer);
            clearInterval(countdownTimer);

            // Hide inactivity modal if it's open
            const modalElement = document.getElementById('inactivityModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }

            // Start new inactivity timer
            inactivityTimer = setTimeout(showInactivityModal, INACTIVITY_TIME);
        }

        function showInactivityModal() {
            inactivityModal = new bootstrap.Modal(document.getElementById('inactivityModal'));
            inactivityModal.show();

            let timeLeft = COUNTDOWN_TIME;
            const countdownElement = document.getElementById('countdownTimer');

            countdownTimer = setInterval(() => {
                timeLeft--;
                countdownElement.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    window.location.href = 'logout.php'; // Redirect to logout page
                }
            }, 1000);
        }

        // Event listeners to reset inactivity timer
        ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt =>
            document.addEventListener(evt, resetInactivityTimer, false)
        );

        // Lanjutkan Aktivitas Button
        document.getElementById('continueActivityBtn').addEventListener('click', function() {
            resetInactivityTimer();
        });

        // Start initial inactivity timer
        resetInactivityTimer();
    </script>
</body>

</html>