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
        <a href="index.html"><h1 id="logo">TRADE</h1></a>
        <button class="hamburger" id="hamburger">&#9776;</button>
        <ul class="nav-links" id="nav-links">
            <li><a href="index.html#hero">Home</a></li>
            <li class="divider">|</li>
            <li><a href="index.html#howItWorks">How It Works</a></li>
            <li class="divider">|</li>
            <li><a href="index.html#about">About</a></li>
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
                <p>Drag & drop your receipt image here, or</p>
                <input type="file" id="fileElem" style="display:none" name="image" required>
                <label class="upload-btn" for="fileElem">Browse Files</label>
                <span class="mobile-upload-label">Tap here to upload your image</span>
                <button type="submit" name="upload">Upload & Detect</button>
            </form>
        </div>
    </div>
    <div class="container" id="result">
        <div id="preview">
            <!-- JS will inject the image here -->
            <h2 id="resultText" style="display:none;">Your uploaded receipt</h2>
            <?php
            if (isset($_POST['upload']) && isset($_FILES['image'])) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir);
                }
                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $result = shell_exec("C:\\Users\\mecsung\\AppData\\Local\\Programs\\Python\\Python39\\python.exe predict.py " . escapeshellarg($target_file) . " 2>&1");
                    
                    $result_lines = explode("\n", $result);
                    $clean_result = '';
                    foreach ($result_lines as $line) {
                        // Only keep lines that do not contain 'Warning' and are not empty
                        if (stripos($line, 'warning') === false && trim($line) !== '') {
                            $clean_result = $line;
                            break;
                        }
                    }
                    echo '<h3>Result: ' . htmlspecialchars(trim($clean_result)) . '</h3>';
                    // echo '<h3>Result: ' . htmlspecialchars(trim($result)) . '</h3>';
                    echo '<img src="' . htmlspecialchars($target_file) . '" alt="Uploaded Image">';
                      // Show heatmap only if it exists
                      $base_name = pathinfo($target_file, PATHINFO_FILENAME);
                      $heatmap_path = "heatmap/heatmap_" . $base_name . ".jpg";
                      if (file_exists($heatmap_path)) {
                          echo '<h3>Heatmap (AI/Tampered regions):</h3>';
                          echo '<img src="' . htmlspecialchars($heatmap_path) . '" alt="Heatmap Result" style="max-width:100%;height:auto;">';
                      }
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
