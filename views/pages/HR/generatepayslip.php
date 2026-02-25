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

  <div style="display:flex; gap:10px;">

    <!-- DOWNLOAD BUTTON -->
    <a href="http://localhost:8080/api/attendance/get/all/payslip/excel" target="_blank">
      <button class="download-btn" style="background:#16a34a;">
        Download Excel
      </button>
    </a>

    <!-- GENERATE BUTTON -->
    <button class="generate-btn">
      Generate Employee Payslip
    </button>

  </div>
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

<button class="view-btn"
  onclick="openModal(
    'view',

    '<?= $p['employeeID'] ?>',
    '<?= $p['startDate'] ?>',
    '<?= $p['endDate'] ?>',

    '<?= $p['totalHours'] ?? 0 ?>',
    '<?= $p['overtimeHours'] ?? 0 ?>',
    '<?= $p['o_amount'] ?? 0 ?>',

    '<?= $p['holiday_days'] ?? 0 ?>',
    '<?= $p['h_amount'] ?? 0 ?>',

    '<?= $p['rd_days'] ?? 0 ?>',
    '<?= $p['rd_amount'] ?? 0 ?>',

    '<?= $p['dailyBasicPay'] ?? 0 ?>',
    '<?= $p['basicpay'] ?? 0 ?>',
    '<?= $p['gross'] ?? 0 ?>',

    '<?= $p['sssDeduction'] ?? 0 ?>',
    '<?= $p['pagibigDeduction'] ?? 0 ?>',
    '<?= $p['philhealthDeduction'] ?? 0 ?>',

    '<?= $p['created'] ?>'
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

  <div class="form-group">
    <label>Employee ID</label>
    <input type="text" id="empID" readonly>
  </div>

  <div class="form-group">
    <label>Start Date</label>
    <input type="text" id="empStartDate" readonly>
  </div>

  <div class="form-group">
    <label>End Date</label>
    <input type="text" id="empEndDate" readonly>
  </div>

  <div class="form-group">
    <label>Created Date</label>
    <input type="text" id="empCreated" readonly>
  </div>


  <div class="form-group">
    <label>Total Hours</label>
    <input type="number" id="empTotalHours" readonly>
  </div>

  <div class="form-group">
    <label>Overtime Hours</label>
    <input type="number" id="empOvertime" readonly>
  </div>

  <div class="form-group">
    <label>Overtime Amount (₱)</label>
    <input type="number" id="empOvertimeAmount" readonly>
  </div>


  <div class="form-group">
    <label>Holiday Days</label>
    <input type="number" id="empHolidayDays" readonly>
  </div>

  <div class="form-group">
    <label>Holiday Amount (₱)</label>
    <input type="number" id="empHolidayAmount" readonly>
  </div>


  <div class="form-group">
    <label>Rest Day Count</label>
    <input type="number" id="empRddays" readonly>
  </div>

  <div class="form-group">
    <label>Rest Day Amount (₱)</label>
    <input type="number" id="empRdAmount" readonly>
  </div>


  <div class="form-group">
    <label>Daily Basic Pay (₱)</label>
    <input type="text" id="empDailyBasicPay" readonly>
  </div>

  <div class="form-group">
    <label>Total Basic Pay (₱)</label>
    <input type="text" id="empBasicPay" readonly>
  </div>

  <div class="form-group">
    <label>Gross Pay (₱)</label>
    <input type="text" id="empGross" readonly>
  </div>


  <div class="form-group">
    <label>SSS Deduction (₱)</label>
    <input type="number" id="empSSS" readonly>
  </div>

  <div class="form-group">
    <label>Pag-IBIG Deduction (₱)</label>
    <input type="number" id="empPagibig" readonly>
  </div>

  <div class="form-group">
    <label>PhilHealth Deduction (₱)</label>
    <input type="number" id="empPhilhealth" readonly>
  </div>

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
            <!-- Employee -->
  <input type="text"
         id="genEmployeeID"
         placeholder="Employee ID (e.g. EMP-22810)"
         required>

  <input type="date"
         id="genStartDate"
         placeholder="Start Date"
         required>

  <input type="date"
         id="genEndDate"
         placeholder="End Date"
         required>


  <!-- OVERTIME -->
  <input type="number"
         id="genOvertimeHours"
         placeholder="Overtime Hours">

  <input type="number"
         id="genOvertimeAmount"
         placeholder="Overtime Amount (₱)">


  <!-- REST DAY -->
  <input type="number"
         id="genRddays"
         placeholder="Rest Day Count">

  <input type="number"
         id="genRdAmount"
         placeholder="Rest Day Amount (₱)">


  <!-- HOLIDAY -->
  <input type="number"
         id="genHolidayDays"
         placeholder="Holiday Count">

  <input type="number"
         id="genHolidayAmount"
         placeholder="Holiday Amount (₱)">


  <!-- DEDUCTIONS -->
  <input type="number"
         id="genSSS"
         placeholder="SSS Deduction (₱)"
         required>

  <input type="number"
         id="genPagibig"
         placeholder="Pag-IBIG Deduction (₱)"
         required>

  <input type="number"
         id="genPhilhealth"
         placeholder="PhilHealth Deduction (₱)"
         required>
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

function formatDate(iso) {
  if (!iso) return "";
  const d = new Date(iso);
  if (isNaN(d.getTime())) return iso; // fallback if not valid date
  return d.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "2-digit" });
}

function openModal(
  mode,
  employeeID, startDate, endDate,
  totalHours, overtimeHours, o_amount,
  holiday_days, h_amount,
  rd_days, rd_amount,
  dailyBasicPay, basicpay, gross,
  sssDeduction, pagibigDeduction, philhealthDeduction,
  created
) {
  viewModal.style.display = "block";

  // ID + Dates
  document.getElementById("empID").value = employeeID;
  document.getElementById("empStartDate").value = formatDate(startDate);
  document.getElementById("empEndDate").value = formatDate(endDate);
  document.getElementById("empCreated").value = formatDate(created);

  // Hours + OT
  document.getElementById("empTotalHours").value = totalHours ?? 0;
  document.getElementById("empOvertime").value = overtimeHours ?? 0;
  document.getElementById("empOvertimeAmount").value = o_amount ?? 0;

  // Holiday
  document.getElementById("empHolidayDays").value = holiday_days ?? 0;
  document.getElementById("empHolidayAmount").value = h_amount ?? 0;

  // Rest Day
  document.getElementById("empRddays").value = rd_days ?? 0;
  document.getElementById("empRdAmount").value = rd_amount ?? 0;

  // Pay
  document.getElementById("empDailyBasicPay").value = dailyBasicPay ?? 0;
  document.getElementById("empBasicPay").value = basicpay ?? 0;
  document.getElementById("empGross").value = gross ?? 0;

  // Deductions
  document.getElementById("empSSS").value = sssDeduction ?? 0;
  document.getElementById("empPagibig").value = pagibigDeduction ?? 0;
  document.getElementById("empPhilhealth").value = philhealthDeduction ?? 0;

  const isEdit = mode === "edit";
  document.getElementById("modalTitle").innerText = isEdit ? "Edit Payslip" : "View Payslip";

  document.querySelectorAll("#employeeModal .form-grid input").forEach(el => {
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

function numVal(id) {
  const v = document.getElementById(id).value;
  const n = Number(v);
  return Number.isFinite(n) ? n : 0;
}

async function generatePayslip(event) {
  event.preventDefault();

  // ✅ Match backend request body EXACTLY
  const data = {
    employeeID: document.getElementById("genEmployeeID").value.trim(),
    startDate: document.getElementById("genStartDate").value,
    endDate: document.getElementById("genEndDate").value,

    overtimeHours: numVal("genOvertimeHours"),
    o_amount: numVal("genOvertimeAmount"),

    rd_days: numVal("genRddays"),
    rd_amount: numVal("genRdAmount"),

    holiday_days: numVal("genHolidayDays"),
    h_amount: numVal("genHolidayAmount"),

    sssDeduction: numVal("genSSS"),
    pagibigDeduction: numVal("genPagibig"),
    philhealthDeduction: numVal("genPhilhealth")
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
      alert("Failed: " + (result.message || "Unknown error"));
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