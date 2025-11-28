<?php  
session_start();
date_default_timezone_set("Asia/Manila");

// Redirect if not logged in
if (!isset($_SESSION["user"])) {
  header("Location: /");
  exit;
}

$user = $_SESSION["user"];

// Fetch payslip data from backend API
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

<body>
  <div class="container">
    <?php include('sidebar.php'); ?>

    <div class="main-content">

      <!-- HEADER -->
      <header class="header">
        <div class="logo">
          <img src="../assets/Agri.jpg" alt="Agri Logo" width="150">
          <h2>Employee Payslip</h2>
        </div>
        <div class="user-profile">
          <img src="../assets/user.png" alt="User" width="50">
          <span><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></span>
        </div>
      </header>

      <!-- PAGE HEADER -->
      <section class="attendance-section">
        <div class="report-header">
          <div>
            <h3>Payslip Overview</h3>
            <p>Track all employee payslip data</p>
          </div>
          <button class="generate-btn">Generate Employee Payslip</button>
        </div>
      </section>

      <!-- PAYSLIP TABLE -->
      <div class="table-container">
        <table id="employee-table">
          <thead>
            <tr>
              <th>Created At</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Employee ID</th>
              <th>Status</th>
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
                ?>

                <tr>
                  <td><?= $created ?></td>
                  <td><?= $start ?></td>
                  <td><?= $end ?></td>
                  <td><?= htmlspecialchars($p["employeeID"]) ?></td>
                  <td>Approved</td>
                  <td class="action-btns">

                    <!-- VIEW BUTTON (passes full data to modal) -->
                    <button class="view-btn"
                      onclick="openModal(
                        'view',
                        '<?= $p['employeeID'] ?>',
                        '<?= $start ?>',
                        '<?= $end ?>',
                        '<?= $p['totalHours'] ?>',
                        '<?= $p['overtimeHours'] ?>',
                        '<?= $p['sssDeduction'] ?>',
                        '<?= $p['pagibigDeduction'] ?>',
                        '<?= $p['philhealthDeduction'] ?>'
                      )">
                      View
                    </button>

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


  <!-- VIEW / EDIT MODAL -->
  <div id="employeeModal" class="modal">
    <div class="modal-content">

      <div class="modal-header">
        <h3 id="modalTitle">View Payslip</h3>
        <span class="close-btn" onclick="closeModal()">&times;</span>
      </div>

      <div class="modal-body">

        <div class="form-grid">
          <input type="text" id="empID" placeholder="Employee ID" readonly>
          <input type="text" id="empStartDate" placeholder="Start Date" readonly>
          <input type="text" id="empEndDate" placeholder="End Date" readonly>
          <input type="number" id="empTotalHours" placeholder="Total Hours" readonly>
          <input type="number" id="empOvertime" placeholder="Overtime Hours" readonly>
          <input type="number" id="empSSS" placeholder="SSS Deduction" readonly>
          <input type="number" id="empPagibig" placeholder="Pag-ibig Deduction" readonly>
          <input type="number" id="empPhilhealth" placeholder="PhilHealth Deduction" readonly>
        </div>

      </div>

      <div class="modal-footer">
        <button id="saveChanges" class="save-btn" style="display:none;">Save Changes</button>
      </div>

    </div>
  </div>


  <!-- GENERATE MODAL -->
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
            <input type="date" id="genStartDate" required>
            <input type="date" id="genEndDate" required>
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
/* ==========================
      VIEW / EDIT MODAL
========================== */
let viewModal = document.getElementById("employeeModal");
let saveBtn = document.getElementById("saveChanges");

function openModal(mode, id, start, end, total, overtime, sss, pagibig, philhealth) {

  viewModal.style.display = "block";

  document.getElementById("empID").value = id;
  document.getElementById("empStartDate").value = start;
  document.getElementById("empEndDate").value = end;
  document.getElementById("empTotalHours").value = total;
  document.getElementById("empOvertime").value = overtime;
  document.getElementById("empSSS").value = sss;
  document.getElementById("empPagibig").value = pagibig;
  document.getElementById("empPhilhealth").value = philhealth;

  const isEdit = mode === "edit";

  document.getElementById("modalTitle").innerText = 
      isEdit ? "Edit Payslip" : "View Payslip";

  document.querySelectorAll(".form-grid input").forEach(el => {
    el.readOnly = !isEdit;
  });

  saveBtn.style.display = isEdit ? "block" : "none";
}

function closeModal() {
  viewModal.style.display = "none";
}


/* ==========================
        GENERATE MODAL
========================== */
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
      alert("Payslip generated successfully!");
      closeGenerateModal();
      location.reload();
    } else {
      alert("Failed: " + result.message);
    }
  } catch (err) {
    console.error(err);
    alert("Network error.");
  }
}


/* ==========================
    CLOSE ON OUTSIDE CLICK
========================== */
window.onclick = function(event) {
  if (event.target === viewModal) viewModal.style.display = "none";
  if (event.target === generateModal) generateModal.style.display = "none";
}
</script>

</body>
</html>
