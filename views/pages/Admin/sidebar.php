<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriTime Admin Access</title>
  <link rel="stylesheet" href="../../styles/sidebar.css">
</head>
<body>

  <!-- ✅ Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-user">
      <h3>AgriTime</h3>
      <p>Attendance System</p>
    </div>

    <ul class="nav-links">
      <?php $currentPage = basename($_SERVER['REQUEST_URI']); ?>
      <li class="<?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
        <i class="icon"><img src="../assets/house.png" alt="money" class="sidebar-icon"></i>
        <a href="/admin/dashboard">Dashboard</a>
      </li>
      <li class="<?php echo $currentPage == 'employeetrack' ? 'active' : ''; ?>">
          <i class="icon"><img src="../assets/people.png" alt="money" class="sidebar-icon"></i>
        <a href="/admin/employeetrack">Employee Tracker</a>
      </li>
      <li class="<?php echo $currentPage == 'allusers' ? 'active' : ''; ?>">
       <i class="icon"><img src="../assets/pie-chart.png" alt="money" class="sidebar-icon"></i>
        <a href="/admin/allusers">All Employees</a>
      </li>
      <li class="<?php echo $currentPage == 'payslipdata' ? 'active' : ''; ?>">
        <i class="icon"><img src="../assets/payslip2.png" alt="money" class="sidebar-icon"></i>
        <a href="/admin/payslipdata">Payslip Data</a>
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
