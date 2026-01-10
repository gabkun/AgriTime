<?php 
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Redirect if not logged in
if (!isset($_SESSION["user"])) {
  header("Location: /");
  exit;
}

$user = $_SESSION["user"];
$employeeID = $user["employeeID"];

// ✅ Handle profile update via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["updateProfile"])) {
  $firstName = $_POST["firstName"] ?? '';
  $lastName = $_POST["lastName"] ?? '';
  $birthday = $_POST["birthday"] ?? '';
  $gender = $_POST["gender"] ?? '';
  $phone = $_POST["phone"] ?? '';

  if (!$firstName || !$lastName) {
    echo "<script>alert('❌ Missing required fields.');</script>";
  } else {
    $url = "http://localhost:8080/api/user/update/" . urlencode($employeeID);

    $data = json_encode([
      "firstName" => $firstName,
      "lastName" => $lastName,
      "dob" => $birthday,
      "gender" => $gender,
      "contactNo" => $phone
    ]);

    $options = [
      "http" => [
        "header"  => "Content-Type: application/json\r\n",
        "method"  => "POST",
        "content" => $data,
        "ignore_errors" => true
      ]
    ];

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === FALSE) {
      echo "<script>alert('❌ Failed to connect to backend API.');</script>";
    } else {
      $decoded = json_decode($response, true);
      if (isset($decoded["message"]) && str_contains(strtolower($decoded["message"]), "updated")) {
        // ✅ Update session values
        $_SESSION["user"]["firstName"] = $firstName;
        $_SESSION["user"]["lastName"] = $lastName;
        $_SESSION["user"]["dob"] = $birthday;
        $_SESSION["user"]["gender"] = $gender;
        $_SESSION["user"]["contactNo"] = $phone;

        echo "<script>alert('✅ Profile updated successfully!'); window.location.href='/employee/myaccount';</script>";
        exit;
      } else {
        $error = $decoded["message"] ?? "Unknown error.";
        echo "<script>alert('❌ Failed to update profile: " . htmlspecialchars($error) . "');</script>";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account | AgriTime</title>
  <link rel="stylesheet" href="../../styles/sidebar.css">
  <link rel="stylesheet" href="../../styles/myAccount.css">
</head>

<body>
  <div class="container">
    <?php include('sidebar.php'); ?>

    <div class="main-content">
      <header class="account-header">
        <div class="profile-info">
          <img src="../assets/user.png" alt="Profile" class="profile-img">
          <div>
            <h2><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h2>
            <p>AgriTime Attendance System</p>
          </div>
        </div>
        <button type="button" id="editBtn" class="edit-btn">✏️ Edit Profile</button>
      </header>

      <div class="account-body">
        <form method="POST" class="account-form" id="accountForm">
          <div class="form-row">
            <div class="form-group">
              <label>First Name</label>
              <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" readonly class="editable" required>
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" readonly class="editable" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Birthday</label>
              <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" readonly class="editable">
            </div>
            <div class="form-group">
              <label>Gender</label>
              <select name="gender" disabled class="editable">
                <option value="Male" <?php if(($user['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if(($user['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" value="<?php echo htmlspecialchars($user['contactNo'] ?? ''); ?>" readonly class="editable">
            </div>
            <div class="form-group">
              <label>Shift Time</label>
              <input type="text" name="shiftTime" value="<?php echo htmlspecialchars($user['shiftTime'] ?? '8:00AM-10:00AM'); ?>" readonly>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Role</label>
              <?php
                $roleName = '';
                if (($user['role'] ?? '') == 2) $roleName = 'HR';
                elseif (($user['role'] ?? '') == 3) $roleName = 'Employee';
                else $roleName = 'Unknown';
              ?>
              <input type="text" name="role" value="<?php echo htmlspecialchars($roleName); ?>" readonly>
            </div>
            <div class="form-group">
              <label>Branch</label>
              <input type="text" name="branch" value="<?php echo htmlspecialchars($user['branch'] ?? 'Main Branch'); ?>" readonly>
            </div>
          </div>

          <div class="form-group address-box">
            <label>Address</label>
            <textarea name="address" readonly><?php echo htmlspecialchars($user['address'] ?? 'Bacolod City, Negros Occidental'); ?></textarea>
          </div>

          <div class="form-group">
            <label>Date Joined</label>
            <input type="text" name="dateJoined" value="<?php echo !empty($user['created_at']) ? date('F d, Y', strtotime($user['created_at'])) : ''; ?>" readonly>
          </div>

          <div class="form-buttons">
            <button type="button" class="save-btn" id="saveBtn" disabled>💾 Save Changes</button>
            <button type="submit" name="updateProfile" id="hiddenSubmit" style="display:none;"></button>
            <a href="dashboard" class="cancel-btn">← Back</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const editBtn = document.getElementById("editBtn");
      const saveBtn = document.getElementById("saveBtn");
      const hiddenSubmit = document.getElementById("hiddenSubmit");
      const inputs = document.querySelectorAll(".editable");

      // Save original values
      const originalValues = {};
      inputs.forEach(input => originalValues[input.name] = input.value);

      // Toggle edit mode
      editBtn.addEventListener("click", () => {
        const isEditing = editBtn.classList.toggle("active");

        inputs.forEach(input => {
          input.disabled = !isEditing;
          input.readOnly = false;
          input.style.border = isEditing ? "1px solid #2196F3" : "none";
          input.style.backgroundColor = isEditing ? "#fff" : "transparent";
        });

        saveBtn.disabled = !isEditing;
        saveBtn.style.display = isEditing ? "inline-block" : "none";
        editBtn.textContent = isEditing ? "🔒 Cancel Edit" : "✏️ Edit Profile";
        editBtn.style.backgroundColor = isEditing ? "#e53935" : "#66bb6a";

        if (!isEditing) {
          inputs.forEach(input => input.value = originalValues[input.name]);
        } else {
          saveBtn.onclick = () => hiddenSubmit.click();
          alert("You can now edit your profile. Click 'Save Changes' to update.");
        }
      });
    });
  </script>
</body>
</html>
