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
    <?php 
$currentPage = basename($_SERVER['REQUEST_URI']);
?>
<li class="<?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
  <i class="icon">🏠</i>
  <a href="/employee/dashboard">Dashboard</a>
</li>
<li class="<?php echo $currentPage == 'report' ? 'active' : ''; ?>">
  <i class="icon">📊</i>
  <a href="/employee/report">Attendance Report</a>
</li>
<li class="<?php echo $currentPage == 'myaccount' ? 'active' : ''; ?>">
  <i class="icon">👤</i>
  <a href="/employee/myaccount">My Account</a>
</li>

      </ul>
    </div>

<!-- Toggle Button -->
<button id="toggle-btn" class="toggle-btn">☰</button>
</body>
</html>
