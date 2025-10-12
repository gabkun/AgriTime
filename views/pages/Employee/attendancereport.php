<?php 
session_start();
date_default_timezone_set("Asia/Manila");

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
  <title>Attendance Report | AgriTime</title>
  <link rel="stylesheet" href="../../styles/attendanceReport.css">
</head>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("toggle-btn");
    const sidebar = document.getElementById("sidebar");
    const main = document.querySelector(".main-content");

    if (toggleBtn && sidebar && main) {
      toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("hidden");
        main.classList.toggle("expanded");
      });
    }
  });

  function sortTable(n) {
    let table = document.getElementById("attendance-table");
    let rows = Array.from(table.rows).slice(1);
    let asc = table.dataset.sortOrder !== "asc";
    rows.sort((a, b) => {
      let x = a.cells[n].innerText;
      let y = b.cells[n].innerText;
      return asc ? x.localeCompare(y) : y.localeCompare(x);
    });
    table.dataset.sortOrder = asc ? "asc" : "desc";
    rows.forEach(r => table.tBodies[0].appendChild(r));
  }
</script>

<body>
  <div class="container">
    <?php include('sidebar.php'); ?>

    <div class="main-content">
      <header class="header">
        <div class="logo">
          <img src="../assets/Agri.jpg" alt="Agri Logo" width="150">
          <h2>Attendance Report</h2>
        </div>
        <div class="user-profile">
          <img src="../assets/user.png" alt="User" width="50">
          <span><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
        </div>
      </header>

      <section class="attendance-section">
        <div class="report-header">
          <div>
            <h3>🌿 Attendance Overview</h3>
            <p>Track employee daily attendance record</p>
          </div>
          <div class="filter-box">
            <label for="from-date">From:</label>
            <input type="date" id="from-date">
            <label for="to-date">To:</label>
            <input type="date" id="to-date">
            <button id="filter-btn">Filter</button>
          </div>
        </div>

        <div class="table-container">
          <table id="attendance-table">
            <thead>
              <tr>
                <th onclick="sortTable(0)">Date ⬍</th>
                <th onclick="sortTable(1)">Time In ⬍</th>
                <th onclick="sortTable(2)">Time Out ⬍</th>
                <th>Employee Name</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>2025-02-25</td>
                <td class="time-in">9:43 AM</td>
                <td class="time-out">5:00 PM</td>
                <td>John</td>
              </tr>
              <tr>
                <td>2025-02-24</td>
                <td class="time-in">9:35 AM</td>
                <td class="time-out">5:10 PM</td>
                <td>Maria</td>
              </tr>
              <tr>
                <td>2025-02-26</td>
                <td class="time-in">9:20 AM</td>
                <td class="time-out">5:05 PM</td>
                <td>Carlos</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
