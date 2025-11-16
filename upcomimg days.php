<?php
// Connection Establishment
session_start();
$userlogin = $_SESSION['farmer_login_user'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture_portal";

// Create Connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function encrypt($message, $encryption_key) {
    $key = hex2bin($encryption_key);
    $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
    $nonce = openssl_random_pseudo_bytes($nonceSize);
    $ciphertext = openssl_encrypt(
        $message,
        'aes-256-ctr',
        $key,
        OPENSSL_RAW_DATA,
        $nonce
    );
    return base64_encode($nonce . $ciphertext);
}

function decrypt($message, $encryption_key) {
    $key = hex2bin($encryption_key);
    $message = base64_decode($message);
    $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
    $nonce = mb_substr($message, 0, $nonceSize, '8bit');
    $ciphertext = mb_substr($message, $nonceSize, null, '8bit');
    $plaintext = openssl_decrypt(
        $ciphertext,
        'aes-256-ctr',
        $key,
        OPENSSL_RAW_DATA,
        $nonce
    );
    return $plaintext;
}

$pk = "1a851867be761f7725cacf7467a184d3";

$display_district = "Select F_District from farmerlogin WHERE email='$userlogin'";
$display_district_result = mysqli_query($conn, $display_district);
$display_district_name = mysqli_fetch_array($display_district_result);
$District_name_farmer = decrypt($display_district_name[0], $pk);

$city = isset($_POST['city']) ? $_POST['city'] : $District_name_farmer;
$state = isset($_POST['state']) ? $_POST['state'] : '';

// Enhanced city to weather ID mapping with better district-to-city conversion
function getWeatherId($district_name, $state_name = '') {
    // Map district names to their corresponding main cities for OpenWeatherMap
    $districtToCityMap = [
        // Maharashtra
        'Mumbai City' => 'Mumbai', 'Mumbai Suburban' => 'Mumbai', 'Thane' => 'Thane',
        'Palghar' => 'Palghar', 'Raigad' => 'Alibag', 'Nandurbar' => 'Nandurbar',
        'Jalgoan' => 'Jalgaon', 'Dhule' => 'Dhule', 'Ratnagiri' => 'Ratnagiri',
        'Sindhudurg' => 'Malvan', 'Kolhapur' => 'Kolhapur', 'Satara' => 'Satara',
        'Sangli' => 'Sangli', 'Solapur' => 'Solapur', 'Pune' => 'Pune',
        'Nashik' => 'Nashik', 'Ahmednagar' => 'Ahmednagar', 'Aurangabad' => 'Aurangabad',
        'Jalna' => 'Jalna', 'Beed' => 'Beed', 'Osmanabad' => 'Osmanabad',
        'Latur' => 'Latur', 'Parbhani' => 'Parbhani', 'Hingoli' => 'Hingoli',
        'Nanded' => 'Nanded', 'Yavatmal' => 'Yavatmal', 'Washim' => 'Washim',
        'Akola' => 'Akola', 'Amravati' => 'Amravati', 'Buldhana' => 'Buldhana',
        'Nagpur' => 'Nagpur', 'Wardha' => 'Wardha', 'Chandrapur' => 'Chandrapur',
        'Gadchiroli' => 'Gadchiroli', 'Bhandara' => 'Bhandara', 'Gondia' => 'Gondia',
        
        // Karnataka
        'Bangalore Urban' => 'Bangalore', 'Bangalore Rural' => 'Bangalore',
        'Bagalkot' => 'Bagalkot', 'Belagavi' => 'Belgaum', 'Ballari' => 'Bellary',
        'Bidar' => 'Bidar', 'Chamarajanagar' => 'Chamarajanagar', 
        'Chikkamagaluru' => 'Chikmagalur', 'Chikkaballapura' => 'Chikkaballapur',
        'Chitradurga' => 'Chitradurga', 'Dakshina Kannada' => 'Mangalore',
        'Davanagere' => 'Davangere', 'Dharwad' => 'Dharwad', 'Gadag' => 'Gadag',
        'Hassan' => 'Hassan', 'Haveri' => 'Haveri', 'Kalaburagi' => 'Gulbarga',
        'Kodagu' => 'Madikeri', 'Kolar' => 'Kolar', 'Koppal' => 'Koppal',
        'Mandya' => 'Mandya', 'Mysuru' => 'Mysore', 'Raichur' => 'Raichur',
        'Ramanagara' => 'Ramanagara', 'Shivamogga' => 'Shimoga', 'Tumakuru' => 'Tumkur',
        'Udupi' => 'Udupi', 'Uttara Kannada' => 'Karwar', 'Vijayapura' => 'Bijapur',
        'Yadgir' => 'Yadgir',
        
        // Add mappings for other states as needed...
    ];
    
    // Use mapped city name or fallback to district name
    $city_name = $districtToCityMap[$district_name] ?? $district_name;
    
    // Load city data from JSON file
    $city_data = file_get_contents('city.list.json');
    $cities = json_decode($city_data);
    
    $weather_id = 0;
    
    // First try exact match
    foreach ($cities as $city_obj) {
        if (strcasecmp(trim($city_obj->name), trim($city_name)) === 0 && $city_obj->country == "IN") {
            $weather_id = $city_obj->id;
            break;
        }
    }
    
    // If exact match not found, try partial match
    if ($weather_id <= 0) {
        foreach ($cities as $city_obj) {
            if (stripos($city_obj->name, trim($city_name)) !== false && $city_obj->country == "IN") {
                $weather_id = $city_obj->id;
                break;
            }
        }
    }
    
    // If still not found, use coordinates-based API call as fallback
    if ($weather_id <= 0) {
        $weather_id = getWeatherIdByCoordinates($city_name, $state_name);
    }
    
    return $weather_id > 0 ? $weather_id : 1275339; // Default to Mumbai
}

// Fallback function using coordinates
function getWeatherIdByCoordinates($city_name, $state_name) {
    $api_key = "c48efd9784e91b7d795257102cc78118";
    
    // Use geocoding API to get coordinates
    $geo_url = "http://api.openweathermap.org/geo/1.0/direct?q=" . urlencode($city_name) . ",IN&limit=1&appid=" . $api_key;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $geo_url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $geo_data = json_decode($response);
    
    if (!empty($geo_data) && isset($geo_data[0]->lat)) {
        // Use coordinates to get weather data directly
        return $geo_data[0]->lat . ',' . $geo_data[0]->lon;
    }
    
    return 0;
}

// Get weather ID
$city_weather_id = getWeatherId($city, $state);

date_default_timezone_set("Asia/Kolkata");
$apiKey = "c48efd9784e91b7d795257102cc78118";

// Check if we have coordinates or city ID
if (strpos($city_weather_id, ',') !== false) {
    // Use coordinates-based API call
    $googleApiUrl = "http://api.openweathermap.org/data/2.5/forecast?lat=" . explode(',', $city_weather_id)[0] . 
                   "&lon=" . explode(',', $city_weather_id)[1] . "&lang=en&units=metric&APPID=" . $apiKey;
} else {
    // Use city ID-based API call
    $googleApiUrl = "http://api.openweathermap.org/data/2.5/forecast?id=" . $city_weather_id . "&lang=en&units=metric&APPID=" . $apiKey;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response);
$forecast = $data->list ?? [];

// Error handling for API response
$api_error = '';
if ($http_code != 200 || !$data || isset($data->cod) && $data->cod != 200) {
    $api_error = 'Unable to fetch weather data. Please try again later.';
    $forecast = [];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Weather Forecast | Agriculture Portal</title>

<!-- Bootstrap & base CSS -->
<link rel="stylesheet" href="css/bootstrap.css"/>
<link rel="stylesheet" href="css/style.css"/>
<script src="js/jquery.min.js"></script>

<!-- Trade-Crops header styling -->
<link rel="stylesheet" href="css/myprofilecss.css">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
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
}

.services {
    margin: 20px auto;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    max-width: 900px;
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

.btn-success {
    background-color: #4CAF50;
    border-color: #1a472a;
    font-family: 'Oswald', sans-serif;
    font-weight: 600;
    padding: 10px 25px;
    border-radius: 6px;
    transition: all 0.3s;
    letter-spacing: 0.5px;
}

.btn-success:hover {
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
    margin-top: auto;
    font-family: 'Oswald', sans-serif;
}

.no-data {
    color: #e74c3c;
    font-weight: bold;
    text-align: center;
    margin-top: 15px;
    padding: 20px;
    background: #ffeaea;
    border-radius: 8px;
    border: 1px solid #f5c6cb;
}

table {
    width: 100%;
}

table th, table td {
    text-align: center;
    vertical-align: middle;
    font-family: 'Oswald', sans-serif;
    padding: 12px;
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
}

.form-control:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 0.3rem rgba(76, 175, 80, 0.25);
}

.results-container {
    background: #f1f8f5;
    padding: 25px;
    border-radius: 10px;
    margin: 20px auto;
    border-left: 4px solid #4CAF50;
    max-width: 900px;
    min-height: auto;
    height: auto;
    overflow: visible;
}

.results-container h3 {
    font-family: 'Merriweather', serif;
    color: #1a472a;
    font-size: 1.5rem;
    margin-bottom: 15px;
    text-align: center;
}

pre {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    font-family: 'Oswald', sans-serif;
    font-size: 14px;
    line-height: 1.5;
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

/* Weather specific styles */
.weather-icon {
    vertical-align: middle;
    margin-right: 10px;
    width: 50px;
    height: 50px;
}

.weather-forecast {
    color: #212121;
    font-size: 1.2em;
    font-weight: bold;
    margin: 10px 0px;
    display: inline-block;
    width: 200px;
    text-align: center;
    color: #1a472a;
}

.time {
    line-height: 25px;
    color: #333;
    font-weight: 500;
}

.forecast-day {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-around;
    margin-bottom: 20px;
    border: 1px solid #E0E0E0;
    padding: 15px;
    border-radius: 8px;
    background-color: rgba(232, 245, 238, 0.5);
    height: auto;
    min-height: auto;
}

.day-separator {
    height: 40px;
    width: 100%;
    text-align: center;
    margin: 15px 0;
    font-weight: bold;
    color: #1a472a;
    font-size: 1.2em;
    border-bottom: 2px solid #4CAF50;
}

.forecast-item {
    text-align: center;
    padding: 10px;
    margin: 5px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    min-width: 150px;
    flex: 1;
    max-width: 200px;
    height: auto;
}

.temp-min {
    color: #4a90e2;
    font-weight: bold;
}

.temp-max {
    color: #e74c3c;
    font-weight: bold;
}

.weather-desc {
    color: #1a472a;
    font-style: italic;
}

.location-title {
    text-align: center;
    color: #1a472a;
    margin: 20px 0;
    font-family: 'Merriweather', serif;
}

.weather-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
    height: auto;
    min-height: auto;
}

.weather-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s;
    height: 250px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.weather-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}

.weather-time {
    font-weight: bold;
    color: #1a472a;
    margin-bottom: 10px;
}

.weather-temp {
    font-size: 1.5em;
    font-weight: bold;
    color: #4CAF50;
    margin: 10px 0;
}

.weather-details {
    font-size: 0.9em;
    color: #666;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #1a472a;
}

.main-content {
    flex: 1;
    padding-bottom: 20px;
}

.day-forecast-container {
    margin-bottom: 30px;
    height: auto;
}

.day-forecast-container:last-child {
    margin-bottom: 0;
}

.weather-card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
    height: auto;
}

.loading {
    text-align: center;
    padding: 20px;
    color: #1a472a;
}

.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #4CAF50;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 2s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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
            <h1><a href="weather_forecast.php">Weather Forecast</a></h1>
        </div>
    </div>
</div>
<!-- ================= HEADER END ================= -->

<div class="main-content">
    <!-- ================= WEATHER FORM SECTION ================= -->
    <div class="services">
        <h2>Check Weather Forecast</h2>
        <form method="post" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="state">Select State:</label>
                    <select id="state" name="state" onchange="populateCities()" class="form-control" required>
                        <option value="">Select State</option>
                        <option value="Maharashtra" <?php echo ($state == 'Maharashtra') ? 'selected' : ''; ?>>Maharashtra</option>
                        <option value="Karnataka" <?php echo ($state == 'Karnataka') ? 'selected' : ''; ?>>Karnataka</option>
                        <option value="Gujarat" <?php echo ($state == 'Gujarat') ? 'selected' : ''; ?>>Gujarat</option>
                        <option value="Uttar Pradesh" <?php echo ($state == 'Uttar Pradesh') ? 'selected' : ''; ?>>Uttar Pradesh</option>
                        <option value="Bihar" <?php echo ($state == 'Bihar') ? 'selected' : ''; ?>>Bihar</option>
                        <option value="Tamil Nadu" <?php echo ($state == 'Tamil Nadu') ? 'selected' : ''; ?>>Tamil Nadu</option>
                        <option value="Kerala" <?php echo ($state == 'Kerala') ? 'selected' : ''; ?>>Kerala</option>
                        <option value="Punjab" <?php echo ($state == 'Punjab') ? 'selected' : ''; ?>>Punjab</option>
                        <option value="Rajasthan" <?php echo ($state == 'Rajasthan') ? 'selected' : ''; ?>>Rajasthan</option>
                        <option value="West Bengal" <?php echo ($state == 'West Bengal') ? 'selected' : ''; ?>>West Bengal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="city">Select City/District:</label>
                    <select id="city" name="city" class="form-control" required>
                        <option value="">Select City/District</option>
                        <option value="<?php echo $District_name_farmer; ?>" selected><?php echo $District_name_farmer; ?></option>
                    </select>
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-success" style="height: 44px;">
                        <i class="fas fa-cloud-sun"></i> Get Weather
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ================= WEATHER RESULTS SECTION ================= -->
    <?php if ((isset($_POST['city']) || $city != "") && !$api_error): ?>
    <div class="results-container">
        <?php if (!empty($forecast)): ?>
            <h3>5-Day Weather Forecast for <?php echo $data->city->name ?? $city; ?></h3>
            
            <?php 
            $current_date = '';
            $day_count = 0;
            
            foreach($forecast as $f) { 
                $date = date('Y-m-d', $f->dt);
                if ($current_date != $date) {
                    if ($current_date != '') {
                        echo '</div></div>'; // Close previous day container
                    }
                    
                    $current_date = $date;
                    $day_count++;
                    
                    // Only show 5 days
                    if ($day_count > 5) break;
                    
                    echo '<div class="day-forecast-container">';
                    echo '<div class="day-separator">' . date('l, F j', $f->dt) . '</div>';
                    echo '<div class="weather-card-container">';
                }
            ?>
                    <div class="weather-card">
                        <div class="weather-time"><?php echo date('H:i', $f->dt); ?></div>
                        <img src="http://openweathermap.org/img/wn/<?php echo $f->weather[0]->icon; ?>.png" class="weather-icon" alt="<?php echo $f->weather[0]->description; ?>" />
                        <div class="weather-temp"><?php echo round($f->main->temp); ?>&deg;C</div>
                        <div class="weather-desc"><?php echo ucfirst($f->weather[0]->description); ?></div>
                        <div class="weather-details">
                            <div><strong>High:</strong> <span class="temp-max"><?php echo round($f->main->temp_max); ?>&deg;C</span></div>
                            <div><strong>Low:</strong> <span class="temp-min"><?php echo round($f->main->temp_min); ?>&deg;C</span></div>
                            <div><strong>Humidity:</strong> <?php echo $f->main->humidity; ?>%</div>
                            <div><strong>Wind:</strong> <?php echo round($f->wind->speed ?? 0); ?> m/s</div>
                        </div>
                    </div>
            <?php 
                } 
                echo '</div></div>'; // Close last day container
            ?>
        <?php else: ?>
            <div class="no-data">Weather data not available for <?php echo $city; ?>. Please try another location.</div>
        <?php endif; ?>
    </div>
    <?php elseif ($api_error): ?>
    <div class="results-container">
        <div class="no-data"><?php echo $api_error; ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- ================= FOOTER ================= -->
<div class="footer">
    <p>&copy; Agriculture Portal. All Rights Reserved.</p>
    <p class="text-muted">Empowering farmers through technology</p>
</div>

<!-- Google translate -->
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<script>
// Enhanced function to populate cities based on state selection
function populateCities() {
    const state = document.getElementById('state').value;
    const citySelect = document.getElementById('city');
    
    // Clear existing options except the first one
    while(citySelect.options.length > 1) {
        citySelect.remove(1);
    }
    
    if (state) {
        const cities = {
            'Maharashtra': [
                'Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Aurangabad', 'Solapur', 
                'Kolhapur', 'Amravati', 'Nanded', 'Sangli', 'Jalgaon', 'Akola', 'Latur',
                'Ahmednagar', 'Chandrapur', 'Parbhani', 'Ichalkaranji', 'Jalna', 'Bhusawal',
                'Panvel', 'Ulhasnagar', 'Malegaon', 'Mira-Bhayandar', 'Bhiwandi', 'Satara',
                'Barshi', 'Yavatmal', 'Ambarnath', 'Bid', 'Gondia', 'Wardha', 'Hinganghat','Nandurbar'
            ],
            'Karnataka': [
                'Bangalore', 'Mysore', 'Hubli', 'Dharwad', 'Belgaum', 'Gulbarga', 'Mangalore',
                'Bellary', 'Bijapur', 'Shimoga', 'Tumkur', 'Raichur', 'Bidar', 'Hospet',
                'Gadag', 'Robertsonpet', 'Hassan', 'Udupi', 'Bhadravati', 'Chitradurga',
                'Kolar', 'Gangawati', 'Ranibennur', 'Bagalkot', 'Sirsi', 'Chikmagalur'
            ],
            'Gujarat': [
                'Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar',
                'Junagadh', 'Gandhinagar', 'Gandhidham', 'Anand', 'Navsari', 'Morbi',
                'Nadiad', 'Surendranagar', 'Bharuch', 'Mehsana', 'Bhuj', 'Porbandar',
                'Palanpur', 'Valsad', 'Vapi', 'Gondal', 'Veraval', 'Godhra', 'Patan'
            ],
            'Uttar Pradesh': [
                'Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Varanasi', 'Meerut', 'Allahabad',
                'Bareilly', 'Aligarh', 'Moradabad', 'Saharanpur', 'Gorakhpur', 'Noida',
                'Firozabad', 'Jhansi', 'Muzaffarnagar', 'Mathura', 'Budaun', 'Rampur',
                'Shahjahanpur', 'Farrukhabad', 'Maunathbhanjan', 'Hapur', 'Etawah', 'Sambhal'
            ],
            'Bihar': [
                'Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Purnia', 'Darbhanga',
                'Bihar Sharif', 'Arrah', 'Begusarai', 'Katihar', 'Munger', 'Chapra',
                'Danapur', 'Saharsa', 'Hajipur', 'Dehri', 'Bettiah', 'Siwan', 'Motihari'
            ],
            'Tamil Nadu': [
                'Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli',
                'Tiruppur', 'Erode', 'Vellore', 'Thoothukkudi', 'Dindigul', 'Thanjavur',
                'Hosur', 'Nagercoil', 'Kanchipuram', 'Kumarapalayam', 'Karaikkudi', 'Neyveli'
            ],
            'Kerala': [
                'Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Kollam', 'Thrissur', 'Alappuzha',
                'Palakkad', 'Kannur', 'Kottayam', 'Manjeri', 'Koyilandy', 'Thalassery'
            ],
            'Punjab': [
                'Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Pathankot',
                'Hoshiarpur', 'Batala', 'Moga', 'Abohar', 'Khanna', 'Phagwara', 'Muktsar'
            ],
            'Rajasthan': [
                'Jaipur', 'Jodhpur', 'Kota', 'Bikaner', 'Ajmer', 'Udaipur', 'Bhilwara',
                'Alwar', 'Bharatpur', 'Sri Ganganagar', 'Sikar', 'Pali', 'Tonk', 'Hanumangarh'
            ],
            'West Bengal': [
                'Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri', 'Bardhaman',
                'Malda', 'Bahrampur', 'Habra', 'Kharagpur', 'Shantipur', 'Baranagar',
                'Bally', 'Bangaon', 'Kanchrapara', 'Chandannagar', 'Krishnanagar'
            ]
        };
        
        if (cities[state]) {
            cities[state].forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.text = city;
                citySelect.add(option);
            });
        }
    }
}

// Initialize cities based on current state selection
document.addEventListener('DOMContentLoaded', function() {
    const currentState = document.getElementById('state').value;
    if (currentState) {
        populateCities();
    }
});
</script>

</body>
</html>