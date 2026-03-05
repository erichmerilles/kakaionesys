<?php
// KakaiOnesys/frontend/auth/login.php

// 1. Strict Session Security (Defeats session hijacking)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 43200,
        'path' => '/',
        'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// 2. Anti-CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Auto-Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /KakaiOnesys/frontend/dashboard/admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KakaiOne | Staff Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* Fallback styles in case style.css is missing these specific alignments */
        .login-page {
            background-color: #f4f6f9;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .brand img {
            max-width: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="login-page">
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-card text-center">

            <div class="brand">
                <img src="../assets/images/logo.jpg" alt="KakaiOne Logo">
                <h3 class="fw-bold text-dark mb-0">KakaiOne</h3>
                <p class="text-muted small">Staff Login</p>
            </div>

            <form id="loginForm" class="mt-4">

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="mb-3 text-start">
                    <label for="username" class="form-label fw-semibold">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autofocus>
                </div>

                <div class="mb-4 text-start">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold">Login</button>
            </form>

            <p class="footer-text mt-4 text-muted small">© <?php echo date('Y'); ?> KakaiOne | All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById("loginForm").addEventListener("submit", async (e) => {
            e.preventDefault();

            // Use window.FormData to safely capture inputs (including the CSRF token)
            const formData = new window.FormData(e.target);

            // Basic frontend validation
            if (!formData.get("username").trim() || !formData.get("password").trim()) {
                Swal.fire("Error", "Please enter both username and password.", "warning");
                return;
            }

            try {
                // Point to our new, secure processor
                const res = await fetch("/KakaiOnesys/backend/auth/login_processor.php", {
                    method: "POST",
                    body: formData // Sends as multipart/form-data, replacing the old JSON stringify method
                });

                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Welcome!",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Dynamically route based on role (Admin vs Cashier vs Stockman)
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Login Failed",
                        text: data.message || "Invalid credentials."
                    });
                }
            } catch (error) {
                console.error("Login Error:", error);
                Swal.fire("System Error", "Unable to connect to the server. Please check your connection.", "error");
            }
        });
    </script>
</body>

</html>