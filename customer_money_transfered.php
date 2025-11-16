<?php
session_start();
date_default_timezone_set("Asia/Calcutta"); 
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

// Cart Details Query
$Cartquery = "SELECT * from cart";
$cartresult = mysqli_query($conn, $Cartquery);

// Customer Details Query
$customerquery = "SELECT * from custlogin where email='" . $userlogin . "'";
$customerresult = mysqli_query($conn, $customerquery);

$row = mysqli_fetch_array($customerresult);
$Customername = decrypt($row['cust_name'], $pk);
$CustomerAddress = decrypt($row['address'], $pk);
$CustomerCity = decrypt($row['city'], $pk);
$CustomerPincode = decrypt($row['pincode'], $pk);
$CustomerState = decrypt($row['state'], $pk);
$CustomerPhone = decrypt($row['phone_no'], $pk);
$CustomerEmail = $row['email'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transaction Page</title>
    <link href="css/bootstrap.css" rel='stylesheet' type='text/css' />
    <link href="css/invoice.css" rel='stylesheet' type='text/css' />
    <script src="js/jquery.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="js/move-top.js"></script>
    <script type="text/javascript" src="js/easing.js"></script>
</head>
<body>
    <!-- header-section-starts -->
    <div class="header-banner" style="min-height:210px">
        <div class="container">
            <div class="header-top">
                <div class="social-icons">
                    <div id="google_translate_element"></div>
                    <script type="text/javascript">
                        function googleTranslateElementInit() {
                            new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'bn,en,gu,hi,kn,mr,ta,te'}, 'google_translate_element');
                        }
                    </script>
                </div>
                <span class="menu"><img class="imgclass" src="images/nav.png" alt=""/></span>
                <div class="top-menu">
                    <ul>
                        <nav class="cl-effect-13">
                            <li><a href="customer_index.php">Home</a></li>
                            <li><a href="php/logout.php">Logout</a></li>
                        </nav>
                    </ul>
                </div>
                <script>
                    $( "span.menu" ).click(function() {
                        $( ".top-menu ul" ).slideToggle(300, function() {});
                    });
                </script>
                <div class="clearfix"></div>
            </div>
            <div class="banner-info text-center">
                <h1><a href="customer_money_transfered.php">Transaction</a></h1>
            </div>
        </div>
    </div>
    <!-- header-section-ends -->

    <!-- INVOICE Table Starts -->
    <div class="container">
        <div class="col-md-12">
            <div class="invoice">
                <div class="invoice-company text-inverse f-w-600">
                    <span class="pull-right hidden-print">
                        <a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-white m-b-10 p-l-5"><i class="fa fa-print t-plus-1 fa-fw fa-lg"></i> Print</a>
                    </span>
                    Agriculture Portal
                </div>
                <div class="invoice-header">
                    <div class="invoice-to">
                        <small>to</small>
                        <address class="m-t-5 m-b-5">
                            <strong class="text-inverse"><?php echo $Customername; ?></strong><br>
                            <?php echo $CustomerAddress; ?><br>
                            <?php echo $CustomerCity; ?>, <?php echo $CustomerPincode; ?><br>
                            Phone: <?php echo $CustomerPhone; ?><br>
                        </address>
                    </div>
                    <div class="invoice-date">
                        <small>Invoice</small>
                        <div class="date text-inverse m-t-5"><?php echo date('d/m/Y'); ?></div>
                        <div class="date text-inverse m-t-5"><?php echo date('H:i:s'); ?></div>
                        <div class="invoice-detail">
                            #0000123DSS<br>
                        </div>
                    </div>
                </div>
                <div class="invoice-content">
                    <div class="table-responsive">
                        <table class="table table-invoice">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th class="text-center" width="10%">Quantity</th>
                                    <th class="text-center" width="10%">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($rows = mysqli_fetch_assoc($cartresult)) { ?>
                                    <tr>
                                        <td class="text-inverse"><?php echo ucfirst($rows['cropname']); ?></td>
                                        <td class="text-center"><?php echo $rows['quantity']; ?></td>
                                        <td class="text-center"><?php echo $rows['price']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="invoice-price">
                        <div class="invoice-price-left">
                            <div class="invoice-price-row">
                                <p>Amount Paid</p>
                            </div>
                        </div>
                        <div class="invoice-price-right">
                            <small>TOTAL</small> <span class="f-w-600">Rs.&nbsp<?php echo $_SESSION['Total_Cart_Price']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="invoice-footer">
                    <p class="text-center m-b-5 f-w-600">
                        THANK YOU FOR YOUR BUSINESS
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- INVOICE Table Ends -->

    <!-- Fake Payment Method -->
    <div class="container">
        <h3>Complete Your Payment</h3>
        <form action="payment_success.php" method="POST">
            <div class="form-group">
                <label for="cardNumber">Card Number (Fake)</label>
                <input type="text" class="form-control" id="cardNumber" name="cardNumber" placeholder="Enter any number" required>
            </div>
            <div class="form-group">
                <label for="expiryDate">Expiry Date</label>
                <input type="text" class="form-control" id="expiryDate" name="expiryDate" placeholder="MM/YY" required>
            </div>
            <div class="form-group">
                <label for="cvv">CVV</label>
                <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required>
            </div>
            <div class="form-group">
                <label for="nameOnCard">Name on Card</label>
                <input type="text" class="form-control" id="nameOnCard" name="nameOnCard" placeholder="John Doe" required>
            </div>
            <button type="submit" class="btn btn-primary">Pay Rs. <?php echo $_SESSION['Total_Cart_Price']; ?></button>
        </form>
    </div>

    <!-- Footer -->
    <div class="footer" style="position:absolute;width:100%; bottom:0;">
        <div class="container">
            <div class="copyright text-center">
                <p>&copy; Agriculture Portal</p>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $().UItoTop({ easingType: 'easeOutQuart' });
        });
    </script>
    <a href="#to-top" id="toTop" style="display: block;"> <span id="toTopHover" style="opacity: 1;"> </span></a>
</body>
</html>