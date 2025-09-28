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
    <!-- <script defer src="js/face-detection.js"></script>  -->
    <script defer src="js/loadFaceDetection.js"></script>
    <!-- Styles -->
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
 
   <video id="video" width="600" height="450" autoplay></video>
    <script>
        window.addEventListener("DOMContentLoaded", async () => {
            const video = document.getElementById("video");
            const labels = ["raphael", "BRAGANZA1", "MALANDAY1"];
            
            await loadFaceDetection(video, labels, {
                modelsPath: "models",
                imagesPath: "labels",
                onDetect: (label, detection) => {
                    console.log("Detected:", label, detection);
                }
            });
        });
</script>
   
</body>
</html>
