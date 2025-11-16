<?php 
session_start();
ini_set('memory_limit', '-1');
$userlogin=$_SESSION['Gov_user'];
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

$sql = "SELECT crop, quantity FROM production_approx;";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Agriculture Portal - Production Approximator</title>

<!-- Bootstrap & base CSS -->
<link rel="stylesheet" href="css/bootstrap.css"/>
<link rel="stylesheet" href="css/style.css"/>
<script src="js/jquery.min.js"></script>

<!-- Fonts and Icons -->
<link href='https://fonts.googleapis.com/css?family=Poppins:400,500,600,700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Merriweather:400,700italic' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Niconne&family=Oswald:wght@400;500;700&display=swap" rel="stylesheet">

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
    padding-top: 40px;
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

/* Table Styling */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    padding: 25px;
    margin: 30px auto;
    max-width: 900px;
}

.table-title {
    text-align: center;
    color: #1a472a;
    font-family: 'Merriweather', serif;
    margin-bottom: 25px;
    font-size: 28px;
    position: relative;
    padding-bottom: 15px;
}

.table-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: #4CAF50;
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

.table th {
    background-color: #4CAF50;
    color: white;
    font-weight: 500;
    text-align: center;
    padding: 15px;
    font-size: 18px;
}

.table td {
    text-align: center;
    padding: 12px 15px;
    vertical-align: middle;
    font-size: 16px;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(76, 175, 80, 0.1);
}

.table-hover tbody tr:hover {
    background-color: rgba(76, 175, 80, 0.2);
}

.crop-icon {
    margin-right: 10px;
    color: #4CAF50;
    font-size: 20px;
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

.goog-te-banner-frame.skiptranslate, .goog-te-gadget-icon {
    display: none !important;
}

.goog-te-gadget {
    color: transparent !important;
}

.goog-te-gadget .goog-te-combo {
    margin: 4px 0;
    padding: 6px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background: white;
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
            <h1><a href="production_approx.php">Production Approximator</a></h1>
        </div>
    </div>
</div>
<!-- ================= HEADER END ================= -->

<div class="main-content">
    <!-- Welcome Message -->
    <div class="welcome-message">
        <h2>Crop Production Data</h2>
        <p>View approximate crop production quantities across the region</p>
    </div>

    <!-- Table Section -->
    <div class="table-container">
        <h2 class="table-title"><i class="fas fa-table me-2"></i>Production Statistics</h2>
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th><center><i class="fas fa-leaf me-2"></i>Crop Name</center></th>
                        <th><center><i class="fas fa-weight-hanging me-2"></i>Quantity (in KG)</center></th>
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    $cropIcons = [
                        'wheat' => 'fas-wheat-alt',
                        'rice' => 'fas-rice',
                        'corn' => 'fas-corn',
                        'vegetable' => 'fas-carrot',
                        'fruit' => 'fas-apple-alt',
                        'pulse' => 'fas-seedling',
                        'default' => 'fas-seedling'
                    ];
                    
                    while($row = $result->fetch_assoc()) {
                        $x = ucfirst($row["crop"]);
                        $y = number_format($row["quantity"]);
                        
                        // Determine icon based on crop type
                        $iconClass = 'fas-seedling'; // default
                        foreach($cropIcons as $key => $icon) {
                            if (stripos($x, $key) !== false) {
                                $iconClass = $icon;
                                break;
                            }
                        }
                        
                        echo "<tr>";
                        echo "<td><center><i class='$iconClass me-2 crop-icon'></i>$x</center></td>";
                        echo "<td><center>$y</center></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Features Section -->
    <div class="feature-section">
        <h2 class="section-title">Government Services</h2>
        
        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <h3>Production Analytics</h3>
                <p>Track and analyze crop production data across regions.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-tractor"></i>
                <h3>Farm Subsidies</h3>
                <p>Manage and distribute agricultural subsidies to farmers.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-seedling"></i>
                <h3>Crop Monitoring</h3>
                <p>Monitor crop health and growth patterns in different regions.</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-cloud-sun-rain"></i>
                <h3>Weather Data</h3>
                <p>Access weather patterns and forecasts for agricultural planning.</p>
            </div>
        </div>
    </div>
</div>

<!-- ================= FOOTER ================= -->
<div class="footer">
    <p>&copy; Agriculture Portal. All Rights Reserved.</p>
    <p class="text-muted">Empowering farmers through technology</p>
</div>

<!-- Google translate -->
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>