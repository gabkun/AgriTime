<?php 
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Redirect if not logged in
if (!isset($_SESSION["user"])) {
    header("Location: /");
    exit;
}

$user = $_SESSION["user"];
$employeeID = $user["employeeID"];

echo "<script>console.log('Employee ID:', " . json_encode($employeeID) . ");</script>";

// ✅ API Base URL
$apiBaseUrl = "http://localhost:8080/api/attendance";

// ✅ Fetch Payslip List
$payslipUrl = "$apiBaseUrl/get/payslip/" . urlencode($employeeID);
$payslipResponse = @file_get_contents($payslipUrl);

$payslipData = [];

if ($payslipResponse !== FALSE) {
    $decodedPayslip = json_decode($payslipResponse, true);
    $payslipData = $decodedPayslip["data"] ?? [];

    // filter only logged-in employee
    $payslipData = array_values(array_filter($payslipData, function($p) use ($employeeID) {
        return isset($p["employeeID"]) && $p["employeeID"] === $employeeID;
    }));
}

// ✅ Format helpers
function fmtDate($iso) {
    if (!$iso) return "";
    return date("M d, Y", strtotime($iso));
}

function fmtMoney($amount) {
    return number_format((float)$amount, 2);
}

// ✅ TOTAL EARNINGS (sum of all gross)
$totalEarnings = 0;
if (!empty($payslipData)) {
    foreach ($payslipData as $p) {
        // gross might be string, ensure number
        $totalEarnings += (float)($p["gross"] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payslip Report | AgriTime</title>
<link rel="stylesheet" href="../../styles/attendanceReport.css">

<style>
/* ✅ small styling block for TOTAL EARNINGS */
.total-earnings-card{
    margin: 14px 0 18px;
    padding: 14px 16px;
    border-radius: 12px;
    border: 1px solid rgba(100,167,11,.25);
    background: rgba(100,167,11,.08);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 12px;
}
.total-earnings-card .label{
    font-weight: 700;
    letter-spacing: .3px;
    color:#1f2937;
}
.total-earnings-card .amount{
    font-size: 20px;
    font-weight: 800;
    color:#2e7d32;
}
.total-earnings-card small{
    display:block;
    margin-top: 2px;
    color:#64748b;
    font-weight: 500;
}
</style>

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
    let table = document.getElementById("payslip-table");
    let rows = Array.from(table.rows).slice(1);
    let asc = table.dataset.sortOrder !== "asc";

    rows.sort((a, b) => {
        let x = a.cells[n].innerText.toLowerCase();
        let y = b.cells[n].innerText.toLowerCase();

        // remove peso sign + commas
        let nx = parseFloat(x.replace(/[₱,]/g, ""));
        let ny = parseFloat(y.replace(/[₱,]/g, ""));

        if (!isNaN(nx) && !isNaN(ny)) {
            return asc ? nx - ny : ny - nx;
        }

        return asc ? x.localeCompare(y) : y.localeCompare(x);
    });

    table.dataset.sortOrder = asc ? "asc" : "desc";
    rows.forEach(row => table.tBodies[0].appendChild(row));
}
</script>

<body>

<div class="container">

<?php include('sidebar.php'); ?>

<div class="main-content">

<header class="header">
    <div class="logo">
        <img src="../assets/Agri.jpg" alt="Agri Logo" width="150">
        <h2>Payslip Report</h2>
    </div>

    <div class="user-profile">
        <img src="../assets/user.png" alt="User" width="50">
        <span>
            <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>
        </span>
    </div>
</header>


<section class="attendance-section">

<div class="report-header">
    <div>
        <h3>Payslip History</h3>
        <p>Track and review your generated payslips</p>
    </div>
</div>

<!-- ✅ TOTAL EARNINGS DISPLAY -->
<div class="total-earnings-card">
    <div>
        <div class="label">TOTAL EARNINGS</div>
        <small>Sum of Gross Pay from your payslip history</small>
    </div>
    <div class="amount">₱<?php echo fmtMoney($totalEarnings); ?></div>
</div>


<div class="table-container">

<table id="payslip-table" data-sort-order="asc">

<thead>
<tr>
    <th onclick="sortTable(0)">Payslip ID ⬍</th>
    <th onclick="sortTable(1)">Period Start ⬍</th>
    <th onclick="sortTable(2)">Period End ⬍</th>
    <th onclick="sortTable(3)">Total Hours ⬍</th>
    <th onclick="sortTable(4)">Overtime Hours ⬍</th>

    <!-- ✅ NEW COLUMN -->
    <th onclick="sortTable(5)">Gross Pay ⬍</th>

    <th onclick="sortTable(6)">SSS Deduction ⬍</th>
    <th onclick="sortTable(7)">Pag-IBIG Deduction ⬍</th>
    <th onclick="sortTable(8)">PhilHealth Deduction ⬍</th>
    <th onclick="sortTable(9)">Created ⬍</th>
</tr>
</thead>

<tbody>

<?php if (!empty($payslipData)): ?>

<?php foreach ($payslipData as $p): ?>

<tr>
    <td><?php echo htmlspecialchars($p["id"]); ?></td>

    <td><?php echo fmtDate($p["startDate"]); ?></td>

    <td><?php echo fmtDate($p["endDate"]); ?></td>

    <td><?php echo htmlspecialchars($p["totalHours"]); ?></td>

    <td><?php echo htmlspecialchars($p["overtimeHours"]); ?></td>

    <!-- ✅ NEW COLUMN VALUE -->
    <td>₱<?php echo fmtMoney($p["gross"] ?? 0); ?></td>

    <td>₱<?php echo fmtMoney($p["sssDeduction"]); ?></td>

    <td>₱<?php echo fmtMoney($p["pagibigDeduction"]); ?></td>

    <td>₱<?php echo fmtMoney($p["philhealthDeduction"]); ?></td>

    <td><?php echo fmtDate($p["created"]); ?></td>
</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="10" style="text-align:center; padding:20px;">
No payslip records found.
</td>
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