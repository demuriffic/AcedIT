<?php
session_start();
// Only allow access if logged in and plan is 'lgu'
if (!isset($_SESSION['user'])) {
    header('Location: login.php?msg=' . urlencode('Please log in as LGU Admin to access the dashboard.'));
    exit;
}
$username = $_SESSION['user'];
$plan = '';
$mysqli = new mysqli('localhost', 'root', '', 'receiptsdb'); // adjust user/pass/db as needed
if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: ' . $mysqli->connect_error);
}
$stmt = $mysqli->prepare('SELECT plan FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($plan);
$stmt->fetch();
$stmt->close();
// --- Stats endpoint for AJAX ---
if (isset($_GET['stats'])) {
    $total = 0; $fake = 0; $real = 0;
    $result = $mysqli->query('SELECT COUNT(*) as total, SUM(result="fake") as fake, SUM(result="real") as real FROM receipts');
    if ($row = $result->fetch_assoc()) {
        $total = (int)$row['total'];
        $fake = (int)$row['fake'];
        $real = (int)$row['real'];
    }
    $ratio = $real > 0 ? round($fake / $real, 2) : ($fake > 0 ? '∞' : 0);
    header('Content-Type: application/json');
    echo json_encode([
        'total_tests' => $total,
        'fake_count' => $fake,
        'real_count' => $real,
        'ratio' => $ratio
    ]);
    exit;
}
if ($plan !== 'lgu') {
    // Show error message directly instead of redirecting
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bitcount+Single:wght@100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <link rel="stylesheet" href="style.css">
        <style>
            body { background: #181818; color: #fff; font-family: 'Inter', sans-serif; }
            .error-container {
                max-width: 480px;
                margin: 120px auto 0 auto;
                background: #232323;
                border-radius: 12px;
                box-shadow: 0 2px 16px 0 rgba(18,18,18,0.13);
                padding: 48px 32px;
                text-align: center;
            }
            .error-container h1 { color: crimson; margin-bottom: 18px; }
            .error-container p { color: #fff; font-size: 1.1rem; }
            .error-container a { color: #1E90FF; text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Access Denied</h1>
            <p>Only LGU Admins can access the dashboard.</p>
            <p><a href="index.php">Return to Home</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
// If allowed, show the dashboard HTML directly
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bitcount+Single:wght@100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Admin Dashboard</title>
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
    <div class="container" id="dashboard">
        <h1>Dashboard</h1>
        <p>Welcome to the admin dashboard. Here you can manage your application.</p>
        <div class="statistics">
            <div class="statistic">
                <h2>Total Tests Done</h2>
                <div id="total-tests" style="font-size:2rem;font-weight:bold;">0</div>
            </div>
            <div class="statistic">
                <h2>Fake Receipts</h2>
                <div id="fake-count" style="font-size:2rem;font-weight:bold;">0</div>
            </div>
            <div class="statistic">
                <h2>Real Receipts</h2>
                <div id="real-count" style="font-size:2rem;font-weight:bold;">0</div>
            </div>
            <div class="statistic">
                <h2>Fake/Real Ratio</h2>
                <div id="ratio" style="font-size:2rem;font-weight:bold;">0</div>
            </div>
        </div>
        <canvas id="statsChart" width="700" height="300"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Fetch stats from PHP endpoint
    async function fetchStats() {
        const res = await fetch('dashboard.php?stats=1');
        if (!res.ok) return;
        const data = await res.json();
        document.getElementById('total-tests').textContent = data.total_tests;
        document.getElementById('fake-count').textContent = data.fake_count;
        document.getElementById('real-count').textContent = data.real_count;
        document.getElementById('ratio').textContent = data.ratio;
        // Chart.js
        const ctx = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Fake', 'Real'],
                datasets: [{
                    label: 'Receipts',
                    data: [data.fake_count, data.real_count],
                    backgroundColor: ['crimson', '#1E90FF']
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    }
    fetchStats();
    </script>
    <script src="dashboard.js"></script>
</body>
</html>
