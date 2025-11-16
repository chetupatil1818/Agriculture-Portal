<?php

// No output before this PHP tag — avoids "unexpected '<'" parse errors.

// Configuration
$apiKey = 'cfa89b96e4fe4eef951a9e65eef5e906'; // replace if needed
$query = urlencode('India farming OR agriculture OR government schemes');
$url = "https://newsapi.org/v2/everything?q={$query}&language=en&apiKey={$apiKey}";

// Fetch from NewsAPI
$newsdata = null;
$curlError = null;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: Agriculture-Portal/1.0']);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    $curlError = curl_error($ch);
} else {
    $newsdata = json_decode($response);
}
curl_close($ch);

// Prepare sections
$trendingNews = [];
$globalNews = [];
$newsForYou = [];
$apiError = null;
if ($curlError) {
    $apiError = "cURL error: " . $curlError;
} elseif (!is_object($newsdata)) {
    $apiError = "Invalid API response (not JSON).";
} elseif (isset($newsdata->status) && $newsdata->status !== 'ok') {
    $apiError = "API error: " . ($newsdata->message ?? 'Unknown error');
} elseif (isset($newsdata->articles) && is_array($newsdata->articles)) {
    foreach ($newsdata->articles as $article) {
        $title = $article->title ?? '';
        // simple categorization
        if (stripos($title, 'global') !== false) {
            $globalNews[] = $article;
        } elseif (stripos($title, 'farm') !== false || stripos($title, 'agri') !== false || stripos($title, 'scheme') !== false) {
            $newsForYou[] = $article;
        } else {
            $trendingNews[] = $article;
        }
    }
} else {
    $apiError = "No articles returned by API.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Agriculture News Feed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="css/bootstrap.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    <script src="js/jquery.min.js"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .menu { 
            text-align: center; 
            margin: 30px 0; 
        }
        .menu button { 
            background: #4caf50; 
            color: #fff; 
            border: none; 
            padding: 12px 24px; 
            margin: 8px; 
            border-radius: 30px; 
            cursor: pointer;
            font-family: 'Nunito', sans-serif;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .menu button:hover {
            background: #388e3c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .news-section { 
            margin: 24px auto; 
            max-width: 1100px; 
            display: none; 
        }
        .news-section.active { 
            display: block; 
        }
        .news-row { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 20px; 
            justify-content: center;
        }
        .news-card { 
            background: #fff; 
            border-radius: 10px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); 
            padding: 20px; 
            flex: 1 1 30%; 
            min-width: 260px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
            transition: transform 0.3s, box-shadow 0.3s;
            border-top: 4px solid #4caf50;
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .news-title { 
            font-size: 18px; 
            color: #1b5e20; 
            margin-bottom: 12px;
            font-weight: 700;
            font-family: 'Merriweather', serif;
        }
        .news-description { 
            font-size: 15px; 
            color: #546e7a; 
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .no-news, .api-error { 
            max-width: 900px; 
            margin: 24px auto; 
            padding: 20px; 
            border-radius: 8px; 
            text-align: center;
            font-size: 16px;
        }
        .no-news { 
            background: #fff8e1; 
            color: #8d6e00; 
            border: 1px solid #ffecb3; 
        }
        .api-error { 
            background: #ffebee; 
            color: #c62828; 
            border: 1px solid #ffcdd2; 
        }
        .iframe-container { 
            width: 100%; 
            height: 600px; 
            border: none; 
            display: none; 
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .footer { 
            text-align: center; 
            padding: 20px; 
            color: #fff; 
            background: linear-gradient(to right, #2e7d32, #4caf50);
            margin-top: 40px;
            font-weight: 600;
        }
        h2 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 25px;
            font-family: 'Merriweather', serif;
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }
        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: #4caf50;
            border-radius: 3px;
        }
        small {
            color: #78909c;
            font-size: 13px;
        }
        a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        a:hover {
            color: #1b5e20;
            text-decoration: underline;
        }
        #google_translate_element {
            position: absolute;
            right: 20px;
            top: 20px;
            z-index: 10;
        }
        @media (max-width: 800px) { 
            .news-card { 
                flex: 1 1 100%; 
            }
            .menu button {
                padding: 10px 18px;
                font-size: 14px;
                margin: 5px;
            }
            .top-left-menu ul {
                flex-direction: column;
                gap: 10px;
            }
            .banner-info h1 {
                font-size: 32px;
            }
        }
    </style>
    <script>
        function showSection(cls){
            document.querySelectorAll('.news-section').forEach(el=>el.classList.remove('active'));
            document.querySelectorAll('.iframe-container').forEach(el=>el.style.display='none');
            let sec = document.querySelector('.news-section.'+cls);
            if(sec) sec.classList.add('active');
        }
        function showExternal(){
            document.querySelectorAll('.news-section').forEach(el=>el.classList.remove('active'));
            document.querySelector('.iframe-container').style.display = 'block';
        }
        document.addEventListener('DOMContentLoaded', function(){
            // default show trending if present, else news-for-you, else global
            if(document.querySelector('.news-section.trending')) showSection('trending');
            else if(document.querySelector('.news-section.news-for-you')) showSection('news-for-you');
            else if(document.querySelector('.news-section.global')) showSection('global');
        });
    </script>
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
            <h1>Agriculture News Feed</h1>
        </div>
    </div>

    <div class="menu">
        <button onclick="showSection('trending')"><i class="fas fa-fire"></i> Trending News</button>
        <button onclick="showSection('global')"><i class="fas fa-globe"></i> Global News</button>
        <button onclick="showSection('news-for-you')"><i class="fas fa-user"></i> News for You</button>
        <button onclick="showExternal()"><i class="fas fa-external-link-alt"></i> External Website</button>
    </div>

    <iframe class="iframe-container" src="https://www.agrinewsnetwork.in/" title="Agri News Network"></iframe>

    <?php if ($apiError): ?>
        <div class="api-error"><?php echo htmlspecialchars($apiError); ?></div>
    <?php else: ?>

        <?php if (count($trendingNews) === 0 && count($globalNews) === 0 && count($newsForYou) === 0): ?>
            <div class="no-news">No news articles found. Try again later or check your API key / network connection.</div>
        <?php else: ?>

            <div class="news-section trending">
                <h2>Trending News</h2>
                <div class="news-row">
                    <?php if (count($trendingNews) > 0): ?>
                        <?php foreach ($trendingNews as $n): ?>
                            <div class="news-card">
                                <div>
                                    <div class="news-title"><?php echo htmlspecialchars($n->title ?? ''); ?></div>
                                    <div class="news-description"><?php echo htmlspecialchars($n->description ?? ''); ?></div>
                                </div>
                                <div>
                                    <small><?php echo htmlspecialchars($n->source->name ?? ''); ?> &nbsp;|&nbsp; <?php echo date('d M Y H:i', strtotime($n->publishedAt ?? '')); ?></small>
                                    <div style="margin-top:12px;"><a href="<?php echo htmlspecialchars($n->url ?? '#'); ?>" target="_blank">Read more</a></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-news">No trending news articles found.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="news-section global">
                <h2>Global News</h2>
                <div class="news-row">
                    <?php if (count($globalNews) > 0): ?>
                        <?php foreach ($globalNews as $n): ?>
                            <div class="news-card">
                                <div>
                                    <div class="news-title"><?php echo htmlspecialchars($n->title ?? ''); ?></div>
                                    <div class="news-description"><?php echo htmlspecialchars($n->description ?? ''); ?></div>
                                </div>
                                <div>
                                    <small><?php echo htmlspecialchars($n->source->name ?? ''); ?> &nbsp;|&nbsp; <?php echo date('d M Y H:i', strtotime($n->publishedAt ?? '')); ?></small>
                                    <div style="margin-top:12px;"><a href="<?php echo htmlspecialchars($n->url ?? '#'); ?>" target="_blank">Read more</a></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-news">No global news articles found.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="news-section news-for-you">
                <h2>News for You</h2>
                <div class="news-row">
                    <?php if (count($newsForYou) > 0): ?>
                        <?php foreach ($newsForYou as $n): ?>
                            <div class="news-card">
                                <div>
                                    <div class="news-title"><?php echo htmlspecialchars($n->title ?? ''); ?></div>
                                    <div class="news-description"><?php echo htmlspecialchars($n->description ?? ''); ?></div>
                                </div>
                                <div>
                                    <small><?php echo htmlspecialchars($n->source->name ?? ''); ?> &nbsp;|&nbsp; <?php echo date('d M Y H:i', strtotime($n->publishedAt ?? '')); ?></small>
                                    <div style="margin-top:12px;"><a href="<?php echo htmlspecialchars($n->url ?? '#'); ?>" target="_blank">Read more</a></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-news">No personalized news articles found.</div>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; ?>
    <?php endif; ?>

    <div class="footer">
        <p>Agriculture Portal</p>
    </div>

    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage:'en',
            includedLanguages:'bn,en,gu,hi,kn,mr,ta,te'
        },'google_translate_element');
    }
    </script>
</body>
</html>