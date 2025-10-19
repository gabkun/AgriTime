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
$apiUrl = "http://localhost:8080/api/user/";

// ✅ Fetch all users
$userResponse = @file_get_contents($apiUrl);
$employees = [];

if ($userResponse !== FALSE) {
  $decoded = json_decode($userResponse, true);
  if (is_array($decoded)) {
    // ✅ FRONTEND ROLE FILTER: Show only role 1 and 2
    $employees = array_filter($decoded, function($emp) {
      return isset($emp["role"]) && in_array($emp["role"], [1, 2]);
    });
  }
} else {
  $employees = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Database | AgriTime</title>
  <link rel="stylesheet" href="../../styles/allusers.css">
</head>

<body>
  <div class="container">
    <?php include('sidebar.php'); ?>

    <div class="main-content">
      <header class="header">
        <div class="logo">
          <img src="../assets/Agri.jpg" alt="Agri Logo" width="150">
          <h2> Employee Management</h2>
        </div>
        <div class="user-profile">
          <img src="../assets/grit.jpg" alt="User" width="50">
          <span><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
        </div>
      </header>

      <section class="attendance-section">
        <div class="report-header">
          <div>
            <h3> Employee Overview</h3>
            <p>Track, search, and manage employee data</p>
          </div>
          <div class="filter-box">
            <input type="text" id="searchInput" placeholder="Search employee...">
          </div>
        </div>

        <div class="table-container">
          <table id="employee-table">
            <thead>
              <tr>
                <th onclick="sortTable(0)">Employee ID ⬍</th>
                <th onclick="sortTable(1)">First Name ⬍</th>
                <th onclick="sortTable(2)">Last Name ⬍</th>
                <th onclick="sortTable(3)">Role ⬍</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $emp): ?>
                  <?php 
                    // ✅ Determine role name
                    if ($emp["role"] == "1") {
                      $roleName = "Employee";
                    } elseif ($emp["role"] == "2") {
                      $roleName = "HR";
                    } else {
                      continue; // Skip roles not 1 or 2 (safety)
                    }

                    $empID = htmlspecialchars($emp["employeeID"] ?? '');
                    $firstName = htmlspecialchars($emp["firstName"] ?? '');
                    $lastName = htmlspecialchars($emp["lastName"] ?? '');
                    $dob = htmlspecialchars($emp["dob"] ?? '');
                    $email = htmlspecialchars($emp["email"] ?? '');
                    $contactNo = htmlspecialchars($emp["contactNo"] ?? '');
                    $nationality = htmlspecialchars($emp["nationality"] ?? '');
                    $maritalStatus = htmlspecialchars($emp["maritalStatus"] ?? '');
                    $emergencyContact = htmlspecialchars($emp["emergencyContact"] ?? '');
                    $basicPay = htmlspecialchars($emp["basicPay"] ?? '');
                    $allowances = htmlspecialchars($emp["allowances"] ?? '');
                    $role = htmlspecialchars($emp["role"] ?? '');
                  ?>
                  <tr>
                    <td><?= $empID ?></td>
                    <td><?= $firstName ?></td>
                    <td><?= $lastName ?></td>
                    <td><?= $roleName ?></td>
                    <td class="action-btns">
                      <button class="view-btn" onclick="openModal('view', '<?= $empID ?>', '<?= $firstName ?>', '<?= $lastName ?>', '<?= $role ?>', '<?= $dob ?>', '<?= $email ?>', '<?= $contactNo ?>', '<?= $nationality ?>', '<?= $maritalStatus ?>', '<?= $basicPay ?>', '<?= $allowances ?>')">View</button>
                      <button class="edit-btn" onclick="openModal('edit', '<?= $empID ?>', '<?= $firstName ?>', '<?= $lastName ?>', '<?= $role ?>')">Edit</button>
                      <button class="delete-btn" onclick="deleteEmployee('<?= $empID ?>')">Delete</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No employees found with role 1 or 2.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>

  <!-- ===== Modal Section ===== -->
  <?php include('employee_modal.php'); ?>

  <script>
    // ===== Sorting Function =====
    function sortTable(n) {
      const table = document.getElementById("employee-table");
      let rows = Array.from(table.rows).slice(1);
      let asc = table.getAttribute("data-sort") !== "asc";
      rows.sort((a, b) => {
        const valA = a.cells[n].innerText.toLowerCase();
        const valB = b.cells[n].innerText.toLowerCase();
        return asc ? valA.localeCompare(valB) : valB.localeCompare(valA);
      });
      table.tBodies[0].append(...rows);
      table.setAttribute("data-sort", asc ? "asc" : "desc");
    }

    // ===== Search Function =====
    document.getElementById("searchInput").addEventListener("keyup", function () {
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll("#employee-table tbody tr");
      rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
      });
    });
  </script>
</body>
</html>
