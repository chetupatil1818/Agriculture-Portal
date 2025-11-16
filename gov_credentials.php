<?php 
session_start();
ini_set('memory_limit', '-1');
$userlogin=$_SESSION['Gov_user'];
$servername="localhost";
$username="root";
$password="";
$dbname="agriculture_portal";

// Create Connection 
$conn =mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function encrypt($message, $encryption_key){
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
    return base64_encode($nonce.$ciphertext);
}

function decrypt($message, $encryption_key) {
    $key = hex2bin($encryption_key);
    $message = base64_decode($message);

    $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
    $nonce = mb_substr($message, 0, $nonceSize, '8bit');

    if (strlen($nonce) < 16) {
        $nonce = str_pad($nonce, 16, "\0");
    }

    $ciphertext = mb_substr($message, $nonceSize, null, '8bit');

    return openssl_decrypt(
        $ciphertext,
        'aes-256-ctr',
        $key,
        OPENSSL_RAW_DATA,
        $nonce
    );
}

$pk="1a851867be761f7725cacf7467a184d3";

$sql = "SELECT farmer_name, farmer_id, F_gender, email, phone_no, F_birthday, F_State, F_District, F_AadharNo FROM farmerlogin;";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Agriculture Portal - Farmer Credentials</title>

<!-- Bootstrap CSS -->
<link href="css/bootstrap.css" rel='stylesheet' type='text/css' />

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- jQuery -->
<script src="js/jquery.min.js"></script>

<!-- Custom Theme files -->
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />

<!-- Google Fonts -->
<link href='https://fonts.googleapis.com/css?family=Oswald:400,300,700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Niconne' rel='stylesheet' type='text/css'>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<!-- Table sorting and filtering -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap4.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap4.min.js"></script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
    color: #333;
    min-height: 100vh;
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

.footer {
    background: #333;
    color: #fff;
    text-align: center;
    padding: 20px 0;
    margin-top: 40px;
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

/* Main content styling */
.container {
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 15px;
}

.panel {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    margin-bottom: 30px;
    border-left: 5px solid #4CAF50;
}

.panel-heading {
    background: #e8f5ee;
    color: #1a472a;
    padding: 20px;
    font-family: 'Oswald', serif;
    font-weight: 700;
    border-bottom: 2px solid #e8f5ee;
    font-size: 20px;
}

.panel-body {
    padding: 25px;
}

.table-container {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-family: 'Roboto', sans-serif;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.table th, .table td {
    padding: 15px;
    text-align: center;
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

.table th {
    background-color: #4CAF50;
    color: white;
    font-weight: 600;
    font-size: 16px;
    position: sticky;
    top: 0;
}

.table td {
    font-size: 15px;
}

.table tr:nth-child(even) {
    background-color: #f8f9fa;
}

.table tr:hover {
    background-color: #e9ecef;
}

.dataTables_wrapper {
    margin-top: 20px;
}

.dataTables_filter input {
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 6px 12px;
    margin-left: 10px;
}

.dataTables_length select {
    border-radius: 4px;
    border: 1px solid #ced4da;
    padding: 6px 12px;
    margin: 0 10px;
}

.page-item.active .page-link {
    background-color: #4CAF50;
    border-color: #4CAF50;
}

.page-link {
    color: #4CAF50;
}

.page-link:hover {
    color: #3d8b40;
}

/* Card view for mobile */
.card-view {
    display: none;
}

@media (max-width: 768px) {
    .table-container {
        display: none;
    }
    
    .card-view {
        display: block;
    }
    
    .farmer-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 15px;
        border-left: 4px solid #4CAF50;
    }
    
    .farmer-card h3 {
        color: #4CAF50;
        margin-bottom: 10px;
        font-family: 'Oswald', sans-serif;
    }
    
    .farmer-detail {
        margin-bottom: 8px;
        display: flex;
    }
    
    .farmer-detail label {
        font-weight: 600;
        min-width: 120px;
        color: #555;
    }
    
    .farmer-detail span {
        flex: 1;
    }
}

/* Export buttons */
.export-buttons {
    margin-bottom: 20px;
    text-align: right;
}

.btn-export {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    margin-left: 10px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-export:hover {
    background-color: #218838;
}

/* Stats summary */
.stats-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    flex: 1;
    min-width: 200px;
    background: white;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card h3 {
    color: #4CAF50;
    margin-bottom: 10px;
    font-family: 'Oswald', sans-serif;
}

.stat-card .number {
    font-size: 24px;
    font-weight: 700;
    color: #333;
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
                <h1><a href="gov_credentials.php">Farmer's Credentials</a></h1>
            </div>
        </div>
    </div>
    <!-- header-section-ends -->    

    <div class="container">
        <div class="panel">
            <div class="panel-heading">Farmer Credentials Database</div>
            <div class="panel-body">
                <?php
                // Count total farmers
                $count_query = "SELECT COUNT(*) as total FROM farmerlogin";
                $count_result = mysqli_query($conn, $count_query);
                $total_farmers = $count_result->fetch_assoc()['total'];
                ?>
                
                <div class="stats-summary">
                    <div class="stat-card">
                        <h3>Total Farmers</h3>
                        <div class="number"><?php echo $total_farmers; ?></div>
                    </div>
                </div>
                
                <div class="export-buttons">
                    <button class="btn-export" onclick="exportTableToCSV('farmers_data.csv')">
                        <i class="fas fa-download"></i> Export to CSV
                    </button>
                    <button class="btn-export" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
                
                <div class="table-container">
                    <table id="farmersTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Farmer Name</th>
                                <th>Gender</th>
                                <th>Email ID</th>
                                <th>Phone no.</th>
                                <th>Aadhar Card No.</th>
                                <th>Date of Birth</th>
                                <th>State</th>
                                <th>District</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php  
                            while($row = $result->fetch_assoc()) {
                                $x = ucfirst(decrypt($row["farmer_name"], $pk));
                                $y = $row["farmer_id"];
                                $gen = $row["F_gender"];
                                $gen = decrypt($gen, $pk);
                                $email = $row["email"];
                                $phone = $row["phone_no"];
                                $phone = decrypt($phone, $pk);
                                $aadhar = $row["F_AadharNo"];
                                $aadhar = decrypt($aadhar, $pk);
                                $dob = $row["F_birthday"];
                                $dob = decrypt($dob, $pk);
                                $state = $row["F_State"];
                                $state = decrypt($state, $pk);
                                $district = $row["F_District"];
                                $district = decrypt($district, $pk);

                                echo "<tr>";
                                echo "<td>$y</td>";
                                echo "<td>$x</td>";
                                echo "<td>$gen</td>";
                                echo "<td>$email</td>";
                                echo "<td>$phone</td>";
                                echo "<td>$aadhar</td>";
                                echo "<td>$dob</td>";
                                echo "<td>$state</td>";
                                echo "<td>$district</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Card view for mobile -->
                <div class="card-view">
                    <?php
                    // Reset pointer and loop again for card view
                    mysqli_data_seek($result, 0);
                    while($row = $result->fetch_assoc()) {
                        $x = ucfirst(decrypt($row["farmer_name"], $pk));
                        $y = $row["farmer_id"];
                        $gen = $row["F_gender"];
                        $gen = decrypt($gen, $pk);
                        $email = $row["email"];
                        $phone = $row["phone_no"];
                        $phone = decrypt($phone, $pk);
                        $aadhar = $row["F_AadharNo"];
                        $aadhar = decrypt($aadhar, $pk);
                        $dob = $row["F_birthday"];
                        $dob = decrypt($dob, $pk);
                        $state = $row["F_State"];
                        $state = decrypt($state, $pk);
                        $district = $row["F_District"];
                        $district = decrypt($district, $pk);
                    ?>
                    <div class="farmer-card">
                        <h3><?php echo $x; ?> (ID: <?php echo $y; ?>)</h3>
                        <div class="farmer-detail">
                            <label>Gender:</label>
                            <span><?php echo $gen; ?></span>
                        </div>
                        <div class="farmer-detail">
                            <label>Email:</label>
                            <span><?php echo $email; ?></span>
                        </div>
                        <div class="farmer-detail">
                            <label>Phone:</label>
                            <span><?php echo $phone; ?></span>
                        </div>
                        <div class="farmer-detail">
                            <label>Aadhar:</label>
                            <span><?php echo $aadhar; ?></span>
                        </div>
                        <div class="farmer-detail">
                            <label>DOB:</label>
                            <span><?php echo $dob; ?></span>
                        </div>
                        <div class="farmer-detail">
                            <label>State:</label>
                            <span><?php echo $state; ?></span>
                        </div>
                        <div class="farmer-detail">
                            <label>District:</label>
                            <span><?php echo $district; ?></span>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- footer-section -->
    <div class="footer">
        <div class="container">
            <div class="copyright text-center">
                <p>&copy; Agriculture Portal. All rights reserved.</p>
                <p class="text-muted">Empowering farmers through technology</p>
            </div>
        </div>
    </div>
    <!-- footer-section -->

    <script>
    $(document).ready(function() {
        $('#farmersTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "responsive": true,
            "dom": '<"top"lf>rt<"bottom"ip><"clear">',
            "language": {
                "search": "Filter:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "&laquo;",
                    "next": "&raquo;"
                }
            }
        });
    });
    
    function downloadCSV(csv, filename) {
        var csvFile;
        var downloadLink;
        
        // CSV file
        csvFile = new Blob([csv], {type: "text/csv"});
        
        // Download link
        downloadLink = document.createElement("a");
        
        // File name
        downloadLink.download = filename;
        
        // Create a link to the file
        downloadLink.href = window.URL.createObjectURL(csvFile);
        
        // Hide download link
        downloadLink.style.display = "none";
        
        // Add the link to DOM
        document.body.appendChild(downloadLink);
        
        // Click download link
        downloadLink.click();
    }
    
    function exportTableToCSV(filename) {
        var csv = [];
        var rows = document.querySelectorAll("table tr");
        
        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll("td, th");
            
            for (var j = 0; j < cols.length; j++) 
                row.push('"' + cols[j].innerText + '"');
            
            csv.push(row.join(","));        
        }
        
        // Download CSV file
        downloadCSV(csv.join("\n"), filename);
    }
    </script>

    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>