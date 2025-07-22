<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php?msg=' . urlencode('Please log in to upload receipts.'));
    exit;
}

// --- TOKEN SYSTEM LOGIC ---
$mysqli = new mysqli('localhost', 'root', '', 'receiptsdb');
if ($mysqli->connect_errno) {
    die('DB error: ' . htmlspecialchars($mysqli->connect_error));
}
$user = $_SESSION['user'];
$stmt = $mysqli->prepare('SELECT plan, tokens_used, tokens_reset FROM users WHERE username = ?');
$stmt->bind_param('s', $user);
$stmt->execute();
$stmt->bind_result($plan, $tokens_used, $tokens_reset);
$stmt->fetch();
$stmt->close();

// Set plan limits (user, subscriber, professional, lgu)
$limits = [
    'user' => 50,
    'subscriber' => 500,
    'professional' => 1250,
    'lgu' => -1 // unlimited
];
$today = date('Y-m-d');
$month = date('Y-m');
$reset_month = $tokens_reset ? date('Y-m', strtotime($tokens_reset)) : null;
$limit = $limits[$plan] ?? 50;

// Always reset tokens_used at the start of a new month (except for lgu, which is unlimited anyway)
if ($plan !== 'lgu') {
    if ($reset_month !== $month) {
        $tokens_used = 0;
        $stmt = $mysqli->prepare('UPDATE users SET tokens_used=0, tokens_reset=? WHERE username=?');
        $stmt->bind_param('ss', $today, $user);
        $stmt->execute();
        $stmt->close();
    }
    if ($limit > 0 && $tokens_used >= $limit) {
        echo '<div class="container" style="margin-top:80px;"><h3>You have reached your monthly limit of ' . $limit . ' checks.<br>Upgrade your plan or wait until next month.</h3></div>';
        exit;
    }
}
?>
// --- END TOKEN SYSTEM LOGIC ---
<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bitcount+Single:wght@100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Upload Your Receipt | TRADE</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="navbar">
        <a href="index.php"><h1 id="logo">TRADE</h1></a>
        <button class="hamburger" id="hamburger">&#9776;</button>
        <ul class="nav-links" id="nav-links">
            <li><a href="index.php#hero">Home</a></li>
            <li class="divider">|</li>
            <li><a href="index.php#howItWorks">How It Works</a></li>
            <li class="divider">|</li>
            <li><a href="index.php#about">About</a></li>
        <?php if (isset($_SESSION['user'])): ?>
            <li class="divider">|</li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li class="divider">|</li>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
        </ul>
    </div>
    <div class="container" style="margin-top: 80px; padding: 20px;">
        <h1>Upload Your Receipt</h1>
        <div id="drop-area" class="drop-area">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                <rect width="48" height="48" rx="12" fill="#121212"/>
                <path d="M24 34V18M24 18L18 24M24 18L30 24" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="12" y="36" width="24" height="2.5" rx="1.25" fill="#FFFFFF" fill-opacity="0.7"/>
              </svg>
            <form class="upload-form" method="POST" enctype="multipart/form-data">
                <p>Drag & drop your receipt image here, or</p>
                <input type="file" id="fileElem" style="display:none" name="image" required>
                <label class="upload-btn" for="fileElem">Browse Files</label>
                <span class="mobile-upload-label">Tap here to upload your image</span>
                <button type="submit" class="upload-btn" style="margin-top:16px;">Submit</button>
                <div class="preview" id="preview"></div>
            </form>
        </div>
    </div>
    <div class="container" id="result">
        <div id="preview">
            <!-- JS will inject the image here -->
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir);
                }
                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // Calculate SHA256 hash of the file
                    $hash = hash_file('sha256', $target_file);
                    // Run AI prediction
                    $result = shell_exec("python predict.py " . escapeshellarg($target_file) . " 2>&1");
                    $result = trim($result);
                    // Check if hash already exists
                    $stmt = $mysqli->prepare('SELECT id FROM receipts WHERE file_hash = ?');
                    $stmt->bind_param('s', $hash);
                    $stmt->execute();
                    $stmt->store_result();
                    $is_unique = ($stmt->num_rows === 0);
                    if ($is_unique) {
                        // Insert new unique receipt
                        $stmt2 = $mysqli->prepare('INSERT INTO receipts (file_hash, result, uploaded_at) VALUES (?, ?, NOW())');
                        $stmt2->bind_param('ss', $hash, $result);
                        $stmt2->execute();
                        $stmt2->close();
                        // Increment tokens_used for non-LGU
                        if ($plan !== 'lgu' && $limit > 0) {
                            $stmt3 = $mysqli->prepare('UPDATE users SET tokens_used = tokens_used + 1 WHERE username = ?');
                            $stmt3->bind_param('s', $user);
                            $stmt3->execute();
                            $stmt3->close();
                        }
                        echo '<h3>Result: ' . htmlspecialchars($result) . ' (Unique - Counted)</h3>';
                    } else {
                        echo '<h3>Result: ' . htmlspecialchars($result) . ' (Duplicate - Not Counted)</h3>';
                    }
                    $stmt->close();
                    echo '<img src="' . htmlspecialchars($target_file) . '" alt="Uploaded Image" style="max-width:300px;">';
                } else {
                    echo "<h3>Error uploading file.</h3>";
                }
            }
            ?>
        </div>
    </div>
    <script src="index.js"></script>
    <script src="upload.js"></script>
  </body>
</html>
