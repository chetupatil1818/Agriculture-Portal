<?php
session_start();
ini_set('memory_limit', '-1');
$userlogin = $_SESSION['farmer_login_user'] ?? null;

// If user is not logged in, handle appropriately
if (!$userlogin) {
    // You might want to redirect to login page or show an error
    // header("Location: login.php");
    // exit();
}

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

// Handle form submission to add new crop sale
$message = '';
$message_type = ''; // success or error

if (isset($_POST['add_crop_sale']) && $userlogin) {
    $crop_name = mysqli_real_escape_string($conn, $_POST['crop_name']);
    $quantity = floatval($_POST['quantity']);
    $price_per_kg = floatval($_POST['price_per_kg']);
    $sale_date = $_POST['sale_date'];
    
    // Validate inputs
    if (!empty($crop_name) && $quantity > 0 && $price_per_kg > 0 && !empty($sale_date)) {
        // Get farmer_id
        $query1 = "SELECT farmer_id FROM farmerlogin WHERE email='".$userlogin."'";
        $result1 = mysqli_query($conn, $query1);
        
        if ($result1 && mysqli_num_rows($result1) > 0) {
            $row = mysqli_fetch_array($result1);
            $farmer_id = $row['farmer_id'];
            
            // Calculate total amount
            $total_amount = $quantity * $price_per_kg;
            
            // Insert into farmer_history table
            $insert_sql = "INSERT INTO farmer_history (farmer_id, farmer_crop, farmer_quantity, farmer_price, date) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("isdis", $farmer_id, $crop_name, $quantity, $total_amount, $sale_date);
            
            if ($stmt->execute()) {
                $message = "Crop sale added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding crop sale: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "User not found. Please try logging in again.";
            $message_type = "error";
        }
    } else {
        $message = "Please fill all fields with valid values.";
        $message_type = "error";
    }
}

// Only query database if user is logged in
if ($userlogin) {
    $query1 = "SELECT * FROM farmerlogin WHERE email='".$userlogin."' ";
    $result1 = mysqli_query($conn, $query1);
    $row = mysqli_fetch_array($result1);
    $farmer_id = $row['farmer_id'] ?? null;

    if ($farmer_id) {
        $sql = "SELECT farmer_crop, farmer_quantity, farmer_price, `date` FROM farmer_history WHERE farmer_id='".$farmer_id."' ORDER BY `date` DESC";
        $result = mysqli_query($conn, $sql);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Agriculture Portal - Selling History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            background: #f8f9fa; 
            font-family: 'Nunito', sans-serif;
            color: #2d3a3a;
            margin: 0;
            padding: 0;
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
        .top-left-menu {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .top-left-menu ul {
            list-style: none;
            display: flex;
            gap: 15px;
            margin: 0;
            padding: 0;
        }
        .top-left-menu a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            background: rgba(76, 175, 80, 0.8);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .top-left-menu a:hover {
            background: rgba(76, 175, 80, 1);
            transform: translateY(-2px);
        }
        .banner-info {
            text-align: center;
            padding-top: 70px;
        }
        .banner-info h1 {
            font-family: 'Merriweather', serif;
            font-size: 42px;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin: 0;
        }
        .banner-info a {
            color: #fff;
            text-decoration: none;
        }
        .banner-info a:hover {
            text-decoration: underline;
        }
        #google_translate_element {
            position: absolute;
            right: 20px;
            top: 20px;
            z-index: 10;
        }
        .table-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .add-crop-form {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .add-crop-form h3 {
            color: #2e7d32;
            margin-bottom: 20px;
            font-family: 'Merriweather', serif;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #4caf50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        .btn-primary {
            background: #4caf50;
            border: none;
            padding: 10px 25px;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #3d8b40;
            transform: translateY(-2px);
        }
        .table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .table thead th {
            background: #4caf50;
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
            text-align: center;
            font-family: 'Merriweather', serif;
        }
        .table tbody td {
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            border-color: #e8f5e9;
        }
        .table tbody tr:nth-of-type(even) {
            background-color: #f8f9fa;
        }
        .table tbody tr:hover {
            background-color: #e8f5e9;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .footer { 
            text-align: center; 
            padding: 20px; 
            color: #fff; 
            background: linear-gradient(to right, #2e7d32, #4caf50);
            font-weight: 600;
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        .not-logged-in {
            max-width: 900px; 
            margin: 40px auto; 
            padding: 30px; 
            border-radius: 8px; 
            text-align: center;
            font-size: 18px;
            background: #ffebee; 
            color: #c62828; 
            border: 1px solid #ffcdd2;
        }
        @media (max-width: 800px) { 
            .top-left-menu ul {
                flex-direction: column;
                gap: 10px;
            }
            .banner-info h1 {
                font-size: 32px;
            }
            .table thead {
                display: none;
            }
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            .table tr {
                margin-bottom: 15px;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            }
            .table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border-bottom: 1px solid #e8f5e9;
            }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: calc(50% - 15px);
                padding-right: 15px;
                text-align: left;
                font-weight: 600;
                color: #2e7d32;
            }
        }
    </style>
</head>
<body>
    <!-- Header with background image -->
    <div class="header-banner">
        <div class="top-left-menu">
            <ul>
                <li><a href="farmer_index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div id="google_translate_element"></div>
        
        <div class="banner-info">
            <h1><a href="farmer_history.php">Selling History</a></h1>
        </div>
    </div>

    <div class="table-container">
        <?php if (!$userlogin): ?>
            <div class="not-logged-in">
                <p>You are not logged in. Please <a href="login.php">login</a> to view your selling history.</p>
            </div>
        <?php elseif (!isset($farmer_id) || !$farmer_id): ?>
            <div class="not-logged-in">
                <p>User information not found. Please try logging in again.</p>
            </div>
        <?php else: ?>
            <!-- Add Crop Sale Form -->
            <div class="add-crop-form">
                <h3><i class="fas fa-plus-circle"></i> Add New Crop Sale</h3>
                
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="crop_name">Crop Name</label>
                                <input type="text" class="form-control" id="crop_name" name="crop_name" 
                                       placeholder="e.g., Wheat, Rice, Corn" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="quantity">Quantity (KG)</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       min="0.1" step="0.1" placeholder="e.g., 100" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="price_per_kg">Price per KG (₹)</label>
                                <input type="number" class="form-control" id="price_per_kg" name="price_per_kg" 
                                       min="1" step="0.01" placeholder="e.g., 25.50" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="sale_date">Sale Date</label>
                                <input type="date" class="form-control" id="sale_date" name="sale_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" name="add_crop_sale" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Add Sale
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Selling History Table -->
            <table class="table table-striped table-bordered table-responsive-md">
                <thead>
                    <tr>
                        <th><center>Crop</center></th>
                        <th><center>Quantity (in KG)</center></th>
                        <th><center>Price per KG (₹)</center></th>
                        <th><center>Total Amount (₹)</center></th>
                        <th><center>Date of Transaction</center></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($result) && $result && mysqli_num_rows($result) > 0) {
                        while($row = $result->fetch_assoc()) {
                            $cropname = ucfirst($row["farmer_crop"]);
                            $cropquantity = $row["farmer_quantity"];
                            $cropprice = $row["farmer_price"];
                            $currentdate = $row['date'];
                            
                            // Calculate price per kg
                            $price_per_kg = $cropquantity > 0 ? $cropprice / $cropquantity : 0;
                            
                            echo "<tr>";
                            echo "<td data-label='Crop'><center>$cropname</center></td>";
                            echo "<td data-label='Quantity'><center>$cropquantity</center></td>";
                            echo "<td data-label='Price per KG'><center>₹" . number_format($price_per_kg, 2) . "</center></td>";
                            echo "<td data-label='Total Amount'><center>₹$cropprice</center></td>";
                            echo "<td data-label='Date'><center>$currentdate</center></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center;'>No selling history found. Add your first crop sale above!</td></tr>";
                    }
                    
                    // Close connection
                    $conn->close();
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>Agriculture Portal</p>
    </div>

    <!-- Google Translate -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage:'en',
            includedLanguages:'bn,en,gu,hi,kn,mr,ta,te'
        },'google_translate_element');
    }

    // Auto-calculate total amount
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const priceInput = document.getElementById('price_per_kg');
        
        function calculateTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            // You can display the total if needed
            // const total = quantity * price;
        }
        
        quantityInput.addEventListener('input', calculateTotal);
        priceInput.addEventListener('input', calculateTotal);
    });
    </script>
</body>
</html>