<?php
session_start();
$userlogin = $_SESSION['customer_login_user'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture_portal";

// Create Connection 
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$deletequery = "DELETE FROM cart";
$deletecart = mysqli_query($conn, $deletequery);

// Weather functionality - Only initialize if we're on the weather section
$city = isset($_POST['city']) ? $_POST['city'] : '';
$state = isset($_POST['state']) ? $_POST['state'] : '';
$forecast = [];
$api_error = '';

// Only process weather data if we're actually on the weather section
if (isset($_POST['city']) || (isset($_POST['state']) && $_POST['state'] != '')) {
    function getWeatherId($city_name) {
        // Simple mapping of common Indian cities
        $cityIdMap = [
            'Mumbai' => 1275339, 'Delhi' => 1273294, 'Bangalore' => 1277333,
            'Kolkata' => 1275004, 'Chennai' => 1264527, 'Hyderabad' => 1269843,
            'Pune' => 1259229, 'Ahmedabad' => 1279233, 'Jaipur' => 1269515,
            'Surat' => 1255364, 'Lucknow' => 1264728, 'Kanpur' => 1268003,
            'Nagpur' => 1262180, 'Patna' => 1260086, 'Indore' => 1269743,
            'Thane' => 1254655, 'Bhopal' => 1275841, 'Visakhapatnam' => 1253102,
            'Vadodara' => 1253573, 'Firozabad' => 1271885, 'Ludhiana' => 1264684,
            'Rajkot' => 1258847, 'Agra' => 1279259, 'Siliguri' => 1256312,
            'Nashik' => 1261731, 'Faridabad' => 1279393, 'Patiala' => 1260036,
            'Meerut' => 1262978, 'Kalyan' => 1268295, 'Jamshedpur' => 1269320,
            'Ghaziabad' => 1271308, 'Howrah' => 1257986, 'Jabalpur' => 1269633,
            'Gwalior' => 1270583, 'Vijayawada' => 1253184, 'Jodhpur' => 1269517,
            'Madurai' => 1264521, 'Raipur' => 1258899, 'Kota' => 1266049,
            'Chandigarh' => 1274746, 'Guwahati' => 1271439, 'Solapur' => 1255634,
            'Hubli' => 1269920, 'Dharwad' => 1273043, 'Bareilly' => 1277013,
            'Moradabad' => 1262801, 'Mysore' => 1277333, 'Gurgaon' => 1270642,
            'Aligarh' => 1278974, 'Jalandhar' => 1269388, 'Tiruchirappalli' => 1254361,
            'Bhubaneswar' => 1275817, 'Salem' => 1257629, 'Mira-Bhayandar' => 1273066,
            'Thiruvananthapuram' => 1254163, 'Bhiwandi' => 1275926, 'Amravati' => 1278710,
            'Noida' => 1261481, 'Jamshedpur' => 1269320, 'Bhilai' => 1275960,
            'Cuttack' => 1273795, 'Kochi' => 1273874, 'Nellore' => 1261551,
            'Bhavnagar' => 1270333, 'Dehradun' => 1273313, 'Durgapur' => 1272080,
            'Asansol' => 1278314, 'Rourkela' => 1258076, 'Nanded' => 1262049,
            'Kolhapur' => 1266285, 'Ajmer' => 1279159, 'Akola' => 1279105,
            'Gulbarga' => 1270752, 'Jamnagar' => 1269395, 'Ujjain' => 1253952,
            'Loni' => 1264729, 'Jhansi' => 1269006, 'Ulhasnagar' => 1253944,
            'Jammu' => 1269583, 'Mangalore' => 1263780, 'Erode' => 1272013,
            'Belgaum' => 1276583, 'Tirunelveli' => 1254363, 'Malegaon' => 1264154,
            'Gaya' => 1271439, 'Jalgaon' => 1269390, 'Udaipur' => 1253944,
            'Tirupur' => 1254343, 'Davanagere' => 1273313, 'Kozhikode' => 1265873,
            'Kurnool' => 1265539, 'Bokaro' => 1275358, 'Bellary' => 1276533,
            'Patiala' => 1260036, 'Agartala' => 1278293, 'Bhagalpur' => 1276375,
            'Muzaffarnagar' => 1262330, 'Latur' => 1264976, 'Dhule' => 1272665,
            'Rohtak' => 1258086, 'Korba' => 1266135, 'Bhilwara' => 1275947,
            'Muzaffarpur' => 1262321, 'Ahmednagar' => 1279237, 'Mathura' => 1260341,
            'Kollam' => 1265871, 'Anantapur' => 1278622, 'Bilaspur' => 1275599,
            'Sambalpur' => 1257619, 'Shahjahanpur' => 1256728, 'Satara' => 1257007,
            'Bijapur' => 1275716, 'Rampur' => 1258526, 'Shivamogga' => 1256437,
            'Chandrapur' => 1274743, 'Junagadh' => 1268782, 'Thrissur' => 1254330,
            'Alwar' => 1278979, 'Bardhaman' => 1277126, 'Kulti' => 1265660,
            'Kakinada' => 1268561, 'Nizamabad' => 1261258, 'Parbhani' => 1260393,
            'Tumkur' => 1253992, 'Hisar' => 1270077, 'Ozhukarai' => 1263989,
            'Bihar Sharif' => 1275715, 'Panipat' => 1260546, 'Darbhanga' => 1273465,
            'Dewas' => 1273006, 'Ichalkaranji' => 1269805, 'Tirupati' => 1254337,
            'Karnal' => 1267771, 'Bathinda' => 1276715, 'Jalna' => 1269392,
            'Barasat' => 1277136, 'Purnia' => 1259154, 'Satna' => 1256975,
            'Mau' => 1263214, 'Sonipat' => 1255711, 'Farrukhabad' => 1271975,
            'Sagar' => 1257809, 'Imphal' => 1269743, 'Ratlam' => 1258238,
            'Hapur' => 1270393, 'Arrah' => 1278370, 'Karimnagar' => 1264735,
            'Etawah' => 1271989, 'Ambernath' => 1278946, 'Bharatpur' => 1276138,
            'Begusarai' => 1276588, 'Gandhidham' => 1271715, 'Puducherry' => 1259424,
            'Sikar' => 1256299, 'Thoothukudi' => 1254333, 'Rewa' => 1258229,
            'Mirzapur' => 1263012, 'Raichur' => 1259004, 'Pali' => 1260779,
            'Ramagundam' => 1258795, 'Haridwar' => 1270407, 'Katihar' => 1267506,
            'Nagercoil' => 1262180, 'Bulandshahr' => 1275120, 'Thanjavur' => 1254661,
            'Murwara' => 1262332, 'Sambhal' => 1257491, 'Singrauli' => 1258128,
            'Nadiad' => 1262233, 'Naihati' => 1262189, 'Yamunanagar' => 1252743,
            'Bidar' => 1275795, 'Munger' => 1262505, 'Panchkula' => 1260476,
            'Burhanpur' => 1275103, 'Kharagpur' => 1267016, 'Dindigul' => 1252648,
            'Gandhinagar' => 1278861, 'Hospet' => 1269978, 'Malda' => 1264155,
            'Ongole' => 1261050, 'Deoghar' => 1273140, 'Chapra' => 1274553,
            'Haldia' => 1274540, 'Khandwa' => 1266794, 'Nandyal' => 1262049,
            'Morena' => 1262802, 'Amroha' => 1278715, 'Anand' => 1278690,
            'Bhind' => 1275762, 'Bhiwani' => 1275842, 'Berhampore' => 1276531,
            'Ambala' => 1278946, 'Morbi' => 1272532, 'Fatehpur' => 1271881,
            'Raebareli' => 1259009, 'Chittoor' => 1273974, 'Bhusawal' => 1275717,
            'Orai' => 1261029, 'Bahraich' => 1277780, 'Vellore' => 1253208,
            'Mandsaur' => 1263795, 'Budaun' => 1275921, 'Hazaribagh' => 1270151,
            'Hindupur' => 1270036, 'Nagaon' => 1262189, 'Hoshiarpur' => 1269935,
            'Beawar' => 1276634, 'Saharanpur' => 1257809, 'Vidisha' => 1253286,
            'Sasaram' => 1257082, 'Giridih' => 1271179, 'Bhimavaram' => 1275761,
            'Adoni' => 1279306, 'Basti' => 1276712, 'Chandausi' => 1274746,
            'Aurangabad' => 1278140, 'Jalpaiguri' => 1269380, 'Guna' => 1270668,
            'Dibrugarh' => 1272643, 'Jorhat' => 1268773, 'Baripada' => 1276959,
            'Haldwani' => 1270482, 'Tambaram' => 1255073, 'Abohar' => 1279445,
            'Port Blair' => 1259385, 'Alappuzha' => 1278980, 'Kumbakonam' => 1265670,
            'Banswara' => 1277264, 'Chikkamagaluru' => 1274406, 'Palakkad' => 1260690,
            'Bhuj' => 1275841, 'Bhadravati' => 1276320, 'Shillong' => 1255634,
            'Karaikudi' => 1267853, 'Kishangarh' => 1268467, 'Bikaner' => 1276680,
            'Dhamtari' => 1273006, 'Tinsukia' => 1254365, 'Guntakal' => 1270667,
            'Srikakulam' => 1255634, 'Motihari' => 1262776, 'Dimapur' => 1272532,
            'Dharmavaram' => 1273006, 'Medininagar' => 1263220, 'Gudivada' => 1270770,
            'Phagwara' => 1259887, 'Pudukkottai' => 1259418, 'Hosur' => 1269983,
            'Suryapet' => 1255364, 'Miryalaguda' => 1263012, 'Anantnag' => 1258553,
            'Tadpatri' => 1255344, 'Karaikal' => 1267862
        ];
        
        return $cityIdMap[$city_name] ?? 1275339; // Default to Mumbai if not found
    }

    // Get weather ID
    $city_weather_id = getWeatherId($city);

    date_default_timezone_set("Asia/Kolkata");
    $apiKey = "c48efd9784e91b7d795257102cc78118";

    // Use direct city name API call
    $googleApiUrl = "http://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($city) . ",IN&lang=en&units=metric&APPID=" . $apiKey;

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
    if ($http_code != 200 || !$data || isset($data->cod) && $data->cod != 200) {
        $api_error = 'Unable to fetch weather data. Please try again later.';
        $forecast = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriculture Portal</title>
    
    <!-- Bootstrap & base CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2e7d32;
            --primary-light: #4caf50;
            --primary-dark: #1b5e20;
            --secondary: #ffa000;
            --light-bg: #f8f9fa;
            --dark-text: #333;
            --light-text: #6c757d;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7f9;
            color: var(--dark-text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Roboto Slab', serif;
            font-weight: 600;
        }
        
        /* Header Styles */
        .main-header {
            background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), 
                        url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80') no-repeat center center;
            background-size: cover;
            color: var(--white);
            padding: 1.5rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .brand {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .brand span {
            color: var(--secondary);
        }
        
        /* Navigation */
        .main-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-links li {
            margin-left: 1.5rem;
        }
        
        .nav-links a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        /* Main Content */
        .main-container {
            padding: 2rem 0;
            flex: 1;
        }
        
        .section-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .section-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 1.25rem 1.5rem;
            font-size: 1.35rem;
        }
        
        .section-body {
            padding: 1.5rem;
        }
        
        /* Feature Cards */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .feature-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .feature-card i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            color: var(--primary-dark);
            margin-bottom: 0.75rem;
        }
        
        .feature-card p {
            color: var(--light-text);
            line-height: 1.5;
        }
        
        /* Buy Crops Block */
        .buy-crops-block, .weather-reports-block {
            background: var(--primary) !important;
            color: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
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
            cursor: pointer;
        }
        
        .buy-crops-block:hover, .weather-reports-block:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            text-decoration: none;
            background: var(--primary-dark) !important;
        }
        
        .buy-crops-block i, .weather-reports-block i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--white);
        }
        
        .buy-crops-block h4, .weather-reports-block h4 {
            color: var(--white);
            margin-bottom: 0.5rem;
        }
        
        .header-grids {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 40px;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        
        /* Section Title */
        .section-title {
            text-align: center;
            color: var(--primary-dark);
            margin-bottom: 2rem;
            font-size: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--primary);
        }
        
        .welcome-message {
            text-align: center;
            margin: 2rem 0;
            padding: 0 1rem;
        }
        
        .welcome-message h2 {
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }
        
        .welcome-message p {
            color: var(--light-text);
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        
        /* Footer */
        .main-footer {
            background-color: var(--primary-dark);
            color: var(--white);
            padding: 1.5rem 0;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                margin-top: 1rem;
                width: 100%;
                flex-direction: column;
            }
            
            .nav-links li {
                margin: 0.5rem 0;
                margin-left: 0;
            }
            
            .brand {
                font-size: 2rem;
            }
            
            .header-grids {
                gap: 20px;
            }
        }
        
        /* Google Translate */
        .google-translate {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }
        
        /* Custom utilities */
        .text-success {
            color: var(--primary) !important;
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
            color: var(--primary-dark);
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
            color: var(--primary-dark);
            font-size: 1.2em;
            border-bottom: 2px solid var(--primary);
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
            color: var(--primary-dark);
            font-style: italic;
        }
        
        .location-title {
            text-align: center;
            color: var(--primary-dark);
            margin: 20px 0;
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
            color: var(--primary-dark);
            margin-bottom: 10px;
        }
        
        .weather-temp {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--primary);
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
            color: var(--primary-dark);
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
        
        .btn-success {
            background-color: var(--primary);
            border-color: var(--primary-dark);
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 6px;
            transition: all 0.3s;
            letter-spacing: 0.5px;
        }
        
        .btn-success:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .form-control {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.3rem rgba(76, 175, 80, 0.25);
        }
        
        .weather-section {
            display: none;
        }
        
        .active-section {
            display: block;
        }
        
        .section-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .section-tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            font-weight: 600;
            color: var(--light-text);
        }
        
        .section-tab.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }
        
        .section-tab:hover {
            color: var(--primary-dark);
        }
        
        /* Initially hide weather sections */
        .weather-reports-section {
            display: none;
        }
        
        .buy-crops-section {
            display: block;
        }
        
        /* Active section styling */
        .section-active {
            display: block;
        }
        
        .section-hidden {
            display: none;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="google-translate">
                <div id="google_translate_element"></div>
            </div>
            
            <div class="main-nav">
                <a href="customer_index.php" class="brand">Agri<span>Market</span></a>
                
                <ul class="nav-links">
                    <li><a href="customer_index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            
            <!--Main Agriculture Components Starts-->
            <div class="header-grids text-center">
                <!-- Buy Crops Block -->
                <a href="buy_crops.php" class="buy-crops-block">
                    <i class="fas fa-shopping-cart"></i>
                    <h4>Buy Crops</h4>
                    <p>Direct from farmers</p>
                </a>
                
                <!-- Weather Reports Block -->
                <a href="#" class="weather-reports-block" onclick="showWeatherSection()">
                    <i class="fas fa-cloud-sun"></i>
                    <h4>Weather Reports</h4>
                    <p>Check forecast</p>
                </a>
            </div>
            <!--Main Agriculture Components Ends-->
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            <!-- Section Tabs -->
            <div class="section-tabs">
                <div class="section-tab active" onclick="showBuyCropsSection()">Marketplace</div>
                <div class="section-tab" onclick="showWeatherSection()">Weather Forecast</div>
            </div>
            
            <!-- Welcome Message -->
            <div class="welcome-message buy-crops-section section-active">
                <h2>Welcome to Agriculture Portal</h2>
                <p>Your one-stop solution for agricultural marketplace. Empowering farmers with technology.</p>
            </div>
            
            <!-- Weather Form Section -->
            <div class="section-card weather-reports-section section-hidden">
                <div class="section-header">
                    Check Weather Forecast
                </div>
                <div class="section-body">
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
                                    <?php if ($city): ?>
                                        <option value="<?php echo $city; ?>" selected><?php echo $city; ?></option>
                                    <?php endif; ?>
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
            </div>

            <!-- Weather Results Section -->
            <?php if ((isset($_POST['city']) || $city != "") && !$api_error && isset($_POST['state'])): ?>
            <div class="section-card weather-reports-section section-active">
                <div class="section-header">
                    Weather Forecast Results
                </div>
                <div class="section-body">
                    <?php if (!empty($forecast)): ?>
                        <h3 class="location-title">5-Day Weather Forecast for <?php echo $data->city->name ?? $city; ?></h3>
                        
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
                            if ($current_date != '') {
                                echo '</div></div>'; // Close last day container
                            }
                        ?>
                    <?php else: ?>
                        <div class="no-data">Weather data not available for <?php echo $city; ?>. Please try another location.</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php elseif ($api_error && isset($_POST['state'])): ?>
            <div class="section-card weather-reports-section section-active">
                <div class="section-body">
                    <div class="no-data"><?php echo $api_error; ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Features Section -->
            <div class="section-card buy-crops-section section-active">
                <div class="section-header">
                    Our Services
                </div>
                <div class="section-body">
                    <div class="feature-grid">
                        <div class="feature-card">
                            <i class="fas fa-seedling"></i>
                            <h3>Crop Marketplace</h3>
                            <p>Buy and sell agricultural products directly with farmers and suppliers.</p>
                        </div>
                        
                        <div class="feature-card">
                            <i class="fas fa-tractor"></i>
                            <h3>Farming Resources</h3>
                            <p>Access to modern farming techniques, equipment information, and best practices.</p>
                        </div>
                        
                        <div class="feature-card">
                            <i class="fas fa-users"></i>
                            <h3>Community</h3>
                            <p>Connect with other farmers and agricultural experts to share knowledge.</p>
                        </div>
                        
                        <div class="feature-card">
                            <i class="fas fa-chart-line"></i>
                            <h3>Market Insights</h3>
                            <p>Get the latest information on crop prices and market trends.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Agriculture Portal. All Rights Reserved.</p>
            <p class="mb-0">Empowering farmers through technology</p>
        </div>
    </footer>

    <!-- Google translate -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage:'en',
            includedLanguages:'bn,en,gu,hi,kn,mr,ta,te'
        },'google_translate_element');
    }
    
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
    
    // Tab switching functionality
    function showWeatherSection() {
        // Update active tab
        document.querySelectorAll('.section-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector('.section-tab[onclick="showWeatherSection()"]').classList.add('active');
        
        // Hide buy crops sections
        document.querySelectorAll('.buy-crops-section').forEach(section => {
            section.classList.remove('section-active');
            section.classList.add('section-hidden');
        });
        
        // Show weather sections
        document.querySelectorAll('.weather-reports-section').forEach(section => {
            section.classList.remove('section-hidden');
            section.classList.add('section-active');
        });
        
        // Scroll to top
        window.scrollTo(0, 0);
    }
    
    function showBuyCropsSection() {
        // Update active tab
        document.querySelectorAll('.section-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector('.section-tab[onclick="showBuyCropsSection()"]').classList.add('active');
        
        // Show buy crops sections
        document.querySelectorAll('.buy-crops-section').forEach(section => {
            section.classList.remove('section-hidden');
            section.classList.add('section-active');
        });
        
        // Hide weather sections
        document.querySelectorAll('.weather-reports-section').forEach(section => {
            section.classList.remove('section-active');
            section.classList.add('section-hidden');
        });
        
        // Scroll to top
        window.scrollTo(0, 0);
    }
    
    // Initialize cities based on current state selection
    document.addEventListener('DOMContentLoaded', function() {
        const currentState = document.getElementById('state');
        if (currentState && currentState.value) {
            populateCities();
        }
        
        // Show weather section if we have weather data to display
        <?php if ((isset($_POST['city']) || $city != "") && !$api_error && isset($_POST['state'])): ?>
            showWeatherSection();
        <?php endif; ?>
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>