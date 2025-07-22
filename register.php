<?php
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $plan = $_POST['plan'] ?? '';
    // Basic validation
    if (strlen($username) < 3 || strlen($username) > 32) {
        $msg = 'Username must be 3-32 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Invalid email address.';
    } elseif (strlen($password) < 6 || strlen($password) > 64) {
        $msg = 'Password must be 6-64 characters.';
    } else {
        $valid_plans = ['user', 'subscriber', 'professional', 'lgu'];
        if (!in_array($plan, $valid_plans)) {
            $msg = 'Please select a valid plan.';
        } else {
            $usersFile = __DIR__ . '/users.txt';
            $exists = false;
            if (file_exists($usersFile)) {
                $lines = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $parts = explode('|', $line);
                    $u = $parts[0] ?? '';
                    $e = $parts[1] ?? '';
                    if (strtolower($u) === strtolower($username) || strtolower($e) === strtolower($email)) {
                        $exists = true;
                        break;
                    }
                }
            }
            if ($exists) {
                $msg = 'Username or email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $entry = $username . '|' . $email . '|' . $hash . '|' . $plan . "\n";
                file_put_contents($usersFile, $entry, FILE_APPEND | LOCK_EX);
                $msg = 'Registration successful! You can now log in.';
            }
        }
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
    <title>Register | TRADE</title>
    <link rel="stylesheet" href="style.css">
    <style>
      .register-container {
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
      .register-container h1 {
        margin-bottom: 18px;
        font-size: 2rem;
      }
      .register-container label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        width: 100%;
        text-align: left;
      }
      .register-container input[type="text"],
      .register-container input[type="email"],
      .register-container input[type="password"],
      .register-container select {
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
      .register-container button {
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
      .register-container button:hover {
        background: crimson;
      }
      .register-container .feedback {
        margin-top: 16px;
        font-size: 1rem;
        color: #ffb3b3;
        min-height: 24px;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="register-container">
      <h1>Register</h1>
      <form id="registerForm" method="POST" action="register.php" autocomplete="off">
        <label for="username"><p>Username</p></label>
        <input type="text" id="username" name="username" required minlength="3" maxlength="32">
        <label for="email"><p>Email</p></label>
        <input type="email" id="email" name="email" required>
        <label for="password"><p>Password</p></label>
        <input type="password" id="password" name="password" required minlength="6" maxlength="64">
        <label for="plan"><p>Select Plan</p></label>
        <select id="plan" name="plan" required>
          <option value="user">User</option>
          <option value="subscriber">Subscriber</option>
          <option value="professional">Professional</option>
          <option value="lgu">LGU</option>
        </select>
        <button type="submit">Register</button>
        <div class="feedback" id="feedback"><?php if ($msg) echo htmlspecialchars($msg); ?></div>
      </form>
      <p style="margin-top:18px; text-align:center; font-size:0.98rem;">Already have an account? <a href="login.php" style="color:#1E90FF;">Login</a></p>
    </div>
  </body>
</html>
