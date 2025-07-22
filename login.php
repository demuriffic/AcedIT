<?php
session_start();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $usersFile = __DIR__ . '/users.txt';
    $found = false;
    if (file_exists($usersFile)) {
        $lines = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            $u = $parts[0] ?? '';
            $e = $parts[1] ?? '';
            $h = $parts[2] ?? '';
            if (strtolower($u) === strtolower($username) || strtolower($e) === strtolower($username)) {
                if (password_verify($password, $h)) {
                    $found = true;
                    $_SESSION['user'] = $u;
                    break;
                }
            }
        }
    }
    if ($found) {
        header('Location: dashboard.php?msg=' . urlencode('Login successful!'));
        exit;
    } else {
        $msg = 'Invalid username/email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bitcount+Single:wght@100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TRADE</title>
    <link rel="stylesheet" href="style.css">
    <style>
      .login-container {
        max-width: 600px;
        margin: 25px auto 0 auto;
        background: #181818;
        padding: 60px 48px;
        border-radius: 12px;
        box-shadow: 0 2px 16px 0 rgba(18,18,18,0.13);
        color: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
      }
      .login-container h1 {
        margin-bottom: 18px;
        font-size: 2rem;
      }
      .login-container label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        width: 100%;
        text-align: left;
      }
      .login-container input[type="text"],
      .login-container input[type="password"] {
        width: 96%;
        max-width: 420px;
        min-width: 25vw;
        box-sizing: border-box;
        padding: 14px 16px;
        margin-bottom: 18px;
        border-radius: 6px;
        border: 1px solid #333;
        background: #232323;
        color: #fff;
        font-size: 1.08rem;
        display: block;
        margin-left: auto;
        margin-right: auto;
      }
      .login-container button {
        width: 100%;
        padding: 12px;
        background: #1E90FF;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 1.1rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
      }
      .login-container button:hover {
        background: crimson;
      }
      .login-container .feedback {
        margin-top: 16px;
        font-size: 1rem;
        color: #ffb3b3;
        min-height: 24px;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="login-container">
      <h1>Login</h1>
      <form id="loginForm" method="POST" action="login.php" autocomplete="off">
        <label for="username">Username or Email</label>
        <input type="text" id="username" name="username" required maxlength="64">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required maxlength="64">
        <button type="submit">Login</button>
        <div class="feedback" id="feedback"><?php if ($msg) echo htmlspecialchars($msg); ?></div>
      </form>
      <p style="margin-top:18px; text-align:center; font-size:0.98rem;">Don't have an account? <a href="register.php" style="color:#1E90FF;">Register</a></p>
    </div>
  </body>
</html>
