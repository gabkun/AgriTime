<?php
date_default_timezone_set('Asia/Manila');
$currentTime = date('g:ia');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Face Detection on the Browser using JavaScript</title>

    <!-- Scripts -->
    <script defer src="js/face-api.min.js"></script>
    <script defer src="js/face-detection.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div>
        <h1>Time In: <?php echo $currentTime; ?> - PHT</h1>
    </div>
    <video id="video" width="600" height="450" autoplay muted></video>
</body>
</html>
