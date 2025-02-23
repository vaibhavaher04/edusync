<?php
require_once('config.php'); // Database connection

// Fetch colleges for the dropdown
$stmt = $db->query("SELECT college_id, college_name FROM colleges");
$colleges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <style>
         :root {
            --primary: #6c63ff;
            --success: #00bf8e;
            --light-bg: #2e3267;
            --dark-bg: #1f2641;
            --accent: #424890;
            --text-light: #ffffff;
        }

        body {
            background-color: var(--dark-bg);
            font-family: Arial, sans-serif;
            color: var(--text-light);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: var(--primary);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select, input[type="text"], input[type="password"] {
            width: 95%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid var(--accent);
            border-radius: 5px;
            background-color: var(--dark-bg);
            color: var(--text-light);
        }

        button {
            background-color: var(--primary);
            color: var(--text-light);
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: var(--success);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }

            h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Teacher Login</h2>
        <form action="teacher_login_action.php" method="POST">
            <label for="college">Select College:</label>
            <select name="college_id" id="college" required>
                <option value="">-- Select College --</option>
                <?php foreach ($colleges as $college): ?>
                    <option value="<?php echo htmlspecialchars($college['college_id']); ?>">
                        <?php echo htmlspecialchars($college['college_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>