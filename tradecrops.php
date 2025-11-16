<?php 
session_start();
ini_set('memory_limit', '-1');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['Crop_submit'])) {
    $crop_name = $_POST['crops'];
    $quantity = $_POST['trade_farmer_cropquantity'];
    $price = $_POST['trade_farmer_cost'];

    // Fetch the current market price range for the selected crop
    $sql = "SELECT min_price, max_price FROM market_prices WHERE crop_name = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $crop_name);
    $stmt->execute();
    $stmt->bind_result($min_price, $max_price);
    $stmt->fetch();
    $stmt->close();

    // Validate the entered price
    if ($price < $min_price || $price > $max_price) {
        echo "<script>alert('Price is not in the current market range. Please enter a price between $min_price and $max_price.');</script>";
    } else {
        // Insert the trade crop data into the database
        $sql = "INSERT INTO trade_crops (crop_name, quantity, price) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("sii", $crop_name, $quantity, $price);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('Crop submitted successfully.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<title>Agriculture Portal - Trade Crops</title>

<!-- Bootstrap & base CSS -->
<link rel="stylesheet" href="css/bootstrap.css"/>
<link rel="stylesheet" href="css/style.css"/>
<script src="js/jquery.min.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Google Fonts -->
<link href='https://fonts.googleapis.com/css?family=Poppins:400,500,600,700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Merriweather:400,700italic' rel='stylesheet' type='text/css'>
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
    position: relative;
    padding-bottom: 80px;
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

.services {
    margin: 20px auto;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    max-width: 1200px;
    border-left: 5px solid #4CAF50;
}

.services h2 {
    color: #1a472a;
    text-align: center;
    margin-bottom: 25px;
    font-family: 'Merriweather', serif;
    font-weight: 700;
    border-bottom: 2px solid #e8f5ee;
    padding-bottom: 12px;
}

.btn-submit {
    background-color: #4CAF50;
    border-color: #1a472a;
    font-family: 'Oswald', sans-serif;
    font-weight: 600;
    padding: 10px 25px;
    border-radius: 6px;
    transition: all 0.3s;
    letter-spacing: 0.5px;
    color: white;
    border: none;
    cursor: pointer;
}

.btn-submit:hover {
    background-color: #3d8b40;
    border-color: #1a472a;
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.footer {
    background: #333;
    color: #fff;
    text-align: center;
    padding: 20px 0;
    margin-top: 40px;
    font-family: 'Oswald', sans-serif;
    position: absolute;
    bottom: 0;
    width: 100%;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

table th, table td {
    text-align: center;
    vertical-align: middle;
    font-family: 'Oswald', sans-serif;
    padding: 12px;
    border: 1px solid #dee2e6;
}

table th {
    background-color: #e8f5ee;
    color: #1a472a;
    font-weight: 600;
}

.form-control {
    border-radius: 6px;
    border: 1px solid #ced4da;
    padding: 10px 15px;
    font-family: 'Oswald', sans-serif;
    transition: all 0.3s;
    width: 100%;
}

.form-control:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 0.3rem rgba(76, 175, 80, 0.25);
}

select {
    border-radius: 6px;
    border: 1px solid #ced4da;
    padding: 10px 15px;
    font-family: 'Oswald', sans-serif;
    transition: all 0.3s;
    width: 100%;
    background-color: white;
}

select:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 0.3rem rgba(76, 175, 80, 0.25);
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
    
    table, thead, tbody, th, td, tr {
        display: block;
    }
    
    thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
    }
    
    tr {
        border: 1px solid #ccc;
        margin-bottom: 10px;
    }
    
    td {
        border: none;
        border-bottom: 1px solid #eee;
        position: relative;
        padding-left: 50%;
    }
    
    td:before {
        position: absolute;
        top: 12px;
        left: 6px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: bold;
    }
    
    td:nth-of-type(1):before { content: "#"; }
    td:nth-of-type(2):before { content: "Crop Name"; }
    td:nth-of-type(3):before { content: "Quantity (in KG)"; }
    td:nth-of-type(4):before { content: "Cost per KG (Rs.)"; }
    td:nth-of-type(5):before { content: "Action"; }
}

.save-indicator {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #4CAF50;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    display: none;
    z-index: 1000;
}
</style>
</head>

<body>
    <!-- header-section-starts -->
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
                            <li><a href="farmer_index.php"><i class="fas fa-home"></i> Home</a></li>
                            <li><a href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
                <h1><a href="tradecrops.php">Trade Crops</a></h1>
            </div>
        </div>
    </div>
    <!-- header-section-ends -->    

    <!-- Save Indicator -->
    <div class="save-indicator" id="saveIndicator">
        <i class="fas fa-check-circle"></i> Crop submitted successfully!
    </div>

    <!-- Main Content -->
    <div class="services">
        <h2>Sell Your Crops</h2>
        <form role="form" id="sellcrops" action="tradecrops.php" method="POST" enctype="multipart/form-data">                                            
            <table class="table table-striped table-responsive-md btn-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Crop Name</th>
                        <th>Quantity (in KG)</th>
                        <th>Cost per KG (Rs.)</th>
                        <th><center>Submit Crop</center></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>
                            <div class="form-group">
                                <select id="crops" name="crops" class="form-control" required>
                                    <option value="">Select Crop...</option>
                                    <option value="Arhar">Arhar (Pigeon Pea)</option>
                                    <option value="Bajra">Bajra (Pearl Millet)</option>  
                                    <option value="Barley">Barley</option>
                                    <option value="Cotton">Cotton</option>	
                                    <option value="Gram">Gram (Chickpea)</option>
                                    <option value="Jowar">Jowar (Sorghum)</option>
                                    <option value="Jute">Jute</option>
                                    <option value="Lentil">Lentil (Masoor)</option>
                                    <option value="Maize">Maize (Corn)</option>
                                    <option value="Moong">Moong (Mung Bean)</option>
                                    <option value="Ragi">Ragi (Finger Millet)</option>
                                    <option value="Rice">Rice</option>
                                    <option value="Soyabean">Soyabean</option>
                                    <option value="Urad">Urad (Black Gram)</option>
                                    <option value="Wheat">Wheat</option>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="number" name="trade_farmer_cropquantity" class="form-control" required min="1">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="number" name="trade_farmer_cost" id="trade_farmer_cost" class="form-control" required min="1">
                            </div>
                        </td>
                        <td>
                            <center>
                                <button type="submit" name="Crop_submit" value="Crop_submit" class="btn-submit">
                                    <i class="fas fa-upload"></i> Submit
                                </button>
                            </center>
                        </td>
                    </tr>
                </tbody>
            </table> 
        </form>
    </div>
    
    <!-- footer-section -->
    <div class="footer">
        <div class="container">
            <div class="copyright text-center">
                <p>&copy; Agriculture Portal. All Rights Reserved.</p>
                <p class="text-muted">Empowering farmers through technology</p>
            </div>
        </div>
    </div>
    <!-- footer-section -->
    
    <script type="text/javascript">
        $(document).ready(function() {
            $().UItoTop({ easingType: 'easeOutQuart' });
            
            // Show success message if crop was submitted
            <?php if(isset($_POST['Crop_submit']) && !empty($crop_name)): ?>
                document.getElementById('saveIndicator').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('saveIndicator').style.display = 'none';
                }, 3000);
            <?php endif; ?>
        });
    </script>
    <a href="#to-top" id="toTop" style="display: block;"> <span id="toTopHover" style="opacity: 1;"> </span></a>
    
    <!--Google translate--> 
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>