<?php  
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Redirect if not logged in
if (!isset($_SESSION["user"])) {
  header("Location: /");
  exit;
}

$user = $_SESSION["user"];

// ✅ Fetch payslip data from backend API
$apiUrl = "http://localhost:8080/api/attendance/get/all/payslip";

$response = @file_get_contents($apiUrl);
$payslips = [];

if ($response !== FALSE) {
  $decoded = json_decode($response, true);
  $payslips = $decoded["data"] ?? [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Payslip | AgriTime</title>
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
            <h3>Payslip Overview</h3>
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
              <th onclick="sortTable(3)">Employee ID</th>
              <th onclick="sortTable(4)">Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($payslips)): ?>
              <?php foreach ($payslips as $p): ?>
                <?php 
                  $created = date("F d, Y", strtotime($p["created"]));
                  $start = date("F d, Y", strtotime($p["startDate"]));
                  $end = date("F d, Y", strtotime($p["endDate"]));
                  $employeeID = htmlspecialchars($p["employeeID"]);
                  $status = "Approved"; // You can modify if you have status field
                ?>
                <tr>
                  <td><?= $created ?></td>
                  <td><?= $start ?></td>
                  <td><?= $end ?></td>
                  <td><?= $employeeID ?></td>
                  <td><?= $status ?></td>
                  <td class="action-btns">
                    <button class="view-btn" onclick="openModal('view', '<?= $employeeID ?>', '<?= $start ?>', '<?= $end ?>', '<?= $status ?>')">View</button>
                    <button class="edit-btn" onclick="openModal('edit', '<?= $employeeID ?>', '<?= $start ?>', '<?= $end ?>', '<?= $status ?>')">Edit</button>
                    <button class="delete-btn" onclick="deleteEmployee('<?= $employeeID ?>')">Delete</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align:center;">No payslip data available.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ===== View/Edit Payslip Modal ===== -->
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
          <input type="text" id="empStartDate" placeholder="Start Date">
          <input type="text" id="empEndDate" placeholder="End Date">
          <input type="number" id="empTotalHours" placeholder="Total Hours">
          <input type="number" id="empOvertime" placeholder="Overtime Hours">
          <input type="number" id="empSSS" placeholder="SSS Deduction">
          <input type="number" id="empPagibig" placeholder="Pag-ibig Deduction">
          <input type="number" id="empPhilhealth" placeholder="PhilHealth Deduction">
        </div>
      </div>

      <div class="modal-footer">
        <button class="save-btn" id="saveChanges" style="display:none;">Save Changes</button>
      </div>
    </div>
  </div>

  <!-- ===== Generate Payslip Modal ===== -->
  <div id="generateModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Generate Employee Payslip</h3>
        <span class="close-btn" onclick="closeGenerateModal()">&times;</span>
      </div>

      <div class="modal-body">
        <form id="generateForm" onsubmit="generatePayslip(event)">
          <div class="form-grid">
            <input type="text" id="genEmployeeID" placeholder="Employee ID" required>
            <input type="date" id="genStartDate" placeholder="Start Date" required>
            <input type="date" id="genEndDate" placeholder="End Date" required>
            <input type="number" id="genSSS" placeholder="SSS Deduction" required>
            <input type="number" id="genPagibig" placeholder="Pag-ibig Deduction" required>
            <input type="number" id="genPhilhealth" placeholder="PhilHealth Deduction" required>
          </div>

          <div class="modal-footer">
            <button type="submit" class="save-btn">Generate Payslip</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    let modal = document.getElementById("employeeModal");
    let saveBtn = document.getElementById("saveChanges");

    function openModal(mode, id, start, end, status) {
      modal.style.display = "block";
      document.getElementById("empID").value = id;
      document.getElementById("empStartDate").value = start;
      document.getElementById("empEndDate").value = end;

      const isEdit = mode === "edit";
      document.getElementById("modalTitle").innerText = isEdit ? "Edit Payslip" : "View Payslip";

      document.querySelectorAll('.form-grid input').forEach(el => {
        el.readOnly = !isEdit;
      });

      saveBtn.style.display = isEdit ? "inline-block" : "none";
    }

    function closeModal() {
      modal.style.display = "none";
    }

    function deleteEmployee(id) {
      if (confirm(`Are you sure you want to delete payslip for ${id}?`)) {
        alert(`Payslip for ${id} deleted successfully (placeholder only).`);
      }
    }

    // ===== Generate Payslip Modal Functions =====
    const generateModal = document.getElementById("generateModal");

    document.querySelector(".generate-btn").addEventListener("click", () => {
      generateModal.style.display = "block";
    });

    function closeGenerateModal() {
      generateModal.style.display = "none";
    }

    async function generatePayslip(event) {
      event.preventDefault();

      const data = {
        employeeID: document.getElementById("genEmployeeID").value,
        startDate: document.getElementById("genStartDate").value,
        endDate: document.getElementById("genEndDate").value,
        sssDeduction: parseFloat(document.getElementById("genSSS").value),
        pagibigDeduction: parseFloat(document.getElementById("genPagibig").value),
        philhealthDeduction: parseFloat(document.getElementById("genPhilhealth").value)
      };

      try {
        const response = await fetch("http://localhost:8080/api/attendance/generate", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
          alert("✅ Payslip generated successfully!");
          closeGenerateModal();
          location.reload();
        } else {
          alert("❌ Failed to generate payslip: " + (result.message || "Redirecting to Dashboard"));
        }
      } catch (error) {
        console.error("Redirecting to Payslip:", error);
        alert("Redirecting to Payslip:");
      }
    }

    // Close modals on outside click
    window.onclick = function(event) {
      if (event.target === modal) modal.style.display = "none";
      if (event.target === generateModal) generateModal.style.display = "none";
    }
  </script>
</body>
</html>
