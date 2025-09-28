<?php
// Simple PHP script to display current date and time
date_default_timezone_set("Asia/Manila");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agritime Attendance System - Registration</title>
    <script defer src="js/face-api.min.js"></script>
    <script defer src="js/faceRegistration.js"></script>
</head>
<body>
    <h1>Time In: <?php echo date("g:i a") . " - PHT"; ?></h1>
    <div>
        <label for="lastname">Last Name:</label>
        <input type="text" id="lastname" name="lastname" />
        <button id="registerBtn">Register Face</button>
    </div>
    <video id="video" width="600" height="450" autoplay></video>
    <script>
        document.getElementById('registerBtn').addEventListener('click', async () => {
            const lastName = document.getElementById('lastname').value.trim();
            const video = document.getElementById('video');
            await faceRegistration(lastName, video);
        });
    </script>
</body>
</html>