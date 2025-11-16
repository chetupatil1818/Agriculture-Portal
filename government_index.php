<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Government Agriculture Portal</title>

<!-- Bootstrap & base CSS -->
<link rel="stylesheet" href="css/bootstrap.css"/>
<link rel="stylesheet" href="css/style.css"/>
<script src="js/jquery.min.js"></script>

<!-- Fonts and Icons -->
<link href='https://fonts.googleapis.com/css?family=Poppins:400,500,600,700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Merriweather:400,700italic' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Niconne&family=Oswald:wght@400;500;700&display=swap" rel="stylesheet">

<!--Notify-->
<link href="css/pnotify.css" rel="stylesheet">
<link href="css/pnotify.brighttheme.css" rel="stylesheet">
<script src="js/pnotify.js"></script>

<!-- Custom Theme files -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>

<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Oswald', sans-serif;
    background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
    color: #333;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.header-banner {
    background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80') no-repeat center center;
    background-size: cover;
    color: #fff;
    min-height: 220px;
    position: relative;
    padding: 20px 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.banner-info {
    text-align: center;
    padding-top: 70px;
}

.banner-info h1 a {
    font-family: 'Niconne', cursive;
    font-size: 56px;
    color: #fff;
    text-decoration: none;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.menu {
    display: none;
    cursor: pointer;
}

@media (max-width: 768px) {
    .menu {
        display: block;
        float: left;
    }
    
    .top-menu ul {
        display: none;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px;
    }
    
    .top-menu ul li {
        display: block;
        margin: 5px 0;
    }
    
    .banner-info h1 a {
        font-size: 42px;
    }
    
    .header-grids {
        flex-direction: column;
        align-items: center;
    }
    
    .feature-card {
        margin-bottom: 20px;
    }
}

.footer {
    background: #333;
    color: #fff;
    text-align: center;
    padding: 20px 0;
    margin-top: auto;
    font-family: 'Oswald', sans-serif;
}

.social-icons {
    float: right;
}

.top-menu ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.top-menu ul li {
    display: inline-block;
    margin-left: 15px;
}

.top-menu ul li a {
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 16px;
    text-transform: uppercase;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 8px 15px;
    border-radius: 5px;
    background: rgba(76, 175, 80, 0.7);
}

.top-menu ul li a:hover {
    background: rgba(76, 175, 80, 1);
    color: #fff;
    text-decoration: none;
    transform: translateY(-2px);
}

.clearfix {
    clear: both;
}

/* Government Feature Blocks */
.gov-feature-block {
    background: #4CAF50 !important;
    color: #fff;
    padding: 25px 20px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    width: 280px;
    height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    transition: all 0.3s ease;
    text-decoration: none;
    margin: 30px auto;
}

.gov-feature-block:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    text-decoration: none;
}

.gov-feature-block i {
    font-size: 42px;
    margin-bottom: 15px;
    color: #fff;
}

.gov-feature-block h4 {
    color: #fff;
    font-family: 'Oswald', sans-serif;
    font-size: 22px;
    margin-bottom: 10px;
    font-weight: 600;
}

.header-grids {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 40px;
    flex-wrap: wrap;
    margin: 30px 0;
}

/* Additional homepage elements */
.feature-section {
    margin: 40px auto;
    max-width: 900px;
    padding: 0 20px;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.feature-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-align: center;
    transition: all 0.3s ease;
    border-left: 4px solid #4CAF50;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.feature-card i {
    font-size: 36px;
    color: #4CAF50;
    margin-bottom: 15px;
}

.feature-card h3 {
    color: #1a472a;
    font-family: 'Oswald', sans-serif;
    margin-bottom: 12px;
}

.feature-card p {
    color: #666;
    font-family: 'Oswald', sans-serif;
    line-height: 1.5;
}

.section-title {
    text-align: center;
    color: #1a472a;
    font-family: 'Merriweather', serif;
    margin-bottom: 30px;
    font-size: 28px;
    position: relative;
    padding-bottom: 15px;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: #4CAF50;
}

.welcome-message {
    text-align: center;
    margin: 30px 0;
    padding: 0 20px;
}

.welcome-message h2 {
    color: #1a472a;
    font-family: 'Merriweather', serif;
    margin-bottom: 15px;
}

.welcome-message p {
    color: #666;
    font-family: 'Oswald', sans-serif;
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.6;
    font-size: 18px;
}

.script-for-menu {
    display: none;
}
</style>
</head>

<body>
<!-- ================= HEADER ================= -->
<div class="header-banner">
    <div class="container">
        <div class="header-top">
            <div class="social-icons">
                <div id="google_translate_element"></div>
                <script type="text/javascript">
                function googleTranslateElementInit() {
                    new google.translate.TranslateElement({
                        pageLanguage:'en',
                        includedLanguages:'bn,en,gu,hi,kn,mr,ta,te'
                    },'google_translate_element');
                }
                </script>
            </div>

            <span class="menu"><img src="images/nav.png" alt=""/></span>
            <div class="top-menu">
                <ul>
                    <nav class="cl-effect-13">
                        <li><a href="government_index.php">Home</a></li>
                        <li><a href="php/logout.php">Logout</a></li>
                    </nav>
                </ul>
            </div>
            <div class="clearfix"></div>

            <script>
            $( "span.menu" ).click(function(){
                $( ".top-menu ul" ).slideToggle(300);
            });
            </script>
        </div>

        <div class="banner-info text-center">
            <h1><a href="government_index.php">Government Agriculture Portal</a></h1>
        </div>
        
        <!--Main Agriculture Components Starts-->
        <div class="header-grids text-center">
            <!-- Production Approximator Block -->
            <a href="production_approx.php" class="gov-feature-block">
                <i class="fas fa-calculator"></i>
                <h4>Production Approximator</h4>
                <p>Estimate crop yields</p>
            </a>
            
            <!-- Farmer's Credentials Block -->
            <a href="gov_credentials.php" class="gov-feature-block">
                <i class="fas fa-id-card"></i>
                <h4>Farmer's Credentials</h4>
                <p>Manage farmer records</p>
            </a>
        </div>
        <!--Main Agriculture Components Ends-->
    </div>
</div>
<!-- ================= HEADER END ================= -->

<div class="main-content">
    <!-- Welcome Message -->
    <div class="welcome-message">
        <h2>Welcome to Government Agriculture Portal</h2>
        <p>Your platform for agricultural management and farmer support services.</p>
    </div>

    <!-- Features Section -->
    <div class="feature-section">
        <h2 class="section-title">Government Services</h2>
        
        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-chart-bar"></i>
                <h3>Production Analytics</h3>
                <p>Track and analyze agricultural production data across regions.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-file-contract"></i>
                <h3>Farmer Documentation</h3>
                <p>Manage farmer credentials, subsidies, and support programs.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-tractor"></i>
                <h3>Resource Allocation</h3>
                <p>Coordinate distribution of agricultural resources and equipment.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-hand-holding-usd"></i>
                <h3>Subsidy Management</h3>
                <p>Administer government subsidies and financial support programs.</p>
            </div>
        </div>
    </div>
</div>

<!-- ================= FOOTER ================= -->
<div class="footer">
    <p>&copy; Government Agriculture Portal. All Rights Reserved.</p>
    <p class="text-muted">Serving the agricultural community</p>
</div>

<!-- Google translate -->
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $().UItoTop({ easingType: 'easeOutQuart' });
    });
</script>
</body>
</html>