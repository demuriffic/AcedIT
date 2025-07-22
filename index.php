<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bitcount+Single:wght@100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TRADE | Fake eReceipt Detector</title>
    <link rel="stylesheet" href="styles/style.css">
  </head>
  <body>
    <script>
      // Show ToS modal if not previously agreed
      window.addEventListener('DOMContentLoaded', function() {
        if (!localStorage.getItem('tosAgreed')) {
          document.getElementById('tosModal').classList.add('active');
          document.body.style.overflow = 'hidden'; // Prevent background scroll
        }
        document.getElementById('agreeTosBtn').onclick = function() {
          localStorage.setItem('tosAgreed', 'true');
          document.getElementById('tosModal').classList.remove('active');
          document.body.style.overflow = '';
        };
      });
    </script>
    <!-- Terms of Service Modal -->
    <div id="tosModal" class="tos-modal">
      <div class="tos-content">
        <h2>Terms of Service</h2>
        <p>
          By using this website and uploading images, you agree that your uploaded images may be collected and used to improve our AI and enhance user experience. Your data will be handled securely and used solely for research and development purposes. If you do not agree, please do not use the upload feature.
        </p>
        <button id="agreeTosBtn">I Agree</button>
      </div>
    </div>
    <div class="navbar">
        <a href="#hero"><h1 id="logo">TRADE</h1></a>
        <button class="hamburger" id="hamburger">&#9776;</button>
        <ul class="nav-links" id="nav-links">
            <li><a href="upload.php">Upload</a></li> 
            <li class="divider">|</li>
            <li><a href="#howItWorks">How It Works</a></li>
            <li class="divider">|</li>
            <li><a href="#about">About</a></li>
            <li class="divider">|</li>
            <li><a href="dashboard.php">Dashboard</a></li>
<?php if (isset($_SESSION['user'])): ?>
            <li class="divider">|</li>
            <li><a href="logout.php">Logout</a></li>
<?php else: ?>
            <li class="divider">|</li>
            <li><a href="login.php">Login</a></li>
<?php endif; ?>
        </ul>
    </div>
    <div class="container" id="hero">
        <h1>Welcome to TRADE</h1>
        <h2>Tampered Receipt AI Detection for E-Transactions</h2>
        <p>Upload a receipt, and we'll see if it's real or fake.</p>
        <button id="uploadButton">Try It Now</button>
    </div>    
    <div class="container" id="howItWorks">
        <h1>How It Works</h1>
        <div class="steps">
            <div class="step">
                <h1>1</h1>
                <h2>Upload Your Receipt</h2>
                <p>Drag and drop your receipt image or click to upload.</p>
            </div>
            <div class="step">
                <h1>2</h1>
                <h2>AI Analysis</h2>
                <p>Our AI analyzes the receipt for authenticity.</p>
            </div>
            <div class="step">
                <h1>3</h1>
                <h2>Get Results</h2>
                <p>Receive instant feedback on whether your receipt is real or fake.</p>
            </div>
    </div>
  </div>
    <div class="container" id="about">
        <h1>About Trade</h1>
        <p>TRADE stands for Tampered Receipt AI Detection for E-Transactions. It’s a tool we created to help spot fake or edited digital receipts. Some people try to change receipts using photo editing tools or even make new ones using AI. This can be used to trick stores, banks, or other businesses.
TRADE uses smart technology to check if a receipt is real or fake. It looks for signs that something has been changed or added. This helps businesses stay safe and makes sure people are being honest.
If you want to protect your business from fraud, TRADE can help you catch fake receipts before they cause problems.</p>
    </div>
    <!-- Pricing Modal -->
    <div id="pricingModal" class="pricing-modal">
      <div class="pricing-content">
        <button id="closePricing" class="close-pricing-btn" aria-label="Close">&times;</button>
        <h2>Choose Your Plan</h2>
        <div class="pricing-plans-row">
          <div class="plan">
            <h3>User (Basic)</h3>
            <p><strong>Free</strong></p>
            <p>50 Checks Monthly</p>
            <button class="select-plan" data-plan="user">Get Started</button>
          </div>
          <div class="plan">
            <h3>Subscriber</h3>
            <p><strong>500 Checks Monthly</strong></p>
            <p>249.99 PHP per Month</p>
            <button class="select-plan" data-plan="subscriber">Subscribe</button>
          </div>
          <div class="plan">
            <h3>Professional</h3>
            <p><strong>1000 + 250 Checks Monthly</strong></p>
            <p>449.99 PHP per Month</p>
            <button class="select-plan" data-plan="professional">Subscribe</button>
          </div>
        </div>
      </div>
    </div>
    <script src="index.js"></script>
    <script>
      // Show ?msg= message in a JS alert if present
      window.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('msg')) {
          alert(decodeURIComponent(params.get('msg')));
        }
      });
    </script>
  </body>
</html>