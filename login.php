<?php
// Nama : Ibnu Hanafi Assalam
// NIM   : A12.2023.06994
session_start();
require_once 'config.php';

// Cek apakah pengguna sudah login
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

// Menyimpan CSRF token dalam sesi
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$error_message = '';

// Cek jika form login disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi token CSRF
    if ($_POST['token'] !== $_SESSION['token']) {
        die("Akses tidak sah!");
    }

    $username = $_POST['username'];
    $email = $_POST['email']; // Menambahkan email
    $password = $_POST['password'];

    // Prepared Statements untuk SQL Injection
    $stmt = $conn->prepare("SELECT id, passw FROM users WHERE name = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["passw"])) {
            $_SESSION["user_id"] = $user["id"];

            // Check Remember Me
            if (isset($_POST['remember'])) {
                // Set cookie for 30 days
                setcookie("user_id", $user["id"], time() + (30 * 24 * 60 * 60), "/");
                setcookie("username", $username, time() + (30 * 24 * 60 * 60), "/");
                setcookie("email", $email, time() + (30 * 24 * 60 * 60), "/");
            }

            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Password salah!";
        }
    } else {
        $error_message = "Username atau Email tidak ditemukan!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .eye-icon {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Login Admin</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?= $error_message ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">

                            <!-- Username -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required
                                    value="<?php echo isset($_COOKIE['username']) ? $_COOKIE['username'] : ''; ?>">
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="<?php echo isset($_COOKIE['email']) ? $_COOKIE['email'] : ''; ?>">
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <i class="fas fa-eye eye-icon" id="toggle-password" onclick="togglePassword()"></i>
                                </div>
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                    <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>

                            <!-- Button placed to the right -->
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-success">Login</button>
                            </div>
                        </form>

                        <!-- Link to Register -->
                        <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Daftar</a></p>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
        }
    </script>
</body>

</html>