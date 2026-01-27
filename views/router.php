<?php 
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Handle POST from detected face
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $faceImage = $_POST["faceImage"] ?? '';

    if (!empty($faceImage)) {
        // Express backend API endpoint
        $url = "http://localhost:8080/api/user/facial/timein";

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
            echo "<script>alert('User already timed in, please contact your administrator.');</script>";
        } else {
            $response = json_decode($result, true);

            // ✅ Successful facial login
               if ($response["message"] === "Time in recorded successfully") {
    echo "<script>
        alert('Time in successful. Have a great day!');
    </script>";
} else {
                    $msg = $response["message"] ?? "Face not recognized or time-in failed.";
                    echo "<script>alert('" . addslashes($msg) . "');</script>";
                }
        }
    } else {
        echo "<script>alert('No face detected. Please try again.');</script>";
    }
}

$attendanceData = [];

$apiUrl = "http://localhost:8080/api/attendance/get/allstatus";

$apiResponse = @file_get_contents($apiUrl);

if ($apiResponse !== false) {
    $decoded = json_decode($apiResponse, true);

    if (!empty($decoded["data"])) {
        $attendanceData = $decoded["data"];
    }
}
?>


<!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8" />
          <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
          <title>Attendance Check</title>
          <!-- Face API scripts -->
          <script defer src="js/face-api.min.js"></script>
          <script defer src="js/loadFaceDetection.js"></script>

          <link rel="stylesheet" href="styles/login.css">
        </head>
         <body class="login-page">

          <!-- LEFT: Facial Recognition -->
          <div class="left">
              <h2 class="login-title">Time-In</h2>
            <p class="login-subtext">
                <br>Please Face the Camera to Time in
                </p>


            <div id="video-container">
              <video id="video" autoplay muted playsinline></video>
            </div>

            <form method="POST" id="faceForm">
              <input type="hidden" name="faceImage" id="faceImage" />
            </form>

      

            <!-- Nature-themed Welcome Feedback -->
            <div id="login-feedback" class="welcome-feedback">
              <div class="leaf-spinner">🍃</div>
              <div id="welcome-message"></div>
            </div>
              
            <!-- Note -->
                             <div class="note">
                Press here to Proceed with Dashboard
                <a href="login">Login here</a>
              </div>
              <div class="note">
                Press here to Proceed with Time Out
                <a href="timeout">Timeout here</a>
              </div>
              <div class="note">
                Don't have an account?
                <a href="register">Register here</a>
              </div>
            </div>

            <!-- RIGHT: Logo Section -->
<div class="right">
    <table class="chrona-attlog-table">
        <thead>
            <tr class="chrona-attlog-head">
                <th>Time</th>
                <th>Employee ID</th>
                <th>Status</th>
            </tr>
        </thead>
<tbody>
<?php if (!empty($attendanceData)): ?>
    <?php foreach ($attendanceData as $row): ?>
<tr class="chrona-attlog-row">
    <td><?= htmlspecialchars($row["time"]) ?></td>
    <td><?= htmlspecialchars($row["employeeID"]) ?></td>
    <td class="chrona-status">
        <?php
            if ($row["attendance_status"] == 1) {
                echo "<span class='status-in'>TIME IN</span>";
            } elseif ($row["attendance_status"] == 3) {
                echo "<span class='status-break'>ON BREAK</span>";
            } else {
                echo "<span class='status-out'>TIME OUT</span>";
            }
        ?>
    </td>
</tr>

    <?php endforeach; ?>
<?php else: ?>
    <tr class="chrona-attlog-row">
        <td colspan="3" style="text-align:center;">No attendance records found</td>
    </tr>
<?php endif; ?>
</tbody>
    </table>
</div>
            
          </body>
    </html>

  <script>
  const video = document.getElementById("video");
 

  let detectionCounts = {};
  let isDetectionRunning = false;

  // ✅ Start the camera and AUTO-START face detection
  window.addEventListener("DOMContentLoaded", async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = stream;
      // Auto-start detection after camera is ready
      video.addEventListener('loadedmetadata', async () => {
        await new Promise(resolve => setTimeout(resolve, 1000)); // Wait 1 second for camera to stabilize
        captureFace(); // Auto-start detection
      });
      
    } catch (err) {
      console.error("Camera access denied or error:", err);
      alert("Unable to access the camera.");
    }
  });

  //  AUTO FACE DETECTION - runs automatically, idles when no face detected
  async function captureFace() {
    if (isDetectionRunning) return; // Prevent multiple instances
    isDetectionRunning = true;

    const response = await fetch("get_labels.php");
    const labels = await response.json();
    console.log("Loaded labels:", labels);

    if (labels.length === 0) {
      alert("No registered face data found. Please register first.");
      isDetectionRunning = false;
      return;
    }

    detectionCounts = {};

    // ✅ Start detection with faster threshold (20 detections)
    await loadFaceDetection(video, labels, {
      modelsPath: "models",
      imagesPath: "labels",
      detectionInterval: 150,
      onDetect: (label, detection) => {
        if (!label) return;

        detectionCounts[label] = (detectionCounts[label] || 0) + 1;

        console.log(`Detected: ${label} (${detectionCounts[label]}x)`);

        // ✅ Trigger login after 20 detections (faster login)
        if (detectionCounts[label] >= 20) {
          console.log(`✅ Triggering login for ${label}`);
          detectionCounts[label] = 0; // reset counter

          
          const feedback = document.getElementById("login-feedback");
          const messageEl = document.getElementById("welcome-message");
          let countdown = 1;

          feedback.style.display = "flex";
          document.getElementById("faceImage").value = label;

          const intervalId = setInterval(() => {
            countdown--;
            if (countdown > 0) {
              messageEl.textContent = `Welcome back, ${label}! Logging you in ...`;
            } else {
              clearInterval(intervalId);
              document.getElementById("faceForm").submit();
            }
          }, 100);
        }
      },
    });
  }
</script>

  
</body>
</html>