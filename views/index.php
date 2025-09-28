<?php
date_default_timezone_set('Asia/Manila');
$currentTime = date('g:ia');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - AgriTime Payroll Attendance System</title>

  <!-- Include face-api and your detection script -->
  <script defer src="js/face-api.min.js"></script>
  <script defer src="js/loadFaceDetection.js"></script>

  <link rel="stylesheet" href="styles/login.css">
</head>
<body>

  <!-- Left Section (Facial Recognition Login) -->
  <div class="left">
    <h2>Login</h2>
    <p>Welcome Back<br>Please Scan Face for Login</p>

    <!-- Camera Preview -->
    <video id="video" width="600" height="450" autoplay></video>

    <!-- Hidden form to submit detected face -->
    <form method="POST" id="faceForm">
      <input type="hidden" name="faceImage" id="faceImage">
      <button type="button" onclick="captureFace()">Scan Face</button>
    </form>

    <div class="note">
      Don’t you have an account? <a href="Registration.php">Register here</a>
    </div>
  </div>

  <!-- Right Section (Logo + System Name) -->
  <div class="right">
    <img src="Agri.jpg" alt="System Logo">
    <h1>AgriTime Payroll<br>Attendance System</h1>
  </div>

  <script>
    window.addEventListener("DOMContentLoaded", async () => {
      const video = document.getElementById("video");

      // Add your trained labels (faces stored in "labels" folder)
      const labels = ["raphael", "BRAGANZA1", "MALANDAY1"];

      // Initialize face detection using loadFaceDetection.js
      await loadFaceDetection(video, labels, {
        modelsPath: "models",  // folder where face-api models are stored
        imagesPath: "labels",  // folder where known face images are stored
        onDetect: (label, detection) => {
          console.log("Detected:", label);

          // When a known face is detected, auto-submit login
          if (label) {
            alert(`Welcome back, ${label}!`);
            document.getElementById("faceImage").value = label;
            document.getElementById("faceForm").submit();
          }
        }
      });
    });

    // Optional manual capture (if you want button control)
    function captureFace() {
      alert("Please face the camera to start scanning...");
    }
  </script>

</body>
</html>
