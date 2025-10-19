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
              <!-- ✅ 5 Sample Records -->
              <tr>
                <td>1</td>
                <td>EMP001</td>
                <td>2025-10-01</td>
                <td>2025-10-15</td>
                <td>80</td>
                <td>5</td>
                <td>₱500.00</td>
                <td>₱300.00</td>
                <td>₱350.00</td>
                <td>2025-10-16</td>
                <td><button class="view-btn" onclick="viewPayslip('EMP001')"> View Payslip</button></td>
              </tr>
              <tr>
                <td>2</td>
                <td>EMP002</td>
                <td>2025-10-01</td>
                <td>2025-10-15</td>
                <td>82</td>
                <td>2</td>
                <td>₱480.00</td>
                <td>₱300.00</td>
                <td>₱320.00</td>
                <td>2025-10-16</td>
                <td><button class="view-btn" onclick="viewPayslip('EMP002')">View Payslip</button></td>
              </tr>
              <tr>
                <td>3</td>
                <td>EMP003</td>
                <td>2025-10-01</td>
                <td>2025-10-15</td>
                <td>76</td>
                <td>3</td>
                <td>₱450.00</td>
                <td>₱280.00</td>
                <td>₱310.00</td>
                <td>2025-10-16</td>
                <td><button class="view-btn" onclick="viewPayslip('EMP003')"> View Payslip</button></td>
              </tr>
              <tr>
                <td>4</td>
                <td>EMP004</td>
                <td>2025-10-01</td>
                <td>2025-10-15</td>
                <td>88</td>
                <td>6</td>
                <td>₱520.00</td>
                <td>₱310.00</td>
                <td>₱360.00</td>
                <td>2025-10-16</td>
                <td><button class="view-btn" onclick="viewPayslip('EMP004')"> View Payslip</button></td>
              </tr>
              <tr>
                <td>5</td>
                <td>EMP005</td>
                <td>2025-10-01</td>
                <td>2025-10-15</td>
                <td>79</td>
                <td>4</td>
                <td>₱500.00</td>
                <td>₱290.00</td>
                <td>₱340.00</td>
                <td>2025-10-16</td>
                <td><button class="view-btn" onclick="viewPayslip('EMP005')"> View Payslip</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>

  <script>
    function viewPayslip(employeeID) {
      alert("Viewing payslip for: " + employeeID);
      // Redirect or modal logic can go here
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
