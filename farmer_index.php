<?php
session_start();
ini_set('memory_limit', '-1');
$userlogin=$_SESSION['farmer_login_user'];
$servername="localhost";
$username="root";
$password="";
$dbname="agriculture_portal";

//Create Connection 
$conn =mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Agriculture Portal</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Google Fonts -->
<link href='https://fonts.googleapis.com/css?family=Oswald:400,300,700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Niconne' rel='stylesheet' type='text/css'>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
}

.header-banner {
    background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80') no-repeat center center;
    background-size: cover;
    color: #fff;
    min-height: 320px;
    position: relative;
    padding: 20px 0;
}

.header-top {
    padding-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.top-left-menu {
    display: flex;
}

.top-left-menu ul {
    list-style: none;
    display: flex;
    gap: 15px;
    margin: 0;
    padding: 0;
}

.top-left-menu ul li a {
    color: #fff;
    text-decoration: none;
    font-size: 18px;
    padding: 8px 15px;
    border-radius: 5px;
    transition: all 0.3s;
    background: rgba(76, 175, 80, 0.7);
    display: inline-block;
}

.top-left-menu ul li a:hover {
    background: rgba(76, 175, 80, 1);
    transform: translateY(-2px);
}

.social-icons {
    text-align: right;
}

.menu {
    display: none;
    cursor: pointer;
}

.banner-info {
    text-align: center;
    padding-top: 40px;
    margin-bottom: 30px;
}

.banner-info h1 a {
    font-family: 'Niconne', cursive;
    font-size: 56px;
    color: #fff;
    text-decoration: none;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.header-grids, .header-bottom-grids {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 25px;
    margin-top: 30px;
}

.header-bottom-grid1, .header-bottom-grid2, .header-bottom-grid3 {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    padding: 20px;
    width: 180px;
    transition: all 0.3s;
    backdrop-filter: blur(5px);
    margin: 10px;
}

.header-bottom-grid1:hover, .header-bottom-grid2:hover, .header-bottom-grid3:hover {
    transform: translateY(-5px);
    background: rgba(76, 175, 80, 0.8);
}

.header-bottom-grid1 span, .header-bottom-grid2 span, .header-bottom-grid3 span {
    font-size: 40px;
    color: #fff;
    display: block;
    margin-bottom: 10px;
}

.header-bottom-grid1 h4 a, .header-bottom-grid2 h4 a, .header-bottom-grid3 h4 a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    font-size: 18px;
}

.footer {
    background: #333;
    color: #fff;
    text-align: center;
    padding: 20px 0;
    margin-top: 40px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .header-grids, .header-bottom-grids {
        gap: 15px;
    }
    
    .header-bottom-grid1, .header-bottom-grid2, .header-bottom-grid3 {
        width: 160px;
        padding: 15px;
    }
}

@media (max-width: 768px) {
    .header-top {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .top-left-menu {
        margin-bottom: 15px;
    }
    
    .top-left-menu ul {
        flex-direction: column;
        gap: 10px;
    }
    
    .social-icons {
        text-align: left;
        width: 100%;
    }
    
    .menu {
        display: block;
        position: absolute;
        top: 20px;
        right: 20px;
    }
    
    .header-grids, .header-bottom-grids {
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }
    
    .header-bottom-grid1, .header-bottom-grid2, .header-bottom-grid3 {
        width: 100%;
        max-width: 250px;
        margin: 5px 0;
    }
    
    .banner-info h1 a {
        font-size: 42px;
    }
}

@media (max-width: 576px) {
    .header-bottom-grid1, .header-bottom-grid2, .header-bottom-grid3 {
        width: 100%;
        max-width: 220px;
    }
    
    .header-bottom-grid1 h4 a, .header-bottom-grid2 h4 a, .header-bottom-grid3 h4 a {
        font-size: 16px;
    }
}

/* Animation for icons */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.header-bottom-grid1:hover span, 
.header-bottom-grid2:hover span, 
.header-bottom-grid3:hover span {
    animation: pulse 1s infinite;
}

/* Tab spacing between components */
.nav-tab {
    margin: 0 8px;
}
</style>
</head>
<body>
	<!-- header-section-starts -->
	<div class="header-banner">
		<div class="container">
			<div class="header-top">
				<div class="top-left-menu">
					<ul>
						<li><a href="farmer_index.php"><i class="fas fa-home"></i> Home</a></li>
						<li><a href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
					</ul>
				</div>
				
				<div class="social-icons">
					<div id="google_translate_element"></div>
					<script type="text/javascript">
					function googleTranslateElementInit() {
						new google.translate.TranslateElement({
							pageLanguage: 'en', 
							includedLanguages: 'bn,en,gu,hi,kn,mr,ta,te',
							layout: google.translate.TranslateElement.InlineLayout.SIMPLE
						}, 'google_translate_element');
					}
					</script>
				</div>
				
				<span class="menu"><i class="fas fa-bars fa-2x"></i></span>
			</div>
			
			<div class="banner-info">
				<h1><a href="farmer_index.php">Agriculture Portal</a></h1>
			</div>
			
			<!--Main Agriculture Components Starts-->
			<div class="header-grids text-center">
				<div class="header-bottom-grid1 nav-tab">
					<span><i class="fas fa-user fa-2x"></i></span>					
					<h4><a href="myprofile.php">My Profile</a></h4>
				</div>
				
				<div class="header-bottom-grid2 nav-tab">
					<span><i class="fas fa-leaf fa-2x"></i></span>
					<h4><a href="crop_predict.php">Crops</a></h4>
				</div>
				
				<div class="header-bottom-grid3 nav-tab">
					<span><i class="fas fa-cloud-sun fa-2x"></i></span>
					<h4><a href="upcomimg days.php">Weather Prediction</a></h4>
				</div>
			</div>

			<div class="header-bottom-grids text-center">
				<div class="header-bottom-grid1 nav-tab">
					<span><i class="fas fa-seedling fa-2x"></i></span>
					<h4><a href="tradecrops.php">Trade Crops</a></h4>
				</div>
				
				<div class="header-bottom-grid2 nav-tab">
					<span><i class="fas fa-newspaper fa-2x"></i></span>
					<h4><a href="newsfeed.php">News Feed</a></h4>
				</div>

				<div class="header-bottom-grid3 nav-tab">
					<span><i class="fas fa-history fa-2x"></i></span>
					<h4><a href="farmer_history.php">Selling History</a></h4>
				</div>

				<div class="clearfix"></div>
			</div>
			<!--Main Agriculture Components Ends-->
		</div>
	</div>

	<!-- footer-section -->
	<div class="footer">
		<div class="container">
			<div class="copyright text-center">
				<p>&copy; Agriculture Portal. All rights reserved </p>
			</div>
		</div>
	</div>
	<!-- footer-section -->
	
	<script type="text/javascript">
		// Menu toggle functionality
		$(document).ready(function() {
			$("span.menu").click(function() {
				$(".top-left-menu ul").slideToggle(300);
			});
			
			// Smooth scrolling
			$(".scroll").click(function(event){		
				event.preventDefault();
				$('html,body').animate({scrollTop:$(this.hash).offset().top},900);
			});
		});
	</script>
	
	<!-- Google Translate -->
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>