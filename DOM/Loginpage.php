<?php
// login.php
session_start();

// If user already logged in, redirect
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission (when face image is posted)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // For now, just simulate success
    // Later, you will match the captured face image with the DB record
    $_SESSION['user'] = "Jane Cooper";  
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
    }
    .left {
      flex: 1;
      background: #f0f4f9;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 40px;
    }
    .left h2 { font-size: 28px; margin-bottom: 10px; }
    .left p { color: #555; margin-bottom: 20px; }
    video {
      width: 300px;
      height: 220px;
      border: 3px solid #333;
      border-radius: 10px;
      object-fit: cover;
    }
    button {
      margin-top: 15px;
      padding: 10px 20px;
      background: #16a34a; color: #fff;
      border: none; border-radius: 5px; cursor: pointer;
    }
    button:hover { background: #15803d; }
    .note {
      margin-top: 20px;
      font-size: 14px;
      text-align: center;
    }
    .note a { color: #2563eb; text-decoration: none; }
    .note a:hover { text-decoration: underline; }
    .right {
      flex: 1;
      background: linear-gradient(135deg, #a7f3d0, #4ade80);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: white;
      padding: 20px;
    }
    .right img {
      width: 120px; height: 120px; border-radius: 20px; margin-bottom: 20px;
    }
    .right h1 {
      font-size: 26px; font-weight: bold; text-align: center;
    }
  </style>
</head>
<body>

  <!-- Left (Facial Recognition Login) -->
  <div class="left">
    <h2>Login</h2>
    <p>Welcome Back<br>Please Scan Face for Login</p>

    <!-- Camera Preview -->
    <video id="camera" autoplay></video>

    <!-- Hidden form to submit face image -->
    <form method="POST" id="faceForm">
      <input type="hidden" name="faceImage" id="faceImage">
      <button type="button" onclick="captureFace()">Scan Face</button>
    </form>

    <div class="note">
      Don’t you have an account? <a href="Registration.php">Register here</a>
    </div>
  </div>

  <!-- Right (Logo + System Name) -->
  <div class="right">
    <img src="Agri.jpg" alt="System Logo">
    <h1>AgriTime Payroll<br>Attendance System</h1>
  </div>

  <script>
    const video = document.getElementById("camera");

    // Access webcam
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(stream => { video.srcObject = stream; })
      .catch(err => { alert("Camera access denied: " + err); });

    function captureFace() {
      const canvas = document.createElement("canvas");
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(video, 0, 0);
      document.getElementById("faceImage").value = canvas.toDataURL("image/png");
      document.getElementById("faceForm").submit();
    }
  </script>

</body>
</html>

