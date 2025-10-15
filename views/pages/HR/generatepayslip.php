<?php 
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Redirect if not logged in
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
  <title>Employee Database | AgriTime</title>
  <link rel="stylesheet" href="../../styles/generate.css">
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
    let table = document.getElementById("employee-table");
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
          <h2>Employee Payslip</h2>
        </div>
        <div class="user-profile">
          <img src="../assets/user.png" alt="User" width="50">
          <span><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
        </div>
      </header>

            <section class="attendance-section">
            <div class="report-header">
                <div>
                <h3>🌿 Payslip Overview</h3>
                <p>Track all employee payslip data</p>
                </div>
                <button class="generate-btn">Generate Employee Payslip</button>
            </div>
            </section>

        <div class="table-container">
          <table id="employee-table">
            <thead>
              <tr>
                <th onclick="sortTable(0)">Created At</th>
                <th onclick="sortTable(1)">Start Date</th>
                <th onclick="sortTable(2)">End Date</th>
                <th onclick="sortTable(2)">Employee ID</th>
                <th onclick="sortTable(2)">Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
                // Placeholder data
                $employees = [
                  ["October 15, 2025", "October 1, 2025", "October 14, 2025", "EMP-12321", "Approved"],
                  ["October 15, 2025", "October 1, 2025", "October 14, 2025", "EMP-23323", "Declined"],
                  ["October 15, 2025", "October 1, 2025", "October 14, 2025", "EMP-56565", "Approved"],
                  ["October 15, 2025", "October 1, 2025", "October 14, 2025", "EMP-44586", "Declined"],
                ];

                foreach ($employees as $emp) {
                  $roleName = $emp[3] == "1" ? "Employee" : "HR";
                  echo "<tr>
                          <td>{$emp[0]}</td>
                          <td>{$emp[1]}</td>
                          <td>{$emp[2]}</td>
                          <td>{$emp[3]}</td>
                          <td>{$emp[4]}</td>
                          <td class='action-btns'>
                            <button class='view-btn' onclick=\"openModal('view', '{$emp[0]}', '{$emp[1]}', '{$emp[2]}', '{$emp[3]}')\">View</button>
                            <button class='edit-btn' onclick=\"openModal('edit', '{$emp[0]}', '{$emp[1]}', '{$emp[2]}', '{$emp[3]}')\">Edit</button>
                            <button class='delete-btn' onclick=\"deleteEmployee('{$emp[0]}')\">Delete</button>
                          </td>
                        </tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>

  <!-- ===== Modal ===== -->
  <div id="employeeModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">View Employee</h3>
        <span class="close-btn" onclick="closeModal()">&times;</span>
      </div>

      <div class="modal-body">
       <div class="profile-section">
          <img id="empProfilePic" src="../assets/user.jpg" alt="Profile Picture" width="100%">
        </div>

        <div class="form-grid">
          <input type="text" id="empID" placeholder="Employee ID" readonly>
          <input type="text" id="empFName" placeholder="First Name">
          <input type="text" id="empLName" placeholder="Last Name">
          <input type="date" id="empDOB" placeholder="Date of Birth">
          <input type="email" id="empEmail" placeholder="Email">
          <input type="password" id="empPassword" placeholder="Password">
          <input type="text" id="empContact" placeholder="Contact No">
          <select id="empRole">
            <option value="1">Employee</option>
            <option value="2">HR</option>
          </select>
          <input type="text" id="empNationality" placeholder="Nationality">
          <input type="text" id="empMarital" placeholder="Marital Status">
          <input type="text" id="empEmergency" placeholder="Emergency Contact">
          <input type="number" id="empBasic" placeholder="Basic Pay">
          <input type="number" id="empAllowance" placeholder="Allowances">
        </div>
      </div>

      <div class="modal-footer">
        <button class="save-btn" id="saveChanges" style="display:none;">Save Changes</button>
      </div>
    </div>
  </div>

  <script>
    let modal = document.getElementById("employeeModal");
    let saveBtn = document.getElementById("saveChanges");

    function openModal(mode, id, fname, lname, role) {
      modal.style.display = "block";
      document.getElementById("empID").value = id;
      document.getElementById("empFName").value = fname;
      document.getElementById("empLName").value = lname;
      document.getElementById("empRole").value = role;

      const isEdit = mode === "edit";
      document.getElementById("modalTitle").innerText = isEdit ? "Edit Employee" : "View Employee";

      // Toggle readonly inputs
      document.querySelectorAll('.form-grid input, .form-grid select').forEach(el => {
        el.readOnly = !isEdit;
        el.disabled = !isEdit;
      });

      // Profile upload toggle
      document.getElementById("empProfileUpload").style.display = isEdit ? "block" : "none";
      saveBtn.style.display = isEdit ? "inline-block" : "none";
    }

    function closeModal() {
      modal.style.display = "none";
    }

    function deleteEmployee(id) {
      if (confirm(`Are you sure you want to delete ${id}?`)) {
        alert(`${id} deleted successfully (placeholder only).`);
      }
    }

    window.onclick = function(event) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    }
  </script>
</body>
</html>
