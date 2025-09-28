<?php
date_default_timezone_set("Asia/Manila");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $dob = $_POST["dob"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $contactNo = $_POST["contactNo"];
    $role = $_POST["role"];
    $nationality = $_POST["nationality"];
    $maritalStatus = $_POST["maritalStatus"];
    $emergencyContact = $_POST["emergencyContact"];
    $employeeID = $_POST["employeeID"];

    // Folder where captured photos are saved
    $profilePicFolder = "uploads/" . $employeeID . "_" . $lastName;
    if (!is_dir($profilePicFolder)) {
        mkdir($profilePicFolder, 0777, true);
    }

    // Get all captured images
    $capturedImages = glob($profilePicFolder . "/*.jpg");

    // Express backend API endpoint
    $url = "http://localhost:8080/api/user";
    $boundary = uniqid();
    $delimiter = '-------------' . $boundary;
    $postData = '';

    // Helper to add form-data fields
    function addFormField($name, $value, $delimiter) {
        return "--$delimiter\r\n" .
               "Content-Disposition: form-data; name=\"$name\"\r\n\r\n" .
               "$value\r\n";
    }

    // Add all form fields
    $fields = [
        "firstName" => $firstName,
        "lastName" => $lastName,
        "dob" => $dob,
        "email" => $email,
        "password" => $password,
        "contactNo" => $contactNo,
        "role" => $role,
        "nationality" => $nationality,
        "maritalStatus" => $maritalStatus,
        "emergencyContact" => $emergencyContact,
        "employeeID" => $employeeID
    ];

    foreach ($fields as $key => $value) {
        $postData .= addFormField($key, $value, $delimiter);
    }

    // Attach captured photos with correct field name
    foreach ($capturedImages as $imgPath) {
        $fileContents = file_get_contents($imgPath);
        $fileName = basename($imgPath);
        $postData .= "--$delimiter\r\n";
        $postData .= "Content-Disposition: form-data; name=\"profilePic\"; filename=\"$fileName\"\r\n";
        $postData .= "Content-Type: image/jpeg\r\n\r\n";
        $postData .= $fileContents . "\r\n";
    }

    // Close form-data
    $postData .= "--$delimiter--\r\n";

    // Send to backend
    $options = [
        "http" => [
            "header"  => "Content-Type: multipart/form-data; boundary=$delimiter\r\n",
            "method"  => "POST",
            "content" => $postData
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "<script>alert('Error connecting to API');</script>";
    } else {
        $response = json_decode($result, true);
        if (isset($response["message"]) && $response["message"] === "User created successfully") {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='/login';</script>";
        } else {
            $msg = isset($response["message"]) ? $response["message"] : "Unknown error";
            echo "<script>alert('".$msg."');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Agritime Attendance System - Registration</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script defer src="js/face-api.min.js"></script>
</head>
<body>

<!-- Background --> 
<div class="login-background"></div>

<!-- Register Box -->
<div class="login-container">
  <div class="login-header">
    <img src="assets/img/logo.png" alt="AgriTime Logo" class="logo">
    <h2>Agritime Attendance System - Register</h2>
  </div>

  <form action="register.php" method="POST" enctype="multipart/form-data" id="registerForm">
    <div class="center-text-button">
      <input type="text" name="firstName" placeholder="First Name" required>
      <input type="text" name="lastName" placeholder="Last Name" required>
      <input type="date" name="dob" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="text" name="contactNo" placeholder="Contact Number" required>
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="1">Employee</option>
        <option value="2">Admin</option>
      </select>
      <input type="text" name="nationality" placeholder="Nationality" required>
      <input type="text" name="maritalStatus" placeholder="Marital Status" required>
      <input type="text" name="emergencyContact" placeholder="Emergency Contact" required>
      <input type="text" name="employeeID" placeholder="Employee ID" required>

      <!-- Webcam capture -->
      <div class="camera-section">
        <h3>Take Your 5 Profile Photos</h3>
        <video id="video" width="400" height="300" autoplay></video><br>
        <button type="button" id="captureBtn" class="login-btn">Capture Photo</button>
        <p id="photoCount">Photos Taken: 0 / 5</p>
        <input type="hidden" id="photoFolder" name="photoFolder">
      </div>

      <p class="register-text">Have an account? <a href="/login">Login Here</a></p>
      <button type="submit" class="login-btn">Register</button>
    </div>
  </form>
</div>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
      const video = document.getElementById("video");
      const captureBtn = document.getElementById("captureBtn");
      const photoCount = document.getElementById("photoCount");

      let photoIndex = 0;
      let folderName = '';

      // Start camera safely
      navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
          video.srcObject = stream;
        })
        .catch(err => {
          console.error("Camera access denied:", err);
          alert("Please allow camera access to capture your profile photos.");
        });

      captureBtn.addEventListener('click', async () => {
        const lastName = document.querySelector('input[name="lastName"]').value.trim();
        const employeeID = document.querySelector('input[name="employeeID"]').value.trim();

        if (!lastName || !employeeID) {
          alert("Please enter Last Name and Employee ID first.");
          return;
        }

        if (!folderName) folderName = `${employeeID}_${lastName}`;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        const dataUrl = canvas.toDataURL('image/jpeg');

        try {
          const response = await fetch('save_face.php', {
            method: 'POST',
            body: new URLSearchParams({
              image: dataUrl,
              folder: folderName,
              index: photoIndex
            })
          });

          const result = await response.text();

          if (result === 'OK') {
            photoIndex++;
            photoCount.textContent = `Photos Taken: ${photoIndex} / 5`;

            if (photoIndex === 5) {
              alert("5 photos captured successfully!");
              captureBtn.disabled = true;
            }
          } else {
            alert("Error saving photo. Please try again.");
          }
        } catch (error) {
          console.error("Error saving photo:", error);
          alert("Something went wrong while saving your photo.");
        }
      });
    });
    </script>


</body>
</html>
