<?php
session_start();

// Hardcoded credentials
$valid_username = 'admin';
$valid_password = 'abc@123';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --color-primary: #6c63ff;
            --color-success: #00bf8e;
            --color-warning: #f7c94b;
            --color-danger: #f75842;
            --color-danger-variant: rgba(247, 88, 66, 0.4);
            --color-white: #fff;
            --color-light: rgba(255, 255, 255, 0.7);
            --color-black: #000;
            --color-bg: #1f2641;
            --color-bg1: #2e3267;
            --color-bg2: #424890;

            --container-width-lg: 76%;
            --container-width-md: 90%;
            --container-width-sm: 94%;

            --transition: all 400ms ease;
        }

        body {
            background-color: var(--color-bg);
            color: var(--color-white);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .login-container {
            background-color: var(--color-bg1);
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            transition: var(--transition);
        }

        .login-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            transform: translateY(-5px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: var(--color-primary);
            font-weight: 600;
        }

        .form-control {
            background-color: var(--color-bg2);
            border: 1px solid var(--color-bg2);
            color: var(--color-white);
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .form-control:focus {
            background-color: var(--color-bg2);
            color: var(--color-white);
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25);
        }

        .btn-login {
            background-color: var(--color-primary);
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            width: 100%;
            transition: var(--transition);
        }

        .btn-login:hover {
            background-color: #5a52e0;
            transform: translateY(-2px);
        }

        .error-message {
            color: var(--color-danger);
            background-color: var(--color-danger-variant);
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 1.5rem;
                margin: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2>Admin Login</h2>
                <p>Please enter your credentials</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-login">Login</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>