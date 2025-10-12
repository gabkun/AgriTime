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
  <link rel="stylesheet" href="../../styles/myAccount.css">
  <link rel="stylesheet" href="../../styles/sidebar.css">
</head>

<body>
  <div class="container">
    <!-- ✅ Include Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- ✅ Main Content Area -->
    <div class="main-content">
      <header class="account-header">
        <img src="../assets/Agri.jpg" alt="Agri Logo" class="logo">
        <h2>My Account</h2>
      </header>

      <div class="account-body">
        <form action="updateAccount.php" method="POST" class="account-form">
          <div class="form-row">
            <div class="form-group">
              <label>First Name</label>
              <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>
            </div>

            <div class="form-group">
              <label>Last Name</label>
              <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Birthday</label>
              <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label>Gender</label>
              <select name="gender">
                <option value="Male" <?php if(($user['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if(($user['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label>Shift Time</label>
              <input type="text" name="shiftTime" value="<?php echo htmlspecialchars($user['shiftTime'] ?? ''); ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Role</label>
              <input type="text" name="role" value="<?php echo htmlspecialchars($user['roleName'] ?? 'Employee'); ?>" readonly>
            </div>

            <div class="form-group">
              <label>Branch</label>
              <input type="text" name="branch" value="<?php echo htmlspecialchars($user['branch'] ?? 'Main Branch'); ?>">
            </div>
          </div>

          <div class="form-group">
            <label>Address</label>
            <textarea name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <label>Date Joined</label>
            <input type="text" name="dateJoined" value="<?php echo htmlspecialchars($user['dateJoined'] ?? ''); ?>" readonly>
          </div>

          <div class="form-buttons">
            <button type="submit" class="save-btn">💾 Save Changes</button>
            <a href="dashboard.php" class="cancel-btn">← Back</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ✅ Sidebar Toggle Script -->
  <script>
  document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("toggle-btn");
    const sidebar = document.getElementById("sidebar");
    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("hidden");
      });
    }
  });
  </script>
</body>
</html>
