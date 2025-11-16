<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture_portal";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if farmer is logged in
if (!isset($_SESSION['farmer_login_user'])) {
    header("Location: farmer_login.php");
    exit();
}

$logged_in_user = $_SESSION['farmer_login_user'];
$success_message = '';
$error_message = '';

// Create farmer_profiles table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS farmer_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    gender ENUM('male', 'female') DEFAULT 'male',
    birthday DATE,
    state VARCHAR(100),
    district VARCHAR(100),
    address TEXT,
    aadhaar_number VARCHAR(20),
    pan_number VARCHAR(20),
    land_area DECIMAL(10,2),
    crops JSON,
    irrigation_methods JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $create_table_sql)) {
    $error_message = "Error creating table: " . mysqli_error($conn);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $aadhaar = mysqli_real_escape_string($conn, $_POST['aadhaar']);
    $pan = mysqli_real_escape_string($conn, $_POST['pan']);
    $land_area = mysqli_real_escape_string($conn, $_POST['landArea']);
    
    // Handle arrays for crops and irrigation
    $crops = isset($_POST['crops']) ? $_POST['crops'] : [];
    $irrigation = isset($_POST['irrigation']) ? $_POST['irrigation'] : [];
    
    // Convert arrays to JSON for storage
    $crops_json = json_encode($crops);
    $irrigation_json = json_encode($irrigation);
    
    // Check if profile already exists for this user
    $check_sql = "SELECT id FROM farmer_profiles WHERE username = '$logged_in_user'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing profile
        $sql = "UPDATE farmer_profiles SET 
                full_name = '$fullName',
                email = '$email',
                gender = '$gender',
                birthday = '$birthday',
                state = '$state',
                district = '$district',
                address = '$address',
                aadhaar_number = '$aadhaar',
                pan_number = '$pan',
                land_area = '$land_area',
                crops = '$crops_json',
                irrigation_methods = '$irrigation_json',
                updated_at = NOW()
                WHERE username = '$logged_in_user'";
    } else {
        // Insert new profile
        $sql = "INSERT INTO farmer_profiles (
                username, full_name, email, gender, birthday, state, district, 
                address, aadhaar_number, pan_number, land_area, crops, irrigation_methods
                ) VALUES (
                '$logged_in_user', '$fullName', '$email', '$gender', '$birthday', '$state', '$district',
                '$address', '$aadhaar', '$pan', '$land_area', '$crops_json', '$irrigation_json'
                )";
    }
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "Profile saved successfully!";
    } else {
        $error_message = "Error saving profile: " . mysqli_error($conn);
    }
}

// Load profile data for the logged-in user from database
$sql = "SELECT * FROM farmer_profiles WHERE username = '$logged_in_user'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $profile = mysqli_fetch_assoc($result);
    
    // Convert JSON strings back to arrays
    $crops = !empty($profile['crops']) ? json_decode($profile['crops'], true) : [];
    $irrigation = !empty($profile['irrigation_methods']) ? json_decode($profile['irrigation_methods'], true) : [];
    
    $farmer = [
        'fullName' => $profile['full_name'],
        'email' => $profile['email'],
        'gender' => $profile['gender'],
        'birthday' => $profile['birthday'],
        'state' => $profile['state'],
        'district' => $profile['district'],
        'address' => $profile['address'],
        'aadhaar' => $profile['aadhaar_number'],
        'pan' => $profile['pan_number'],
        'land_area' => $profile['land_area'],
        'crops' => $crops,
        'irrigation' => $irrigation
    ];
} else {
    // Empty farmer data structure if no profile found
    $farmer = [
        'fullName' => $logged_in_user,
        'email' => $logged_in_user,
        'gender' => 'male',
        'birthday' => '',
        'state' => '',
        'district' => '',
        'address' => '',
        'aadhaar' => '',
        'pan' => '',
        'land_area' => '',
        'crops' => [],
        'irrigation' => []
    ];
}

// Define states and districts data
$states = [
    'Maharashtra' => [
        'Ahmednagar', 'Akola', 'Amravati', 'Aurangabad', 'Beed', 'Bhandara', 'Buldhana', 
        'Chandrapur', 'Dhule', 'Gadchiroli', 'Gondia', 'Hingoli', 'Jalgaon', 'Jalna', 
        'Kolhapur', 'Latur', 'Mumbai City', 'Mumbai Suburban', 'Nagpur', 'Nanded', 
        'Nandurbar', 'Nashik', 'Osmanabad', 'Palghar', 'Parbhani', 'Pune', 'Raigad', 
        'Ratnagiri', 'Sangli', 'Satara', 'Sindhudurg', 'Solapur', 'Thane', 'Wardha', 
        'Washim', 'Yavatmal'
    ],
    'Gujarat' => [
        'Ahmedabad', 'Amreli', 'Anand', 'Aravalli', 'Banaskantha', 'Bharuch', 'Bhavnagar', 
        'Botad', 'Chhota Udaipur', 'Dahod', 'Dang', 'Devbhoomi Dwarka', 'Gandhinagar', 
        'Gir Somnath', 'Jamnagar', 'Junagadh', 'Kutch', 'Kheda', 'Mahisagar', 'Mehsana', 
        'Morbi', 'Narmada', 'Navsari', 'Panchmahal', 'Patan', 'Porbandar', 'Rajkot', 
        'Sabarkantha', 'Surat', 'Surendranagar', 'Tapi', 'Vadodara', 'Valsad'
    ],
    'Madhya Pradesh' => [
        'Agar Malwa', 'Alirajpur', 'Anuppur', 'Ashoknagar', 'Balaghat', 'Barwani', 
        'Betul', 'Bhind', 'Bhopal', 'Burhanpur', 'Chhatarpur', 'Chhindwara', 'Damoh', 
        'Datia', 'Dewas', 'Dhar', 'Dindori', 'Guna', 'Gwalior', 'Harda', 'Hoshangabad', 
        'Indore', 'Jabalpur', 'Jhabua', 'Katni', 'Khandwa', 'Khargone', 'Mandla', 
        'Mandsaur', 'Morena', 'Narsinghpur', 'Neemuch', 'Panna', 'Raisen', 'Rajgarh', 
        'Ratlam', 'Rewa', 'Sagar', 'Satna', 'Sehore', 'Seoni', 'Shahdol', 'Shajapur', 
        'Sheopur', 'Shivpuri', 'Sidhi', 'Singrauli', 'Tikamgarh', 'Ujjain', 'Umaria', 
        'Vidisha'
    ]
];

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agriculture Portal - Complete Your Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #7cb342;
            --accent-color: #ff9800;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #6c757d;
            --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark-text);
            min-height: 100vh;
            padding-bottom: 60px;
        }
        
        .main-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .main-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }
        
        .brand {
            font-family: 'Roboto Slab', serif;
            font-weight: 700;
            font-size: 28px;
            color: white;
            text-decoration: none;
        }
        
        .brand span {
            color: var(--accent-color);
        }
        
        .nav-links {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }
        
        .nav-links li {
            margin-left: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .nav-links a:hover {
            color: #ffeb3b;
        }
        
        .nav-links i {
            margin-right: 8px;
        }
        
        .profile-header {
            text-align: center;
            padding: 30px 0;
        }
        
        .profile-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 36px;
        }
        
        .profile-header h2 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .main-container {
            padding: 30px 0;
        }
        
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-message h3 {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .section-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .section-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 30px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .section-body {
            padding: 30px;
        }
        
        .progress-container {
            position: relative;
            margin-bottom: 40px;
        }
        
        .progress {
            height: 8px;
            margin-top: 30px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 20px;
        }
        
        .step-active {
            text-align: center;
            position: absolute;
            left: 0;
            top: -50px;
        }
        
        .step-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 20px;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-left: 12px;
            border-left: 4px solid var(--accent-color);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark-text);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 16px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(124, 179, 66, 0.25);
        }
        
        .input-group {
            position: relative;
        }
        
        .edit-icon {
            margin-left: 10px;
            color: var(--secondary-color);
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s;
        }
        
        .edit-icon:hover {
            color: var(--primary-color);
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .main-footer {
            background: var(--dark-text);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        /* Custom checkbox and radio buttons */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Custom multiple select */
        select[multiple] {
            height: auto;
            min-height: 120px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-nav {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-links {
                margin-top: 15px;
            }
            
            .nav-links li {
                margin: 0 10px;
            }
            
            .section-body {
                padding: 20px;
            }
        }
        
        /* Animation for success message */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert {
            animation: fadeIn 0.5s ease-out;
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        /* Custom styling for form sections */
        .form-control-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .form-control-wrapper .form-control,
        .form-control-wrapper .form-select {
            flex: 1;
        }
        
        /* Irrigation methods styling */
        .irrigation-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .irrigation-methods .form-check {
            background: var(--light-bg);
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .irrigation-methods .form-check:hover {
            background: #e9ecef;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="main-nav">
                <a href="farmer_index.php" class="brand">Agri<span>Market</span></a>
                <ul class="nav-links">
                    <li><a href="farmer_index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
            <div class="profile-header">
                <div class="profile-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h2><?php echo htmlspecialchars($farmer['fullName']); ?></h2>
                <p class="text-light">Complete your profile to access all features</p>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            <div class="welcome-message">
                <h3>Welcome to Your Profile</h3>
                <p>Please complete your profile information to get started</p>
            </div>
            
            <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-user-edit me-2"></i>Application Enrollment Form for Farmer
                </div>
                <div class="section-body">
                    <p class="text-center text-muted mb-4">Fill all form fields and Submit</p>
                    
                    <!-- Progress Indicator -->
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="step-indicator">
                            <div class="step-active">
                                <div class="step-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <p class="mt-2">Applicant Details</p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <!-- Personal Details -->
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-user me-2"></i>Personal Details</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <div class="form-control-wrapper">
                                        <input type="text" class="form-control" id="fullName" name="fullName" value="<?php echo htmlspecialchars($farmer['fullName']); ?>" required>
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('fullName').focus()"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <div class="form-control-wrapper">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($farmer['email']); ?>" required>
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('email').focus()"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="male" value="male" <?php echo ($farmer['gender'] === 'male') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="male">Male</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="female" value="female" <?php echo ($farmer['gender'] === 'female') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="female">Female</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Birthday</label>
                                    <div class="form-control-wrapper">
                                        <input type="date" class="form-control" id="birthday" name="birthday" value="<?php echo htmlspecialchars($farmer['birthday']); ?>">
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('birthday').focus()"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Details -->
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-map-marker-alt me-2"></i>Address Details</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">State</label>
                                    <div class="form-control-wrapper">
                                        <select class="form-select" id="state" name="state" required onchange="updateDistricts()">
                                            <option value="">Select your state</option>
                                            <?php foreach (array_keys($states) as $state): ?>
                                                <option value="<?php echo $state; ?>" <?php echo ($farmer['state'] === $state) ? 'selected' : ''; ?>>
                                                    <?php echo $state; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('state').focus()"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">District</label>
                                    <div class="form-control-wrapper">
                                        <select class="form-select" id="district" name="district" required>
                                            <option value="">Select your district</option>
                                            <!-- Districts will be populated by JavaScript -->
                                        </select>
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('district').focus()"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Address</label>
                                <div class="form-control-wrapper">
                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your complete address"><?php echo htmlspecialchars($farmer['address']); ?></textarea>
                                    <i class="fas fa-edit edit-icon" onclick="document.getElementById('address').focus()"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Document Details -->
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-file-alt me-2"></i>Document Details</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Aadhaar Number</label>
                                    <div class="form-control-wrapper">
                                        <input type="text" class="form-control" id="aadhaar" name="aadhaar" placeholder="Enter Aadhaar number" value="<?php echo htmlspecialchars($farmer['aadhaar']); ?>">
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('aadhaar').focus()"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">PAN Number</label>
                                    <div class="form-control-wrapper">
                                        <input type="text" class="form-control" id="pan" name="pan" placeholder="Enter PAN number" value="<?php echo htmlspecialchars($farmer['pan']); ?>">
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('pan').focus()"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Farm Details -->
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-tractor me-2"></i>Farm Details</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Land Area (acres)</label>
                                    <div class="form-control-wrapper">
                                        <input type="number" class="form-control" id="landArea" name="landArea" placeholder="Enter land area" value="<?php echo htmlspecialchars($farmer['land_area']); ?>">
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('landArea').focus()"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Primary Crops</label>
                                    <div class="form-control-wrapper">
                                        <select class="form-select" id="crops" name="crops[]" multiple>
                                            <?php 
                                            $cropOptions = ['Wheat', 'Cotton', 'Sugarcane', 'Pulses', 'Rice', 'Maize', 'Soybean', 'Groundnut'];
                                            foreach ($cropOptions as $crop): 
                                            ?>
                                                <option value="<?php echo $crop; ?>" <?php echo in_array($crop, $farmer['crops']) ? 'selected' : ''; ?>>
                                                    <?php echo $crop; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="fas fa-edit edit-icon" onclick="document.getElementById('crops').focus()"></i>
                                    </div>
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple crops</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Irrigation Methods</label>
                                <div class="irrigation-methods">
                                    <?php 
                                    $irrigationMethods = [
                                        'canal' => 'Canal',
                                        'well' => 'Well',
                                        'drip' => 'Drip Irrigation',
                                        'sprinkler' => 'Sprinkler',
                                        'rainfed' => 'Rainfed'
                                    ];
                                    foreach ($irrigationMethods as $key => $method): 
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="irrigation[]" 
                                                id="<?php echo $key; ?>" value="<?php echo $key; ?>"
                                                <?php echo in_array($key, $farmer['irrigation']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo $key; ?>"><?php echo $method; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save me-2"></i>Save Profile
                            </button>
                        </div>
                    </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // State and district data
        const statesData = <?php echo json_encode($states); ?>;
        
        // Function to update districts based on selected state
        function updateDistricts() {
            const stateSelect = document.getElementById('state');
            const districtSelect = document.getElementById('district');
            const selectedState = stateSelect.value;
            
            // Clear previous districts
            districtSelect.innerHTML = '<option value="">Select your district</option>';
            
            // Add districts for selected state
            if (selectedState && statesData[selectedState]) {
                statesData[selectedState].forEach(district => {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    // Check if this district was previously selected
                    option.selected = district === "<?php echo $farmer['district']; ?>";
                    districtSelect.appendChild(option);
                });
            }
        }
        
        // Initialize districts on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set the state if it was previously selected
            const stateSelect = document.getElementById('state');
            const savedState = "<?php echo $farmer['state']; ?>";
            if (savedState) {
                stateSelect.value = savedState;
                updateDistricts();
            }
            
            // Set the district after districts are loaded
            setTimeout(function() {
                const districtSelect = document.getElementById('district');
                const savedDistrict = "<?php echo $farmer['district']; ?>";
                if (savedDistrict) {
                    districtSelect.value = savedDistrict;
                }
            }, 100);
        });
    </script>
</body>
</html>