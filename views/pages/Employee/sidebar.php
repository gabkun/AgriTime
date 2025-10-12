<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriTime Attendance System</title>
  <link rel="stylesheet" href="../../styles/sidebar.css">
</head>
    <body>

      <div class="sidebar" id="sidebar">
      <div class="sidebar-user">
        <h3>🌿 AgriTime</h3>
        <p>Attendance System</p>
      </div>

     <ul class="nav-links">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
          <i class="icon">🏠</i>
          <a href="/employee/dashboard.php">Dashboard</a>
        </li>

        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'attendance_report.php' ? 'active' : ''; ?>">
          <i class="icon">📊</i>
          <a href="/employee/attendance_report.php">Attendance Report</a>
        </li>

        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'myAccount.php' ? 'active' : ''; ?>">
          <i class="icon">👤</i>
          <a href="/employee/myAccount.php">My Account</a>
        </li>

        <li>
          <i class="icon">🚪</i>
          <a href="/employee/logout.php">Logout</a>
        </li>
      </ul>
    </div>

<!-- Toggle Button -->
<button id="toggle-btn" class="toggle-btn">☰</button>
</body>
</html>
