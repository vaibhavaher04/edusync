<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduSync</title>
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

    <!-- GOOGLE FONTS (MONTSERRAT) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
        
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <div class="login-container">
        <form id="loginForm" class="login-form" action="login_action.php" method="POST">
            <h2>Login</h2>
            <input type="text" id="emailOrMobile" name="emailOrMobile" placeholder="Email or Mobile Number" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <p class="register-link">Not registered? <a href="register.html">Register here</a></p>
            <!-- Display error message here -->
            <?php if(isset($_GET['error'])): ?>
                <p class="error"><?php echo $_GET['error']; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>