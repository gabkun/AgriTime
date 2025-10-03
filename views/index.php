<?php 
date_default_timezone_set("Asia/Manila");

// ✅ Handle POST from detected face
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $faceImage = $_POST["faceImage"] ?? '';

    if (!empty($faceImage)) {
        // Express backend API endpoint
        $url = "http://localhost:8080/api/user/facial-login";

        // Send as x-www-form-urlencoded (simpler and cleaner)
        $data = ['faceImage' => $faceImage];

        $options = [
            "http" => [
                "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
                "method"  => "POST",
                "content" => http_build_query($data)
            ]
        ];

        $context  = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === FALSE) {
            echo "<script>alert('Error connecting to API. Please try again.');</script>";
        } else {
            $response = json_decode($result, true);

            if (isset($response["message"]) && $response["message"] === "Login successful") {
                echo "<script>
                        alert('Welcome back, " . htmlspecialchars($faceImage) . "! Redirecting to dashboard...');
                        window.location.href = '/dashboard';
                      </script>";
            } else {
                $msg = $response["message"] ?? "Invalid credentials or face not recognized.";
                echo "<script>alert('" . addslashes($msg) . "');</script>";
            }
        }
    } else {
        echo "<script>alert('No face detected. Please try again.');</script>";
    }
}
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

    // ✅ Start the camera as soon as page loads
    window.addEventListener("DOMContentLoaded", async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
      } catch (err) {
        console.error("Camera access denied or error:", err);
        alert("Unable to access the camera.");
      }
    });

    // ✅ Capture Face and handle 100 detection check
    async function captureFace() {
      alert("Please face the camera to start scanning...");

      // Fetch all labels dynamically from PHP
      const response = await fetch("get_labels.php");
      const labels = await response.json();

      console.log("Loaded labels:", labels);

      if (labels.length === 0) {
        alert("No registered face data found. Please register first.");
        return;
      }

      // Store label detection counts
      let detectionCounts = {};

      // ✅ Proceed with detection using the fetched labels
      await loadFaceDetection(video, labels, {
        modelsPath: "models",
        imagesPath: "labels",
        onDetect: (label, detection) => {
          if (!label) return;

          // Count how many times each label was detected
          detectionCounts[label] = (detectionCounts[label] || 0) + 1;

          console.log(`Detected: ${label} (${detectionCounts[label]}x)`);

          // ✅ If detected 100 times, trigger login
          if (detectionCounts[label] >= 100) {
            console.log(`✅ Triggering login for ${label}`);
            detectionCounts[label] = 0; // reset counter

            document.getElementById("scanBtn").style.display = "none";
            const feedback = document.getElementById("login-feedback");
            const messageEl = document.getElementById("welcome-message");
            let countdown = 5;

            feedback.style.display = "flex";
            document.getElementById("faceImage").value = label;

            messageEl.textContent = `Welcome back, ${label}! Logging you in ${countdown}...`;

            const intervalId = setInterval(() => {
              countdown--;
              if (countdown > 0) {
                messageEl.textContent = `Welcome back, ${label}! Logging you in ${countdown}...`;
              } else {
                clearInterval(intervalId);
                // ✅ Submit to backend (facial login)
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
