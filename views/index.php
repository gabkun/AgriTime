<?php
date_default_timezone_set('Asia/Manila');
$currentTime = date('g:ia');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - AgriTime Payroll Attendance System</title>

  <!-- Include face-api and detection script -->
  <script defer src="js/face-api.min.js"></script>
  <script defer src="js/loadFaceDetection.js"></script>

  <link rel="stylesheet" href="styles/login.css">
</head>
<body>

  <!-- Left Section (Facial Recognition Login) -->
  <div class="left">
    <h2>Login</h2>
    <p>Welcome Back<br>Please Scan Face for Login</p>

    <!-- Video + Canvas container -->
    <div id="video-container">
     <video id="video" autoplay muted playsinline></video>
    </div>

    <!-- Hidden form to submit detected face -->
    <form method="POST" id="faceForm">
      <input type="hidden" name="faceImage" id="faceImage" />
      <button type="button" id="scanBtn" onclick="captureFace()">Scan Face</button>
    </form>

    <!-- Feedback container for welcome message + spinner -->
    <div id="login-feedback" style="display:none; align-items:center; margin-top:15px; gap:10px; font-weight:600; font-size:1.2rem; color:#2a9d8f;">
      <div class="loader"></div>
      <div id="welcome-message"></div>
    </div>

    <div class="note">
      Don’t you have an account? <a href="Registration.php">Register here</a>
    </div>
  </div>

  <!-- Right Section (Logo + System Name) -->
  <div class="right">
    <img src="Agri.jpg" alt="System Logo" />
    <h1>AgriTime Payroll<br>Attendance System</h1>
  </div>


  <script>
    const video = document.getElementById("video");

    // Start the camera as soon as page loads
    window.addEventListener("DOMContentLoaded", async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
      } catch (err) {
        console.error("Camera access denied or error:", err);
        alert("Unable to access the camera.");
      }
    });

    // Trigger face detection only on button click
    async function captureFace() {
      alert("Please face the camera to start scanning...");

      const labels = ["raphael", "BRAGANZA1", "MALANDAY1"];

      await loadFaceDetection(video, labels, {
        modelsPath: "models",
        imagesPath: "labels",
        onDetect: (label, detection) => {
          console.log("Detected:", label);
          if (label) {
            // Hide scan button
            document.getElementById("scanBtn").style.display = "none";

            const feedback = document.getElementById("login-feedback");
            const messageEl = document.getElementById("welcome-message");
            let countdown = 5;

            // Show feedback container
            feedback.style.display = "flex";

            // Set hidden input value
            document.getElementById("faceImage").value = label;

            // Start countdown
            messageEl.textContent = `Welcome back, ${label}! Logging you in ${countdown}...`;
            const intervalId = setInterval(() => {
              countdown--;
              if (countdown > 0) {
                messageEl.textContent = `Welcome back, ${label}! Logging you in ${countdown}...`;
              } else {
                clearInterval(intervalId);
                document.getElementById("faceForm").submit();
              }
            }, 1000);
          }
        }
      });
    }
  </script>

  <style>
   #video-container {
      position: relative;
      width: 600px;
      height: 450px;
    }

    #video {
      width: 100%;
      height: 100%;
      display: block;
      border: 2px solid #ccc;
      z-index: 1;
    }

    #video-container canvas {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 2;
      pointer-events: none;
    }

   .loader {
    border: 6px solid #f3f3f3;
    border-top: 6px solid #2a9d8f;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  </style>

</body>
</html>
