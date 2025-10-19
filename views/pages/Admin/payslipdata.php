<?php 
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Redirect if not logged in
if (!isset($_SESSION["user"])) {
  header("Location: /");
  exit;
}

$user = $_SESSION["user"];

// ✅ API Base URL
$apiUrl = "http://localhost:8080/api/attendance/get/all/payslip";

// ✅ Fetch all payslips
$payslipResponse = @file_get_contents($apiUrl);
$payslips = [];

if ($payslipResponse !== FALSE) {
  $decoded = json_decode($payslipResponse, true);
  if (isset($decoded["data"]) && is_array($decoded["data"])) {
    $payslips = $decoded["data"];
  }
} else {
  $payslips = [];
}
?>
<!DOCTYPE html> 
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Payslip Records</title>
  <link rel="stylesheet" href="../../styles/payslipdata.css">
</head>
<body>
  <div class="container">
    <?php include('sidebar.php'); ?>

    <div class="main-content">
      <header class="header">
        <div class="logo">
          <img src="../assets/Agri.jpg" alt="Agri Logo" width="150">
          <h2>Employee Payslip Management</h2>
        </div>
        <div class="user-profile">
          <img src="../assets/user.png" alt="User" width="50">
        </div>
      </header>

      <section class="attendance-section">
        <div class="report-header">
          <div>
            <h3> Employee Payslip Overview</h3>
            <p>Manage and view all employees’ payslip records</p>
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
          <table id="payslip-table" data-sort-order="asc">
            <thead>
              <tr>
                <th onclick="sortTable(0)">ID</th>
                <th onclick="sortTable(1)">Employee ID</th>
                <th onclick="sortTable(2)">Start Date</th>
                <th onclick="sortTable(3)">End Date</th>
                <th onclick="sortTable(4)">Total Hours</th>
                <th onclick="sortTable(5)">Overtime Hours</th>
                <th onclick="sortTable(6)">SSS Deduction</th>
                <th onclick="sortTable(7)">Pag-IBIG Deduction</th>
                <th onclick="sortTable(8)">PhilHealth Deduction</th>
                <th onclick="sortTable(9)">Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($payslips)): ?>
                <?php foreach ($payslips as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p["id"]) ?></td>
                    <td><?= htmlspecialchars($p["employeeID"]) ?></td>
                    <td><?= date("Y-m-d", strtotime($p["startDate"])) ?></td>
                    <td><?= date("Y-m-d", strtotime($p["endDate"])) ?></td>
                    <td><?= htmlspecialchars($p["totalHours"]) ?></td>
                    <td><?= htmlspecialchars($p["overtimeHours"]) ?></td>
                    <td>₱<?= number_format($p["sssDeduction"], 2) ?></td>
                    <td>₱<?= number_format($p["pagibigDeduction"], 2) ?></td>
                    <td>₱<?= number_format($p["philhealthDeduction"], 2) ?></td>
                    <td><?= date("Y-m-d", strtotime($p["created"])) ?></td>
                    <td><button class="view-btn" onclick="viewPayslip('<?= $p['employeeID'] ?>')">View Payslip</button></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="11" style="text-align:center;">No payslip records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>

  <script>
    function viewPayslip(employeeID) {
      alert("Viewing payslip for: " + employeeID);
      // You can add modal or redirect here:
      // window.location.href = "viewPayslip.php?id=" + encodeURIComponent(employeeID);
    }

    function sortTable(n) {
      let table = document.getElementById("payslip-table");
      let rows = Array.from(table.rows).slice(1);
      let asc = table.getAttribute("data-sort-order") === "asc";
      rows.sort((a, b) => {
        let x = a.cells[n].innerText.toLowerCase();
        let y = b.cells[n].innerText.toLowerCase();
        return asc ? x.localeCompare(y) : y.localeCompare(x);
      });
      table.setAttribute("data-sort-order", asc ? "desc" : "asc");
      rows.forEach(r => table.tBodies[0].appendChild(r));
    }
  </script>
</body>
</html>
