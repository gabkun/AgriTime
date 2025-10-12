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
  <link rel="stylesheet" href="styles/register.css">
  <script defer src="js/face-api.min.js"></script>
</head>
<body class="register-page">
  <div class="left">
    <form method="POST" enctype="multipart/form-data">
    <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AgriTime Registration</title>
  <link rel="stylesheet" href="styles/register.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="container">
  <div class="card">
    <div class="card-left">
      <h1>🌱 AgriTime</h1>
      <p>Join our nature-driven attendance system.</p>
      <img src="assets/Agri.jpg" alt="Farm Illustration">
    </div>

    <div class="card-right">
      <h2>Register Account</h2>
      <form method="POST" enctype="multipart/form-data">
        
        <div class="form-row">
          <div>
            <label>First Name</label>
            <input type="text" name="firstName" required>
          </div>
          <div>
            <label>Last Name</label>
            <input type="text" name="lastName" required>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Date of Birth</label>
            <input type="date" name="dob" required>
          </div>
          <div>
            <label>Role</label>
            <select name="role" required>
              <option value="">Select Role</option>
              <option value="1">Employee</option>
              <option value="2">Admin</option>
            </select>
          </div>
        </div>

        <label>Email Address</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Contact Number</label>
        <input type="text" name="contactNo" required>

        <div class="form-row">
          <input type="text" name="nationality" placeholder="Nationality" required>
          <select name="maritalStatus" required>
            <option value="">Select Marital Status</option>
            <option value="Single">Single</option>
            <option value="Married">Married</option>
            <option value="Divorced">Divorced</option>
            <option value="Widowed">Widowed</option>
          </select>
        </div>

        <input type="text" name="emergencyContact" placeholder="Emergency Contact" required>
        <input type="text" name="employeeID" placeholder="Employee ID" required>

        <div class="camera-section">
          <h3>📸 Capture 5 Profile Photos</h3>
          <video id="video" width="100%" height="220" autoplay></video><br>
          <button type="button" id="captureBtn" class="capture-btn">Capture</button>
          <p id="photoCount">Photos Taken: 0 / 5</p>
        </div>

        <button type="submit" class="register-btn">Register</button>
        <a href="login" class="login-link">Already have an account? Login</a>
      </form>
    </div>
  </div>
</div>

</body>
</html>
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
