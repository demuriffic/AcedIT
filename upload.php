<?php
    session_start();
    $user_id = $_SESSION['user'] ?? null;

    $conn = new mysqli("localhost", "root", "", "receiptsdb");

    $sql = "SELECT plan, tokens_used, tokens_reset FROM users WHERE username = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) { 
        $row = mysqli_fetch_assoc($result);
        $plan = $row['plan'];
        $tokens_used = $row['tokens_used'];
        $tokens_reset = new DateTime($row['tokens_reset']);
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
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Upload Your Receipt | TRADE</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <div class="navbar">
        <a href="index.php"><h1 id="logo">TRADE</h1></a>
        <button class="hamburger" id="hamburger">&#9776;</button>
        <ul class="nav-links" id="nav-links">
            <li><a href="upload.php">Upload</a></li> 
            <li class="divider">|</li>
            <li><a href="profile.php">Profile</a></li> 
            <li class="divider">|</li>
            <li><a href="index.php#howItWorks">How It Works</a></li>
            <li class="divider">|</li>
            <li><a href="index.php#about">About</a></li>
            <li class="divider">|</li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h1>Upload Your Receipt</h1>
        <div id="drop-area" class="drop-area">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                <rect width="48" height="48" rx="12" fill="#121212"/>
                <path d="M24 34V18M24 18L18 24M24 18L30 24" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="12" y="36" width="24" height="2.5" rx="1.25" fill="#FFFFFF" fill-opacity="0.7"/>
              </svg>
            <form class="upload-form" method="POST" enctype="multipart/form-data">
                <?php echo $tokens_used ." credits remaining"; ?>
                <p>Drag & drop your receipt image here, or</p>
                <input type="file" id="fileElem" style="display:none" name="image" required>
                <label class="upload-btn" for="fileElem">Browse Files</label>
                <span class="mobile-upload-label">Tap here to upload your image</span>
                <button type="submit" name="upload" class="upload-btn">Upload & Detect</button>
                <div id="preview"></div>
            </form>
        </div>
    </div>
    <div class="container" id="result">
            <?php
            
            if (isset($_POST['upload']) && isset($_FILES['image'])) {
                $target_dir = "uploads/";

                if (!is_dir($target_dir)) {
                    mkdir($target_dir);
                }
                $target_file = $target_dir . basename($_FILES["image"]["name"]);

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $result = shell_exec("python predict.py " . escapeshellarg($target_file) . " 2>&1");
                    
                    $result_lines = explode("\n", $result);
                    $clean_result = '';
                    $color_class = 'result-tampered';
                    
                    // Color coding based on result
                    if (stripos($result, 'AI-Generated') == true) {
                        $color_class = 'result-ai';
                    } elseif (stripos($result, 'Authentic') == true) {
                        $color_class = 'result-authentic';
                    }
                    foreach ($result_lines as $line) {
                        // Only keep lines that do not contain 'Warning' and are not empty
                        if (stripos($line, 'warning') === false && trim($line) !== '') {
                            $clean_result = $line;
                            break;
                        }
                    }
                    echo '<div class="result-img"><img src="' . htmlspecialchars($target_file) . '" alt="Uploaded Image">';
                    // Show heatmap only if it exists
                    $base_name = pathinfo($target_file, PATHINFO_FILENAME);
                    $heatmap_path = "heatmap/heatmap_" . $base_name . ".jpg";
                    if (file_exists($heatmap_path)) {
                        echo '<img src="' . htmlspecialchars($heatmap_path) . '" alt="Heatmap Result">';
                    }
                    echo '</div>';
                    // Display the result with color coding
                    echo '<div id="result-text" class="' . $color_class . '">';
                    echo '<h3>Result: ' . htmlspecialchars(trim($clean_result)) . '</h3>';
                    echo '<p class="result-description">This result indicates the authenticity of the receipt based on our AI model.</p>';
                    echo '</div>';
                    // Show the report button only if the result is AI-Generated or Tampered
                    if (stripos($result, 'AI-Generated') == true) {
                        echo '<div id="report"><button class="report-btn">Report AI-Generated Receipt</button>';
                    } elseif (stripos($result, 'Authentic') == true) {
                        echo '<button class="report-btn">Report Authentic Receipt</button></div>';
                    }
                    } else {
                        echo "<h3>Error uploading file.</h3>";
                    }

                    // After a successful check, increment tokens_used:
                    $tokens_used = $tokens_used - 1;

                    $user_id = mysqli_real_escape_string($conn, $user_id);
                    $update = "UPDATE users SET tokens_used='$tokens_used' WHERE username = '$user_id'";
                    $result_update = mysqli_query($conn, $update);
                }
            ?>
    </div>
    <script src="index.js"></script>
    <script src="upload.js"></script>
  </body>
</html>