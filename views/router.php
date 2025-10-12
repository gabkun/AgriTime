<?php 
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Handle POST from detected face
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $faceImage = $_POST["faceImage"] ?? '';

    if (!empty($faceImage)) {
        // Express backend API endpoint
        $url = "http://localhost:8080/api/user/facial-login";

        // Send face image to Express backend
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

            // ✅ Successful facial login
            if (isset($response["message"]) && $response["message"] === "Login successful") {
                // Save user info in PHP session
                $_SESSION["user"] = $response["user"];
                $_SESSION["login_time"] = $response["loginTime"];

                // ✅ Redirect using router route
                echo "<script>
                        alert('Welcome back, " . addslashes($response['user']['firstName']) . "!');
                        window.location.href = '/employee/dashboard';
                      </script>";
            } else {
                // Handle invalid credentials or unrecognized face
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

          <!-- Face API scripts -->
          <script defer src="js/face-api.min.js"></script>
          <script defer src="js/loadFaceDetection.js"></script>

          <link rel="stylesheet" href="styles/login.css">
        </head>
         <body class="login-page">

          <!-- LEFT: Facial Recognition -->
          <div class="left">
            <h2 class="login-title">🌱 Login</h2>
            <p class="login-subtext">Welcome Back<br>Pleasess Scan Face for Login</p>


            <div id="video-container">
              <video id="video" autoplay muted playsinline></video>
            </div>

            <form method="POST" id="faceForm">
              <input type="hidden" name="faceImage" id="faceImage" />
              <button type="button" id="scanBtn" onclick="captureFace()">Scan Face</button>
            </form>

            <div class="progress-container" id="progressContainer">
              <div class="progress-bar" id="progressBar"></div>
            </div>
            <div id="progressText">0%</div>

            <!-- Nature-themed Welcome Feedback -->
            <div id="login-feedback" class="welcome-feedback">
              <div class="leaf-spinner">🍃</div>
              <div id="welcome-message"></div>
            </div>
              
            <!-- Note -->
              <div class="note">
                Don’t have an account?
                <a href="register">Register here</a>
              </div>
            </div>

            <!-- RIGHT: Logo Section -->
            <div class="right">
              <img src="assets/Agri.jpg" alt="System Logo" />
              <h1>AgriTime Payroll<br>Attendance System</h1>
           
            </div>
            
          </body>
    </html>

  <script>
  const video = document.getElementById("video");
  const scanBtn = document.getElementById("scanBtn");
  const progressBar = document.getElementById("progressBar");
  const progressText = document.getElementById("progressText");

  // ✅ Start the camera
  window.addEventListener("DOMContentLoaded", async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = stream;
    } catch (err) {
      console.error("Camera access denied or error:", err);
      alert("Unable to access the camera.");
    }
  });

  // ✅ Capture Face and show progress based on detections
  async function captureFace() {
    alert("Please face the camera to start scanning...");

    const response = await fetch("get_labels.php");
    const labels = await response.json();
    console.log("Loaded labels:", labels);

    if (labels.length === 0) {
      alert("No registered face data found. Please register first.");
      return;
    }

    let detectionCounts = {};
    progressBar.style.width = "0%";
    progressText.innerText = "0%";

    // ✅ Start detection and update bar based on progress
    await loadFaceDetection(video, labels, {
      modelsPath: "models",
      imagesPath: "labels",
      onDetect: (label, detection) => {
        if (!label) return;

        detectionCounts[label] = (detectionCounts[label] || 0) + 1;

        // Calculate percentage based on 100 detections target
        const progress = Math.min((detectionCounts[label] / 100) * 100, 100);
        progressBar.style.width = progress + "%";
        progressText.innerText = Math.floor(progress) + "%";

        // ✅ Optional funny status text update
        if (progress < 30) progressText.innerText = "Scanning face 😎 (" + Math.floor(progress) + "%)";
        else if (progress < 60) progressText.innerText = "Hold still 😁 (" + Math.floor(progress) + "%)";
        else if (progress < 90) progressText.innerText = "Almost there 🤓 (" + Math.floor(progress) + "%)";
        else progressText.innerText = "Perfect match detected 😍 (" + Math.floor(progress) + "%)";

        console.log(`Detected: ${label} (${detectionCounts[label]}x)`);

        // ✅ When reaches 100 detections, trigger login
        if (detectionCounts[label] >= 100) {
          console.log(`✅ Triggering login for ${label}`);
          detectionCounts[label] = 0; // reset counter

          document.getElementById("scanBtn").style.display = "none";
          const feedback = document.getElementById("login-feedback");
          const messageEl = document.getElementById("welcome-message");
          let countdown = 5;

          feedback.style.display = "flex";
          document.getElementById("faceImage").value = label;

          // Set progress bar full green
          progressBar.style.width = "100%";
          progressBar.style.background = "linear-gradient(90deg, #4caf50, #2e7d32)";


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
      },
    });
  }
</script>

  
</body>
</html>
