<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Crop Predictor</title>

<!-- Bootstrap & base CSS -->
<link rel="stylesheet" href="css/bootstrap.css"/>
<link rel="stylesheet" href="css/style.css"/>
<script src="js/jquery.min.js"></script>
<script src="js/crop_predict_location.js"></script>

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
    margin-top: 40px;
    font-family: 'Oswald', sans-serif;
}

.no-data {
    color: #e74c3c;
    font-weight: bold;
    text-align: center;
    margin-top: 15px;
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
    margin-top: 20px;
    border-left: 4px solid #4CAF50;
}

.results-container h3 {
    font-family: 'Merriweather', serif;
    color: #1a472a;
    font-size: 1.5rem;
    margin-bottom: 15px;
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

.crop-list {
    list-style-type: none;
    padding: 0;
}

.crop-list li {
    background: white;
    margin: 8px 0;
    padding: 12px 15px;
    border-radius: 6px;
    border-left: 4px solid #4CAF50;
    font-family: 'Oswald', sans-serif;
    font-size: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            <h1><a href="crop_predict.php">Crop Predictor</a></h1>
        </div>
    </div>
</div>
<!-- ================= HEADER END ================= -->

<!-- Save Indicator -->
<div class="save-indicator" id="saveIndicator">
    <i class="fas fa-check-circle"></i> Prediction completed successfully!
</div>

<!-- ================= FORM SECTION ================= -->
<div class="services">
    <h2>Select Your Location and Season</h2>
    <form role="form" action="#" method="post" enctype="multipart/form-data">
        <table class="table table-striped table-responsive-md btn-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>State Name</th>
                    <th>District Name</th>
                    <th>Season Name</th>
                    <th>Submit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>
                        <div class="form-group col-md-10 col-xs-10">
                            <select onchange="print_city('state', this.selectedIndex);" id="sts" name="stt" class="form-control" required></select>
                            <script language="javascript">print_state("sts");</script>
                        </div>
                    </td>
                    <td>
                        <div class="form-group col-md-10 col-xs-10">
                            <select id="state" name="district" class="form-control" required></select>
                        </div>
                    </td>
                    <td>
                        <div class="form-group col-md-10 col-xs-10">
                            <select name="Season" class="form-control" required>
                                <option value="">Select Season...</option>
                                <option value="Kharif">Kharif</option>
                                <option value="Whole Year">Whole Year</option>
                                <option value="Autumn">Autumn</option>
                                <option value="Rabi">Rabi</option>
                                <option value="Summer">Summer</option>
                                <option value="Winter">Winter</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <center>
                            <div class="form-group col-md-10 col-xs-10">
                                <button type="submit" name="Crop_Predict" class="btn btn-success btn-submit">
                                    <i class="fas fa-seedling"></i> Predict
                                </button>
                            </div>
                        </center>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

<!-- ================= RESULTS SECTION ================= -->
<div class="services">
    <h2>Crops Predicted:</h2>
    <div class="row">
        <div class="container-fluid">
            <div class="results-container">
            <?php
            if (isset($_POST['Crop_Predict'])) {
                $state  = trim($_POST['stt']);
                $district = trim($_POST['district']);
                $season = trim($_POST['Season']);

                echo "<h3>Crops grown in <b>" . htmlspecialchars($district) . "</b> during the <b>" . htmlspecialchars($season) . "</b> season are:<br><br></h3>";

                $python = "C:\\Program Files\\Python312\\python.exe";
                $script = "C:\\xampp\\htdocs\\Agriculture-Portal\\MLdecisiontree\\ZDecision_Tree_Model_Call.py";
                $command = escapeshellarg($python) . " " .
                           escapeshellarg($script) . " " .
                           escapeshellarg($state)  . " " .
                           escapeshellarg($district) . " " .
                           escapeshellarg($season);

                $output = shell_exec($command . " 2>&1");
                
                // Process the output to remove percentages
                if (!empty($output)) {
                    // Split the output by lines
                    $lines = explode("\n", $output);
                    $clean_output = "";
                    
                    foreach ($lines as $line) {
                        // Remove percentage patterns like (85.71%) or [95%] or any numbers with % sign
                        $clean_line = preg_replace('/\s*\([^)]*\%\)/', '', $line); // Remove (85.71%)
                        $clean_line = preg_replace('/\s*\[[^\]]*\%\]/', '', $clean_line); // Remove [95%]
                        $clean_line = preg_replace('/\s*\d+\.?\d*\%/', '', $clean_line); // Remove any 85.71% pattern
                        $clean_line = preg_replace('/\s*accuracy\s*:?\s*\d+\.?\d*\%/i', '', $clean_line); // Remove accuracy percentages
                          $clean_line = preg_replace('/^:/', '', $clean_line); // Remove colon at start
        $clean_line = preg_replace('/:$/', '', $clean_line); // Remove colon at end
        $clean_line = preg_replace('/\s*:\s*/', ' ', $clean_line); // Replace colons in middle 
                        // Only add non-empty lines
                        if (!empty(trim($clean_line))) {
                            $clean_output .= $clean_line . "\n";
                        }
                    }
                    
                    // Format as a clean list without percentages
                    if (!empty($clean_output)) {
                        echo "<ul class='crop-list'>";
                        $crops = array_filter(explode("\n", trim($clean_output)));
                        foreach ($crops as $crop) {
                            $crop = trim($crop);
                            // Skip lines that contain technical terms or are too short
                            if (!empty($crop) && strlen($crop) > 2 && 
                                !preg_match('/(accuracy|precision|recall|f1-score|support|macro|weighted|classification|report)/i', $crop)) {
                                echo "<li>" . htmlspecialchars($crop) . "</li>";
                            }
                        }
                        echo "</ul>";
                    } else {
                        echo "<p class='no-data'>No crop predictions available for the selected criteria.</p>";
                    }
                } else {
                    echo "<p class='no-data'>No output received from prediction model.</p>";
                }
                
                // Show save indicator after prediction
                echo "<script>document.getElementById('saveIndicator').style.display = 'block'; setTimeout(function() { document.getElementById('saveIndicator').style.display = 'none'; }, 3000);</script>";
            }
            ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= FOOTER ================= -->
<div class="footer">
    <p>&copy; Agriculture Portal. All Rights Reserved.</p>
    <p class="text-muted">Empowering farmers through technology</p>
</div>

<!-- scroll-to-top -->
<script type="text/javascript">
    $(document).ready(function() {
        $().UItoTop({ easingType: 'easeOutQuart' });
    });
</script>
<a href="#to-top" id="toTop" style="display:block;"> <span id="toTopHover" style="opacity:1;"></span></a>

<!-- Google translate -->
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>