<?php 
session_start();
if (!isset($_SESSION["user"])) {
  header("Location: /");
  exit;
}
$user = $_SESSION["user"];
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
        <form action="updateAccount.php" method="POST" class="account-form" id="accountForm">

          <div class="form-row">
            <div class="form-group">
              <label>First Name</label>
              <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" disabled required>
            </div>

            <div class="form-group">
              <label>Last Name</label>
              <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" disabled required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Birthday</label>
              <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" disabled>
            </div>

            <div class="form-group">
              <label>Gender</label>
              <select name="gender" disabled>
                <option value="Male" <?php if(($user['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if(($user['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" value="<?php echo htmlspecialchars($user['contactNo'] ?? ''); ?>" disabled>
            </div>

            <div class="form-group">
              <label>Shift Time</label>
              <input type="text" name="shiftTime" value="<?php echo htmlspecialchars($user['shiftTime'] ?? ''); ?>" disabled>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Role</label>
              <input type="text" name="role" value="<?php echo htmlspecialchars($user['roleName'] ?? 'Employee'); ?>" readonly>
            </div>

            <div class="form-group">
              <label>Branch</label>
              <input type="text" name="branch" value="<?php echo htmlspecialchars($user['branch'] ?? 'Main Branch'); ?>" disabled>
            </div>
          </div>

          <div class="form-group address-box">
            <label>Address</label>
            <textarea name="address" disabled><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <label>Date Joined</label>
            <input type="text" name="dateJoined" value="<?php echo htmlspecialchars($user['dateJoined'] ?? ''); ?>" readonly>
          </div>

          <div class="form-buttons">
            <button type="submit" class="save-btn" id="saveBtn" disabled>💾 Save Changes</button>
            <a href="dashboard" class="cancel-btn">← Back</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Sidebar toggle
    document.addEventListener("DOMContentLoaded", () => {
      const toggleBtn = document.getElementById("toggle-btn");
      const sidebar = document.getElementById("sidebar");
      if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", () => sidebar.classList.toggle("hidden"));
      }

      // Edit Mode Toggle
      const editBtn = document.getElementById("editBtn");
      const saveBtn = document.getElementById("saveBtn");
      const inputs = document.querySelectorAll("#accountForm input:not([readonly]), #accountForm select, #accountForm textarea");

      editBtn.addEventListener("click", () => {
        const isEditing = editBtn.classList.toggle("active");

        inputs.forEach(input => input.disabled = !isEditing);
        saveBtn.disabled = !isEditing;

        editBtn.textContent = isEditing ? "🔒 Cancel Edit" : "✏️ Edit Profile";
        editBtn.style.backgroundColor = isEditing ? "#e53935" : "#66bb6a";
      });
    });
  </script>
</body>
</html>
