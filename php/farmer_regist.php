<?php
session_start();
$servername="localhost";
$username="root";
$password="";
$dbname="agriculture_portal";
$conn = mysqli_connect($servername, $username, $password, $dbname);

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
  function decrypt($message,$encryption_key){
    $key = hex2bin($encryption_key);
    $message = base64_decode($message);
    $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
    $nonce = mb_substr($message, 0, $nonceSize, '8bit');
    $ciphertext = mb_substr($message, $nonceSize, null, '8bit');
    $plaintext= openssl_decrypt(
      $ciphertext, 
      'aes-256-ctr', 
      $key,
      OPENSSL_RAW_DATA,
      $nonce
    );
    return $plaintext;
  }

$pk="1a851867be761f7725cacf7467a184d3";
//echo("connection");
if(isset($_POST ['signupbt'])) {
  $farmername=$_POST['farmername'];
  $farmername=encrypt($farmername,$pk);
  $pass=$_POST['password1'];
  $pass=SHA1($pass);
  $email=$_POST['email'];
  $email=encrypt($email,$pk);
  $email=decrypt($email,$pk);
  $phone=$_POST['phoneno'];
  $phone=encrypt($phone,$pk);
  $aadhar=$_POST['aadharno'];
  $aadhar=encrypt($aadhar,$pk);
 
  $insertquery = "INSERT INTO `farmerlogin`(`farmer_name`, `password`, `email`, `phone_no`,`F_AadharNo`) 
  VALUES ('$farmername','$pass','$email','$phone','$aadhar');";
  $result = mysqli_query($conn, $insertquery);
  header("location: ../index.php");
}
?>
<!DOCTYPE HTML>
<HTML lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Farmer Registration</title>
    
    <!-- Bootstrap & jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Translate -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    
    <script src="../js/RFarmer.js"></script>
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Nunito', sans-serif;
            color: #2d3a3a;
            margin: 0;
            padding: 0;
        }
        
        .header-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80') no-repeat center center;
            background-size: cover;
            color: #fff;
            height: 180px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .banner-info h1 {
            font-family: 'Merriweather', serif;
            font-size: 36px;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin: 0;
        }
        
        #google_translate_element {
            position: absolute;
            right: 20px;
            top: 20px;
            z-index: 1010;
        }
        
        .registration-container {
            margin-bottom: 50px;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            border-top: 4px solid #4caf50;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .form-control {
            border-radius: 30px;
            padding: 12px 20px;
            border: 1px solid #ced4da;
        }
        
        .form-control:focus {
            border-color: #4caf50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        
        .btn-success {
            background-color: #4caf50;
            border-color: #4caf50;
            border-radius: 30px;
            padding: 12px 20px;
            font-weight: 600;
        }
        
        .btn-success:hover {
            background-color: #388e3c;
            border-color: #388e3c;
            transform: translateY(-2px);
        }
        
        .h4 {
            font-family: 'Merriweather', serif;
            color: #2e7d32;
            font-weight: 700;
            margin-bottom: 1.5rem !important;
        }
        
        a {
            color: #2e7d32;
            font-weight: 600;
        }
        
        a:hover {
            color: #1b5e20;
            text-decoration: none;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #fff;
            background: linear-gradient(to right, #2e7d32, #4caf50);
            font-weight: 600;
            margin-top: 30px;
        }
        
        .form-text {
            font-size: 0.85rem;
        }
        
        hr {
            border-top: 1px solid rgba(76, 175, 80, 0.3);
        }
        
        @media (max-width: 768px) {
            .header-banner {
                height: 160px;
            }
            
            .banner-info h1 {
                font-size: 28px;
            }
            
            .card {
                width: 90% !important;
            }
        }
    </style>
</head>

<body>
    <!-- Header with background image -->
    <div class="header-banner">
        <div class="banner-info">
            <h1>Farmer Registration</h1>
        </div>
        
        <div id="google_translate_element"></div>
    </div>

    <div class="registration-container">
        <center>
            <div class="card" style="width:600px">
                <form onsubmit="return newfarmer()" method="POST" class="text-center border border-light p-5" action="farmer_regist.php">
                    <p class="h4 mb-4">
                        <i class="fas fa-user-plus mr-2"></i> Farmer Sign up
                    </p>

                    <div class="form-row mb-4">
                        <div class="col">
                            <!-- Farmer name -->
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" id="FirstName" class="form-control" placeholder="Farmer name" name="farmername">
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" id="Password" class="form-control" placeholder="Password" name="password1">
                    </div>
                    <small id="PasswordHelpBlock" class="form-text text-muted mb-4">
                        Password Length should be minimum 8 characters and maximum 20 characters.
                    </small>

                    <!-- Confirm Password -->
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" id="confirm_pass" class="form-control" placeholder="Confirm Password" name="confirm_pass">
                    </div>

                    <!-- E-mail -->
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" id="Email" class="form-control" name="email" placeholder="E-mail">
                    </div>

                    <!-- Phone number -->
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        </div>
                        <input type="text" id="defaultRegisterPhonePassword" class="form-control" placeholder="Phone number" name="phoneno">
                    </div>

                    <!-- Aadhar Card No -->
                    <div class="input-group mb-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        </div>
                        <input type="text" id="Aadhar" class="form-control" placeholder="Aadhar Card No." name="aadharno">
                    </div>

                    <!-- Sign up button -->
                    <button class="btn btn-success my-4 btn-block" type="submit" name="signupbt" value="signupbt">
                        <i class="fas fa-user-plus mr-2"></i> Sign up
                    </button>

                    <hr>

                    <p>Already have an account?
                        <a href="../index.php" style="color: #2e7d32;">Sign in here</a>
                    </p>
                </form>
            </div>
        </center>
    </div>

    <div class="footer">
        <p>Agriculture Portal</p>
    </div>

    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'bn,en,gu,hi,kn,mr,ta,te'
            }, 'google_translate_element');
        }
    </script>
</body>
</HTML>