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
        <!-- Removed form action -->
        <form method="POST" class="account-form" id="accountForm">

          <!-- First & Last Name -->
          <div class="form-row">
            <div class="form-group">
              <label>First Name</label>
              <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" disabled class="editable" required>
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" disabled class="editable" required>
            </div>
          </div>

          <!-- Birthday & Gender -->
          <div class="form-row">
            <div class="form-group">
              <label>Birthday</label>
              <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" disabled class="editable">
            </div>
            <div class="form-group">
              <label>Gender</label>
              <select name="gender" disabled class="editable">
                <option value="Male" <?php if(($user['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if(($user['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
              </select>
            </div>
          </div>

          <!-- Phone Number & Shift -->
          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="contactNo" value="<?php echo htmlspecialchars($user['contactNo'] ?? ''); ?>" disabled class="editable">
            </div>
            <div class="form-group">
              <label>Shift Time</label>
              <input type="text" name="shiftTime" value="<?php echo htmlspecialchars($user['shiftTime'] ?? '8:00AM-10:00AM'); ?>" readonly>
            </div>
          </div>

          <!-- Role & Branch -->
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

          <!-- Address -->
          <div class="form-group address-box">
            <label>Address</label>
            <textarea name="address" disabled><?php echo htmlspecialchars($user['address'] ?? 'Bacolod City, Negros Occidental'); ?></textarea>
          </div>

          <!-- Date Joined -->
          <div class="form-group">
            <?php
              $rawDate = $user['created_at'] ?? '';
              $formattedDate = !empty($rawDate) ? date("F d, Y", strtotime($rawDate)) : '';
            ?>
            <label>Date Joined</label>
            <input type="text" name="dateJoined" value="<?php echo htmlspecialchars($formattedDate); ?>" readonly>
          </div>

          <!-- Buttons -->
          <div class="form-buttons">
            <button type="button" class="save-btn" id="saveBtn" disabled>💾 Save Changes</button>
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
      const inputs = document.querySelectorAll(".editable");

      // Save original values
      const originalValues = {};
      inputs.forEach(input => originalValues[input.name] = input.value);

      // Toggle edit mode
      editBtn.addEventListener("click", () => {
        const isEditing = editBtn.classList.toggle("active");
        inputs.forEach(input => input.disabled = !isEditing);
        saveBtn.disabled = !isEditing;

        editBtn.textContent = isEditing ? "🔒 Cancel Edit" : "✏️ Edit Profile";
        editBtn.style.backgroundColor = isEditing ? "#e53935" : "#66bb6a";

        if (!isEditing) {
          inputs.forEach(input => input.value = originalValues[input.name]);
        }
      });

      // Save changes via API
      saveBtn.addEventListener("click", async () => {
        const userID = "<?php echo $user['employeeID'] ?? $user['id']; ?>";

        // Only send editable fields
        const editableFields = ['firstName', 'lastName', 'dob', 'gender', 'contactNo'];
        const payload = {};
        editableFields.forEach(field => {
          const input = document.querySelector(`[name="${field}"]`);
          if (input) payload[field] = input.value;
        });

        console.log("📤 Payload to send:", payload);

        try {
          const res = await fetch(`http://localhost:8080/api/user/update/${encodeURIComponent(userID)}`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
          });

          const data = await res.json();
          console.log("📥 API response:", data);

          if (res.ok && data.message.toLowerCase().includes("updated")) {
            alert("✅ Profile updated successfully!");
            // Update original values
            inputs.forEach(input => originalValues[input.name] = input.value);
            // Disable editing
            inputs.forEach(input => input.disabled = true);
            editBtn.classList.remove("active");
            editBtn.textContent = "✏️ Edit Profile";
            editBtn.style.backgroundColor = "#66bb6a";
          } else {
            const error = data.message || "Unknown error.";
            alert("❌ Failed to update profile: " + error);
          }
        } catch (err) {
          console.error(err);
          alert("❌ Error connecting to API.");
        }
      });
    });
  </script>
</body>
</html>
