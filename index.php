<!DOCTYPE html>
<html lang="en">
<head>
    <title>Agricultural Portal</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
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
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
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
        
        .login-container {
            margin-top: 220px;
            margin-bottom: 50px;
        }
        
        .card-deck {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            border-top: 4px solid #4caf50;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .nav-pills .nav-link {
            color: #2d3a3a;
            font-weight: 600;
            border-radius: 30px;
            margin: 0 5px;
            background: rgba(76, 175, 80, 0.15);
        }
        
        .nav-pills .nav-link.active {
            background: #4caf50;
            color: white;
        }
        
        .nav-pills .nav-link:hover:not(.active) {
            background: rgba(76, 175, 80, 0.3);
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
        
        .btn-info {
            background-color: #4caf50;
            border-color: #4caf50;
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: 600;
        }
        
        .btn-info:hover {
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
            position: fixed;
            bottom: 0;
            width: 100%;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .header-banner {
                height: 160px;
            }
            
            .banner-info h1 {
                font-size: 28px;
            }
            
            .login-container {
                margin-top: 190px;
            }
            
            .card-deck {
                width: 90% !important;
            }
        }
    </style>
</head>

<body>
    <!-- Header with background image -->
    <div class="header-banner">
        <div class="banner-info">
            <h1>Agriculture Portal</h1>
        </div>
        
        <div id="google_translate_element"></div>
    </div>

    <div class="login-container">
        <div class="card-deck">
            <div class="card shadow-lg p-3 mb-5 bg-white rounded">
                <div class="card-body text-center">
                    <ul class="nav nav-pills pl-2 pr-1 mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-farmer-tab" data-toggle="pill" href="#pills-farmer" role="tab"
                            aria-controls="pills-farmer" aria-selected="true">
                                <i class="fas fa-user-tie mr-1"></i> Farmer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-customer-tab" data-toggle="pill" href="#pills-customer" role="tab"
                            aria-controls="pills-customer" aria-selected="false">
                                <i class="fas fa-user mr-1"></i> Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-government-tab" data-toggle="pill" href="#pills-government" role="tab"
                            aria-controls="pills-government" aria-selected="false">
                                <i class="fas fa-landmark mr-1"></i> Government
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content pt-2 pl-1" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-farmer" role="tabpanel" aria-labelledby="pills-farmer-tab">
                            <form onsubmit="return validateFarmer()" method="POST" class="text-center border border-light p-4" action="php/login.php">
                                <p class="h4 mb-4">Sign in</p>
                                <input type="text" id="EmailId" class="form-control mb-4" placeholder="Email ID" name="farmer_email">
                                <input type="password" id="defaultLoginFormPassword" class="form-control mb-4" placeholder="Password" name="farmer_password">
                                <button class="btn btn-info btn-block my-4" type="submit" name="farmerlogin">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Sign in
                                </button>
                                <p>Not a member?
                                    <a href="php/farmer_regist.php">Register</a>
                                </p>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="pills-customer" role="tabpanel" aria-labelledby="pills-customer-tab">
                            <form onsubmit="return validateCustomer()" class="text-center border border-light p-4" action="php/login.php" method="POST">
                                <p class="h4 mb-4">Sign in</p>
                                <input type="text" id="defaultLoginFormEmail" class="form-control mb-4" placeholder="Email ID" name="cust_email">
                                <input type="password" id="defaultLoginFormPassword" class="form-control mb-4" placeholder="Password" name="cust_password">
                                <button class="btn btn-info btn-block my-4" type="submit" name="customerlogin">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Sign in
                                </button>
                                <p>Not a member?
                                    <a href="php/user_register.php">Register</a>
                                </p>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="pills-government" role="tabpanel" aria-labelledby="pills-government-tab">
                            <form class="text-center border border-light p-4" action="php/login.php" method="POST">
                                <p class="h4 mb-4">Sign in</p>
                                <input type="text" id="defaultLoginFormEmail" class="form-control mb-4" placeholder="Username" name="gov_username">
                                <input type="password" id="defaultLoginFormPassword" class="form-control mb-4" placeholder="Password" name="gov_password">
                                <button class="btn btn-info btn-block my-4" name="Govlogin" type="submit">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Sign in
                                </button>
                            </form>    
                        </div>
                    </div>     
                </div>
            </div>
        </div>
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
        
        function send_otp() {
            var email = jQuery('#EmailId').val();
            jQuery.ajax({
                url: 'send_otp.php',
                type: 'post',
                data: 'email=' + email,
                success: function(result) {
                    // Handle success response
                }
            });
        }
    </script>
</body>
</html>