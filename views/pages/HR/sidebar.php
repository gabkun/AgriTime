<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriTime Attendance System</title>
  <link rel="stylesheet" href="../../styles/sidebar.css">
</head>
<body>

  <!-- ✅ Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-user">
      <h3>🌿 AgriTime</h3>
      <p>Attendance System</p>
    </div>

    <ul class="nav-links">
      <?php $currentPage = basename($_SERVER['REQUEST_URI']); ?>
      <li class="<?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
        <i class="icon">🏠</i>
        <a href="/hr/dashboard">Dashboard</a>
      </li>
      <li class="<?php echo $currentPage == 'employeedb' ? 'active' : ''; ?>">
        <i class="icon">🧑‍🌾</i>
        <a href="/hr/employeedb">Employee Database</a>
      </li>
      <li class="<?php echo $currentPage == 'attendancereport' ? 'active' : ''; ?>">
        <i class="icon">📊</i>
        <a href="/hr/attendancereport">Attendance Report</a>
      </li>
      <li class="<?php echo $currentPage == 'generatepayslip' ? 'active' : ''; ?>">
        <i class="icon">📃</i>
        <a href="/hr/generatepayslip">Generate Employee Payslip</a>
      </li>
      <li class="<?php echo $currentPage == 'myaccount' ? 'active' : ''; ?>">
        <i class="icon">👤</i>
        <a href="/hr/myaccount">My Account</a>
      </li>
    </ul>
  </div>

  <!-- ✅ Toggle Button -->
  <button id="toggle-btn" class="toggle-btn">☰</button>

  <!-- ✅ Script for Sidebar Toggle -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const toggleBtn = document.getElementById("toggle-btn");
      const sidebar = document.getElementById("sidebar");

      toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("active");
      });
    });
  </script>

</body>
</html>
