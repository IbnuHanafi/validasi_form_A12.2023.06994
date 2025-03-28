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
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
        }

        .card {
            background: rgba(33, 33, 33, 0.8);
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
            width: 100%;
            max-width: 450px;
        }

        .card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transform: translateY(-8px);
        }

        .card-header {
            background-color: #0d47a1;
            text-align: center;
            border-radius: 20px 20px 0 0;
        }

        .card-title {
            font-size: 2.2rem;
            color: #ffffff;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .form-label {
            color: white;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 1rem;
            font-weight: 400;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.7);
            color: #fff;
        }

        .eye-icon {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #90caf9;
        }

        .btn-success {
            background: linear-gradient(45deg, #4caf50, #66bb6a);
            border: none;
            border-radius: 30px;
            padding: 14px 40px;
            font-size: 1.1rem;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #388e3c, #81c784);
            box-shadow: 0 5px 15px rgba(0, 255, 0, 0.5);
            transform: translateY(-3px);
        }

        .form-check-label {
            color: white;
        }

        .text-center a {
            color: #0d47a1;
        }

        .text-center a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            margin-top: 20px;
            padding: 15px;
            background-color: #ffcc00;
            color: #333;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title text-center">Login Admin</h2>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-warning">
                        <?php echo $error_message; ?>
                    </div>
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

                <p class="mt-3 text-center text-white">Belum punya akun? <a href="register.php"><b>Daftar</b></a></p>

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