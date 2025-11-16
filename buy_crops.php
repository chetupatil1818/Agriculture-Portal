<?php
session_start();
$userlogin = $_SESSION['customer_login_user'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture_portal";

// Create Connection 
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Razorpay Test Credentials (Demo Account)
define('RAZORPAY_KEY_ID', 'rzp_test_1DP5mmOlF5G5ag');
define('RAZORPAY_KEY_SECRET', 'rzp_test_1DP5mmOlF5G5ag');

// Initialize variables
$order_details_html = "";
$order_message = "";
$total = 0;

// Remove from cart and DB
if(isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    foreach($_SESSION["shopping_cart"] as $keys => $values) {
        if($values["item_id"] == $id) {
            $crop_name = $values["item_name"];
            $query6 = "DELETE FROM cart WHERE cropname = '".$crop_name."' LIMIT 1";
            mysqli_query($conn, $query6);
            unset($_SESSION["shopping_cart"][$keys]);
            break;
        }
    }
    $_SESSION["shopping_cart"] = array_values($_SESSION["shopping_cart"]);
    header("Location: buy_crops.php");
    exit;
}

// Edit address action
if(isset($_GET["action"]) && $_GET["action"] == "edit_address") {
    unset($_SESSION['order_address']);
    unset($_SESSION['show_payment']);
    header("Location: buy_crops.php");
    exit;
}

// Add to cart and update DB
if(isset($_POST["add_to_cart"])) {
    $crop_id = intval($_POST["crop_id"]);
    $crop_name = $_POST["hidden_name"];
    $quantity = intval($_POST["quantity"]);
    $price_per_kg = floatval($_POST["hidden_price"]);
    $total_price = $price_per_kg * $quantity;

    // Get user_id from session
    $user_email = $_SESSION['customer_login_user'];
    $user_id = 0;
    $res = mysqli_query($conn, "SELECT cust_id FROM custlogin WHERE email='$user_email' LIMIT 1");
    if($row = mysqli_fetch_assoc($res)) {
        $user_id = $row['cust_id'];
    }

    if(!isset($_SESSION["shopping_cart"])) $_SESSION["shopping_cart"] = [];
    
    // Check if item already exists in cart
    $item_exists = false;
    foreach($_SESSION["shopping_cart"] as $keys => $values) {
        if($values["item_id"] == $crop_id) {
            $_SESSION["shopping_cart"][$keys]["item_quantity"] += $quantity;
            $_SESSION["shopping_cart"][$keys]["item_price"] += $total_price;
            $item_exists = true;
            break;
        }
    }
    
    if(!$item_exists) {
        $item_array = array(
            'item_id' => $crop_id,
            'item_name' => $crop_name,
            'item_price' => $total_price,
            'item_quantity' => $quantity,
            'price_per_kg' => $price_per_kg
        );
        $_SESSION['shopping_cart'][] = $item_array;
    }
    
    // Insert with user_id
    $query4 = "INSERT INTO cart (user_id, cropname, quantity, price) VALUES ('$user_id', '$crop_name', '$quantity', '$total_price')";
    mysqli_query($conn, $query4);
}

// Save address
if(isset($_POST["save_address"])) {
    $_SESSION['order_address'] = [
        'name' => $_POST['name'],
        'mobile' => $_POST['mobile'],
        'email' => $_POST['email'],
        'address1' => $_POST['address1'],
        'address2' => $_POST['address2'],
        'town' => $_POST['town'],
        'district' => $_POST['district'],
        'postcode' => $_POST['postcode'],
        'state' => $_POST['state']
    ];
    $_SESSION['show_payment'] = true;
}

// Handle Razorpay payment verification
if(isset($_POST["razorpay_payment_id"])) {
    $razorpay_payment_id = $_POST["razorpay_payment_id"];
    $payment_method = $_POST["payment_method"];
    
    if(isset($_SESSION['order_address']) && isset($_SESSION["shopping_cart"])) {
        $address = $_SESSION['order_address'];
        $cart = $_SESSION["shopping_cart"];
        $total = 0;
        foreach($cart as $item) { 
            $total += $item['item_price']; 
        }
        $order_id = 'ORD' . date('YmdHis') . rand(1000,9999);
        
        // Generate order summary
        $order_details_html = '
        <div class="order-success">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Order Placed Successfully!</h2>
            
            <div class="order-details">
                <div class="order-detail-item">
                    <strong>Order ID:</strong> '.$order_id.'
                </div>
                <div class="order-detail-item">
                    <strong>Payment ID:</strong> '.htmlspecialchars($razorpay_payment_id).'
                </div>
                <div class="order-detail-item">
                    <strong>Payment Method:</strong> '.($payment_method == "upi" ? "UPI" : "QR Code").'
                </div>
                <div class="order-detail-item">
                    <strong>Name:</strong> '.htmlspecialchars($address['name']).'
                </div>
                <div class="order-detail-item">
                    <strong>Mobile:</strong> '.htmlspecialchars($address['mobile']).'
                </div>
                <div class="order-detail-item">
                    <strong>Email:</strong> '.htmlspecialchars($address['email']).'
                </div>
                <div class="order-detail-item">
                    <strong>Address:</strong> 
                    '.htmlspecialchars($address['address1']) . ', ' . 
                    (isset($address['address2']) ? htmlspecialchars($address['address2']) . ', ' : '') . 
                    htmlspecialchars($address['town']) . ', ' . 
                    htmlspecialchars($address['district']) . ', ' . 
                    htmlspecialchars($address['postcode']) . ', ' . 
                    htmlspecialchars($address['state']).'
                </div>
                
                <h5 class="mt-4">Order Items:</h5>
                <div class="table-container mt-3">
                    <table class="crops-table">
                        <thead>
                            <tr>
                                <th>Crop</th>
                                <th>Quantity (KG)</th>
                                <th>Price Per KG (Rs.)</th>
                                <th>Total Price (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach($cart as $item) {
            $order_details_html .= "<tr>
                <td>".htmlspecialchars($item['item_name'])."</td>
                <td>".$item['item_quantity']."</td>
                <td>Rs. ".$item['price_per_kg']."</td>
                <td>Rs. ".$item['item_price']."</td>
            </tr>";
        }
        $order_details_html .= '<tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>Rs. '.number_format($total,2).'</strong></td>
            </tr>
        </tbody>
    </table>
</div>
</div>

<p class="thank-you-message">Thank you for shopping with us!</p>
</div>';

        // Update crop quantities
        foreach($cart as $item) {
            $crop_name = mysqli_real_escape_string($conn, $item['item_name']);
            $result = mysqli_query($conn, "SELECT quantity FROM trade_crops WHERE crop_name = '$crop_name'");
            if($row = mysqli_fetch_assoc($result)) {
                $new_quantity = $row['quantity'] - $item['item_quantity'];
                if($new_quantity < 0) $new_quantity = 0;
                $update = "UPDATE trade_crops SET quantity = $new_quantity WHERE crop_name = '$crop_name'";
                mysqli_query($conn, $update);
            }
        }

        $_SESSION["shopping_cart"] = [];
        mysqli_query($conn, "DELETE FROM cart");
        unset($_SESSION['order_address']);
        unset($_SESSION['show_payment']);
    }
}

// Simulate payment and order placement for COD
if(isset($_POST["place_order"]) && isset($_POST["payment_method"]) && $_POST["payment_method"] == "cod") {
    if(!isset($_SESSION['order_address'])) {
        $order_message = "<div class='alert alert-danger'>Please fill in your address details first.</div>";
    } else {
        $address = $_SESSION['order_address'];
        $cart = isset($_SESSION["shopping_cart"]) ? $_SESSION["shopping_cart"] : [];
        $total = 0;
        foreach($cart as $item) { 
            $total += $item['item_price']; 
        }
        $order_id = 'ORD' . date('YmdHis') . rand(1000,9999);

        // Order summary for COD
        $order_details_html = '
        <div class="order-success">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Order Placed Successfully!</h2>
            
            <div class="order-details">
                <div class="order-detail-item">
                    <strong>Order ID:</strong> '.$order_id.'
                </div>
                <div class="order-detail-item">
                    <strong>Payment Method:</strong> Cash on Delivery
                </div>
                <div class="order-detail-item">
                    <strong>Name:</strong> '.htmlspecialchars($address['name']).'
                </div>
                <div class="order-detail-item">
                    <strong>Mobile:</strong> '.htmlspecialchars($address['mobile']).'
                </div>
                <div class="order-detail-item">
                    <strong>Email:</strong> '.htmlspecialchars($address['email']).'
                </div>
                <div class="order-detail-item">
                    <strong>Address:</strong> 
                    '.htmlspecialchars($address['address1']) . ', ' . 
                    (isset($address['address2']) ? htmlspecialchars($address['address2']) . ', ' : '') . 
                    htmlspecialchars($address['town']) . ', ' . 
                    htmlspecialchars($address['district']) . ', ' . 
                    htmlspecialchars($address['postcode']) . ', ' . 
                    htmlspecialchars($address['state']).'
                </div>
                
                <h5 class="mt-4">Order Items:</h5>
                <div class="table-container mt-3">
                    <table class="crops-table">
                        <thead>
                            <tr>
                                <th>Crop</th>
                                <th>Quantity (KG)</th>
                                <th>Price Per KG (Rs.)</th>
                                <th>Total Price (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>';
        foreach($cart as $item) {
            $order_details_html .= "<tr>
                <td>".htmlspecialchars($item['item_name'])."</td>
                <td>".$item['item_quantity']."</td>
                <td>Rs. ".$item['price_per_kg']."</td>
                <td>Rs. ".$item['item_price']."</td>
            </tr>";
        }
        $order_details_html .= '<tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>Rs. '.number_format($total,2).'</strong></td>
            </tr>
        </tbody>
    </table>
</div>
</div>

<p class="thank-you-message">Thank you for shopping with us!</p>
</div>';

        // Update crop quantities
        foreach($cart as $item) {
            $crop_name = mysqli_real_escape_string($conn, $item['item_name']);
            $result = mysqli_query($conn, "SELECT quantity FROM trade_crops WHERE crop_name = '$crop_name'");
            if($row = mysqli_fetch_assoc($result)) {
                $new_quantity = $row['quantity'] - $item['item_quantity'];
                if($new_quantity < 0) $new_quantity = 0;
                $update = "UPDATE trade_crops SET quantity = $new_quantity WHERE crop_name = '$crop_name'";
                mysqli_query($conn, $update);
            }
        }

        $_SESSION["shopping_cart"] = [];
        mysqli_query($conn, "DELETE FROM cart");
        unset($_SESSION['order_address']);
        unset($_SESSION['show_payment']);
    }
}

// Get available crops
$available_crops = [];
$query = "SELECT id, crop_name, quantity, price FROM trade_crops WHERE quantity > 0";
$result = mysqli_query($conn, $query);
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $available_crops[] = $row;
    }
}

// Calculate cart total
$cart_total = 0;
if(isset($_SESSION["shopping_cart"])) {
    foreach($_SESSION["shopping_cart"] as $item) {
        $cart_total += $item['item_price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agriculture Marketplace - Buy Crops</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
            min-height: calc(100vh - 200px);
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
        
        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }
        
        .crops-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .crops-table th {
            background-color: var(--primary-light);
            color: var(--white);
            padding: 1rem;
            text-align: left;
        }
        
        .crops-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .crops-table tr:last-child td {
            border-bottom: none;
        }
        
        .crops-table tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Button Styles */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 0.5rem 1.25rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: #528f53;
            border-color: #528f53;
        }
        
        .btn-sm {
            padding: 0.35rem 0.85rem;
            font-size: 0.875rem;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-text);
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        
        /* Order Success */
        .order-success {
            text-align: center;
            padding: 2rem;
        }
        
        .success-icon {
            color: var(--primary);
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        
        .order-details {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin: 1.5rem 0;
            text-align: left;
        }
        
        .order-detail-item {
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .order-detail-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        /* Quantity Input */
        .quantity-input {
            width: 80px;
            text-align: center;
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
        }
        
        /* Payment Options */
        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option:hover {
            border-color: var(--primary-light);
        }
        
        .payment-option.selected {
            border-color: var(--primary);
            background-color: #f0f9f0;
        }
        
        .payment-option input[type="radio"] {
            margin-right: 10px;
        }
        
        .payment-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .qr-code-container {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
        }
        
        .qr-code {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .upi-apps {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .upi-app {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .payment-instructions {
            background: #e8f5e8;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .demo-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 0.75rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="main-nav">
                <a href="buy_crops.php" class="brand">Agri<span>Market</span></a>
                
                <ul class="nav-links">
                    <li><a href="customer_index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            <?php if(!empty($order_details_html)): ?>
                <div class="section-card">
                    <?php echo $order_details_html; ?>
                </div>
            <?php elseif(!empty($order_message)): ?>
                <?php echo $order_message; ?>
            <?php endif; ?>

            <!-- Available Crops Section -->
            <div class="section-card">
                <div class="section-header">
                    Available Crops
                </div>
                <div class="section-body">
                    <div class="table-container">
                        <table class="crops-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Crop Name</th>
                                    <th>Available Quantity (in KG)</th>
                                    <th>Price Per KG (in Rs.)</th>
                                    <th>Quantity to Buy</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($available_crops)): ?>
                                    <?php $i = 1; foreach($available_crops as $crop): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($crop['crop_name']); ?></td>
                                        <td><?php echo htmlspecialchars($crop['quantity']); ?></td>
                                        <td>Rs. <?php echo htmlspecialchars($crop['price']); ?></td>
                                        <td>
                                            <input type="number" name="quantity" class="form-control quantity-input" 
                                                   value="1" min="1" max="<?php echo $crop['quantity']; ?>" 
                                                   form="form_<?php echo $crop['id']; ?>">
                                        </td>
                                        <td>
                                            <form method="POST" action="buy_crops.php" id="form_<?php echo $crop['id']; ?>">
                                                <input type="hidden" name="crop_id" value="<?php echo $crop['id']; ?>">
                                                <input type="hidden" name="hidden_name" value="<?php echo htmlspecialchars($crop['crop_name']); ?>">
                                                <input type="hidden" name="hidden_price" value="<?php echo $crop['price']; ?>">
                                                <button class="btn btn-primary btn-sm" name="add_to_cart" type="submit" <?php if($crop['quantity'] <= 0) echo "disabled"; ?>>
                                                    <i class="fas fa-cart-plus"></i> Add To Cart
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-3">No crops available at the moment.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Shopping Cart Section -->
            <div class="section-card">
                <div class="section-header">
                    Your Shopping Cart
                </div>
                <div class="section-body">
                    <?php if(!empty($_SESSION["shopping_cart"])): ?>
                    <div class="table-container">
                        <table class="crops-table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity (in KG)</th>
                                    <th>Price Per KG (Rs.)</th>
                                    <th>Total Price (Rs.)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $total = 0;
                                    foreach($_SESSION["shopping_cart"] as $keys => $values) {
                                ?>
                                <tr>
                                    <td><?php echo ucfirst($values["item_name"]); ?></td>
                                    <td><?php echo $values["item_quantity"]; ?></td>
                                    <td>Rs. <?php echo $values["price_per_kg"]; ?></td>
                                    <td>Rs. <?php echo $values["item_price"]; ?> </td>
                                    <td>
                                        <a href="buy_crops.php?action=delete&id=<?php echo $values["item_id"]; ?>" class="text-danger">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                        $total += $values["item_price"];
                                    }
                                ?>
                                <tr>
                                    <td colspan="3" align="right"><strong>Total</strong></td>
                                    <td align="right"><strong>Rs. <?php echo number_format($total, 2); ?></strong></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Step Indicator -->
                    <div class="step-indicator mt-4">
                        <div class="step <?php echo isset($_SESSION['order_address']) ? 'completed' : 'active'; ?>">
                            <div class="step-number">1</div>
                            <div>Address</div>
                        </div>
                        <div class="step <?php echo (isset($_SESSION['show_payment']) && $_SESSION['show_payment']) ? 'active' : ''; ?>">
                            <div class="step-number">2</div>
                            <div>Payment</div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div>Confirmation</div>
                        </div>
                    </div>
                    
                    <!-- Address Form (Step 1) -->
                    <?php if(!isset($_SESSION['order_address'])): ?>
                    <div class="mt-4">
                        <h4>Step 1: Delivery Address Details</h4>
                        <form method="post" action="buy_crops.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Name *</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Mobile Number *</label>
                                        <input type="text" name="mobile" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Address Line 1 *</label>
                                <input type="text" name="address1" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" name="address2" class="form-control">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Town *</label>
                                        <input type="text" name="town" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">District *</label>
                                        <input type="text" name="district" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Postcode *</label>
                                        <input type="text" name="postcode" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">State *</label>
                                <input type="text" name="state" class="form-control" required>
                            </div>
                            
                            <button type="submit" name="save_address" class="btn btn-primary">
                                Save Address & Continue to Payment
                            </button>
                        </form>
                    </div>
                    
                    <!-- Payment Form (Step 2) -->
                    <?php elseif(isset($_SESSION['show_payment']) && $_SESSION['show_payment']): ?>
                    <div class="mt-4">
                        <h4>Step 2: Choose Payment Method</h4>
                        <form method="post" action="buy_crops.php" id="payment-form">
                            <!-- UPI Payment Option -->
                            <div class="payment-option" onclick="selectPayment('upi')">
                                <input type="radio" name="payment_method" id="upi" value="upi" required>
                                <label for="upi" style="cursor: pointer; width: 100%;">
                                    <div class="payment-icon">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <strong>Pay with UPI</strong><br>
                                    <small class="text-muted">Fast and secure UPI payment</small>
                                </label>
                            </div>
                            
                            <!-- QR Code Payment Option -->
                            <div class="payment-option" onclick="selectPayment('qrcode')">
                                <input type="radio" name="payment_method" id="qrcode" value="qrcode" required>
                                <label for="qrcode" style="cursor: pointer; width: 100%;">
                                    <div class="payment-icon">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <strong>Scan QR Code</strong><br>
                                    <small class="text-muted">Scan to pay with any UPI app</small>
                                </label>
                            </div>
                            
                            <!-- Cash on Delivery -->
                            <div class="payment-option" onclick="selectPayment('cod')">
                                <input type="radio" name="payment_method" id="cod" value="cod" required>
                                <label for="cod" style="cursor: pointer; width: 100%;">
                                    <div class="payment-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <strong>Cash on Delivery</strong><br>
                                    <small class="text-muted">Pay when you receive your order</small>
                                </label>
                            </div>
                            
                            <!-- UPI Payment Details -->
                            <div id="upi-details" class="payment-instructions" style="display: none;">
                                <h6><i class="fas fa-mobile-alt"></i> UPI Payment Instructions</h6>
                                <p class="mb-2"><strong>UPI ID:</strong> <span class="text-success">agrimarket@upi</span></p>
                                <p>Open your UPI app (Google Pay, PhonePe, Paytm, etc.) and send payment to the above UPI ID.</p>
                                <div class="upi-apps">
                                    <div class="upi-app">GPay</div>
                                    <div class="upi-app">PhonePe</div>
                                    <div class="upi-app">Paytm</div>
                                    <div class="upi-app">BHIM</div>
                                </div>
                            </div>
                            
                            <!-- QR Code Display -->
                            <div id="qrcode-details" class="qr-code-container" style="display: none;">
                                <h6><i class="fas fa-qrcode"></i> Scan QR Code to Pay</h6>
                                <div class="qr-code">
                                    <!-- Demo QR Code - In real implementation, generate dynamic QR -->
                                    <div style="width: 100%; height: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; text-align: center;">
                                        DEMO QR CODE<br>Scan with any UPI app
                                    </div>
                                </div>
                                <p class="mt-2">Scan this QR code with any UPI app to complete your payment</p>
                                <div class="upi-apps">
                                    <div class="upi-app">GPay</div>
                                    <div class="upi-app">PhonePe</div>
                                    <div class="upi-app">Paytm</div>
                                </div>
                            </div>
                            
                            <div class="demo-note">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Demo Mode:</strong> This is a simulation. No real payment will be processed.
                            </div>
                            
                            <div class="mt-4">
                                <button type="button" id="proceed-payment" class="btn btn-success btn-lg" onclick="processPayment()">
                                    <i class="fas fa-lock"></i> Complete Payment
                                </button>
                                <a href="buy_crops.php?action=edit_address" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-edit"></i> Edit Address
                                </a>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <p class="text-center text-muted py-3">Your cart is empty. Add some crops from above.</p>
                    <?php endif; ?>
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
        // Select payment method
        function selectPayment(method) {
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`input[value="${method}"]`).parentElement.classList.add('selected');
            document.querySelector(`input[value="${method}"]`).checked = true;
            
            // Show/hide relevant payment details
            document.getElementById('upi-details').style.display = method === 'upi' ? 'block' : 'none';
            document.getElementById('qrcode-details').style.display = method === 'qrcode' ? 'block' : 'none';
        }
        
        // Process payment based on selected method
        function processPayment() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!paymentMethod) {
                alert('Please select a payment method');
                return;
            }
            
            if (paymentMethod.value === 'cod') {
                // For COD, simply submit the form
                document.getElementById('payment-form').submit();
            } else {
                // For UPI and QR Code payments, simulate Razorpay payment
                simulateUPIPayment(paymentMethod.value);
            }
        }
        
        // Simulate UPI/QR Code payment
        function simulateUPIPayment(method) {
            const paymentType = method === 'upi' ? 'UPI' : 'QR Code';
            
            if(confirm(`Demo: ${paymentType} Payment Simulation\n\nClick OK to simulate successful ${paymentType} payment.`)) {
                // Create a form and submit it to process the payment
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'buy_crops.php';
                
                const paymentId = document.createElement('input');
                paymentId.type = 'hidden';
                paymentId.name = 'razorpay_payment_id';
                paymentId.value = 'pay_' + Math.random().toString(36).substr(2, 9);
                form.appendChild(paymentId);
                
                const paymentMethod = document.createElement('input');
                paymentMethod.type = 'hidden';
                paymentMethod.name = 'payment_method';
                paymentMethod.value = method;
                form.appendChild(paymentMethod);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Validate quantity input
        document.querySelectorAll('.quantity-input').forEach(function(input) {
            input.addEventListener('change', function() {
                var max = parseInt(this.getAttribute('max'));
                var value = parseInt(this.value);
                
                if (value < 1) {
                    this.value = 1;
                } else if (value > max) {
                    this.value = max;
                }
            });
        });
        
        // Auto-select first payment option
        document.addEventListener('DOMContentLoaded', function() {
            const firstPaymentOption = document.querySelector('.payment-option');
            if (firstPaymentOption) {
                firstPaymentOption.classList.add('selected');
                const radio = firstPaymentOption.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    selectPayment(radio.value);
                }
            }
        });
    </script>
</body>
</html>