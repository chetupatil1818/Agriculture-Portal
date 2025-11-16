<?php
session_start();
if (!isset($_SESSION['customer_login_user'])) {
    header("Location: customer_login.php");
    exit();
}

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

// Get user ID
$user_id = 0;
$res = mysqli_query($conn, "SELECT cust_id FROM custlogin WHERE email='$userlogin' LIMIT 1");
if($row = mysqli_fetch_assoc($res)) {
    $user_id = $row['cust_id'];
}

// Fetch user's orders (you'll need to create an orders table first)
$orders = [];
$query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
$result = mysqli_query($conn, $query);
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}

// Handle order cancellation
$message = "";
if(isset($_POST['cancel_order']) && isset($_POST['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $reason = mysqli_real_escape_string($conn, $_POST['cancel_reason']);
    
    // Fetch order details to get items and quantities
    $order_query = "SELECT * FROM orders WHERE order_id = '$order_id' AND user_id = '$user_id'";
    $order_result = mysqli_query($conn, $order_query);
    
    if(mysqli_num_rows($order_result) > 0) {
        $order = mysqli_fetch_assoc($order_result);
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Update order status to cancelled
            $update_order = "UPDATE orders SET status = 'cancelled', cancellation_reason = '$reason', cancelled_at = NOW() WHERE order_id = '$order_id'";
            mysqli_query($conn, $update_order);
            
            // Restore crop quantities (you'll need to store order items in order_items table)
            $items_query = "SELECT * FROM order_items WHERE order_id = '$order_id'";
            $items_result = mysqli_query($conn, $items_query);
            
            while($item = mysqli_fetch_assoc($items_result)) {
                $crop_name = mysqli_real_escape_string($conn, $item['crop_name']);
                $quantity = $item['quantity'];
                
                // Update crop quantity in trade_crops table
                $update_crop = "UPDATE trade_crops SET quantity = quantity + $quantity WHERE crop_name = '$crop_name'";
                mysqli_query($conn, $update_crop);
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            $message = "<div class='alert alert-success'>Order cancelled successfully. Quantities have been restored.</div>";
            
            // Refresh orders list
            header("Location: cancel_order.php?success=1");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $message = "<div class='alert alert-danger'>Error cancelling order: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Order not found or you don't have permission to cancel this order.</div>";
    }
}

// Show success message if redirected after cancellation
if(isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "<div class='alert alert-success'>Order cancelled successfully. Quantities have been restored to available stock.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Order - Agriculture Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2e7d32;
            --primary-light: #4caf50;
            --primary-dark: #1b5e20;
            --secondary: #ffa000;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7f9;
        }
        
        .navbar {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), 
                       url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
        }
        
        .brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .brand span {
            color: var(--secondary);
        }
        
        .order-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
        }
        
        .order-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px 20px;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-delivered { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-shipped { background: #cce7ff; color: #004085; }
        
        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="brand" href="customer_index.php">Agri<span>Market</span></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="customer_index.php"><i class="fas fa-home"></i> Home</a>
                <a class="nav-link" href="buy_crops.php"><i class="fas fa-shopping-cart"></i> Buy Crops</a>
                <a class="nav-link" href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-times-circle"></i> Cancel Order</h2>
                    <a href="buy_crops.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Shopping
                    </a>
                </div>
                
                <?php echo $message; ?>
                
                <!-- Orders List -->
                <div class="row">
                    <?php if(!empty($orders)): ?>
                        <?php foreach($orders as $order): ?>
                            <?php if($order['status'] != 'cancelled'): ?>
                            <div class="col-md-6">
                                <div class="card order-card">
                                    <div class="order-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <small>Placed on: <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <strong>Total Amount:</strong><br>
                                                <span class="h5 text-success">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                            </div>
                                            <div class="col-6">
                                                <strong>Payment Method:</strong><br>
                                                <?php echo ucfirst($order['payment_method']); ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Order Items -->
                                        <h6>Items:</h6>
                                        <?php
                                        $items_query = "SELECT * FROM order_items WHERE order_id = '{$order['order_id']}'";
                                        $items_result = mysqli_query($conn, $items_query);
                                        while($item = mysqli_fetch_assoc($items_result)):
                                        ?>
                                            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                                <span><?php echo $item['crop_name']; ?></span>
                                                <span><?php echo $item['quantity']; ?> kg × ₹<?php echo $item['price_per_kg']; ?></span>
                                            </div>
                                        <?php endwhile; ?>
                                        
                                        <!-- Cancel Button -->
                                        <button class="btn btn-cancel w-100 mt-3" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal"
                                                data-order-id="<?php echo $order['order_id']; ?>">
                                            <i class="fas fa-times-circle"></i> Cancel Order
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h4>No Orders Found</h4>
                                <p>You haven't placed any orders yet.</p>
                                <a href="buy_crops.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="cancel_order.php">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="cancelOrderId">
                        <p>Are you sure you want to cancel this order? The crop quantities will be restored to available stock.</p>
                        
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label">Reason for cancellation:</label>
                            <select class="form-select" name="cancel_reason" required>
                                <option value="">Select a reason</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="wrong_order">Wrong order placed</option>
                                <option value="delivery_issues">Delivery time too long</option>
                                <option value="found_cheaper">Found better price elsewhere</option>
                                <option value="other">Other reason</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Note:</strong> Order cancellation cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="cancel_order" class="btn btn-danger">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set order ID in modal
        document.getElementById('cancelModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var orderId = button.getAttribute('data-order-id');
            document.getElementById('cancelOrderId').value = orderId;
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>