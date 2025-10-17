<?php  
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Redirect if not logged in
if (!isset($_SESSION["user"])) {
    header("Location: /");
    exit;
}

$user = $_SESSION["user"];

// ✅ API Endpoint for all employees' daily status
$apiUrl = "http://localhost:8080/api/attendance/get/allstatus";

// ✅ Fetch Attendance Status (Backend → Frontend connection)
$response = @file_get_contents($apiUrl);
$attendanceData = [];

if ($response !== FALSE) {
    $decoded = json_decode($response, true);
    $attendanceData = $decoded["data"] ?? [];
} else {
    $attendanceData = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Tracking | AgriTime</title>
  <link rel="stylesheet" href="../../styles/employeetracking.css">
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

  // ✅ Sort table columns
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
          <h2>Employee Tracking</h2>
        </div>
        <div class="user-profile">
          <img src="../assets/user.png" alt="User" width="50">
          <span><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
        </div>
      </header>

      <section class="attendance-section">
        <div class="report-header">
          <div>
            <h3>🌿 Employee Daily Status Overview</h3>
            <p>Track all employees’ current attendance status</p>
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
          <table id="attendance-table" data-sort-order="asc">
            <thead>
              <tr>
                <th onclick="sortTable(0)">Employee ID</th>
                <th onclick="sortTable(1)">Status</th>
                <th onclick="sortTable(2)">Time</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($attendanceData)): ?>
                <?php foreach ($attendanceData as $record): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($record['employeeID']); ?></td>
                    <td>
                      <?php 
                        // ✅ Convert numeric status to readable text
                        if ($record['attendance_status'] == 1) {
                            echo "<span style='color: green; font-weight: bold;'>Timed In</span>";
                        } else {
                            echo "<span style='color: red; font-weight: bold;'>Timed Out</span>";
                        }
                      ?>
                    </td>
                    <td><?php echo htmlspecialchars($record['time']); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" style="text-align:center; padding:15px;">No attendance records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
