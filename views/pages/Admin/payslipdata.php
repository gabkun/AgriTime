<?php 
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Redirect if not logged in
if (!isset($_SESSION["user"])) {
  header("Location: /");
  exit;
}

$user = $_SESSION["user"];

// ✅ API URL
$apiUrl = "http://localhost:8080/api/attendance/get/all/payslip";

/* ======================================================
   ✅ PAYSLIP DOWNLOAD HANDLER (BY PAYSLIP ID)
   ====================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["download_payslip_id"])) {

    $payslipId = intval($_POST["download_payslip_id"]);
    $downloadUrl = "http://localhost:8080/api/attendance/download/payslip/" . urlencode($payslipId);

    $tempFile = tempnam(sys_get_temp_dir(), "payslip_") . ".pdf";

    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "Accept: application/pdf\r\n"
        ]
    ]);

    $pdfContent = @file_get_contents($downloadUrl, false, $context);

    if ($pdfContent === false) {
        echo "<script>alert('No payslip available for this record');</script>";
    } else {
        file_put_contents($tempFile, $pdfContent);

        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"Payslip_ID_{$payslipId}.pdf\"");
        header("Content-Length: " . filesize($tempFile));

        readfile($tempFile);
        unlink($tempFile);
        exit;
    }
}

/* ======================================================
   ✅ FETCH ALL PAYSLIPS
   ====================================================== */
$payslipResponse = @file_get_contents($apiUrl);
$payslips = [];

if ($payslipResponse !== FALSE) {
  $decoded = json_decode($payslipResponse, true);
  if (isset($decoded["data"]) && is_array($decoded["data"])) {
    $payslips = $decoded["data"];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Payslip Records</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
          <h3>Employee Payslip Overview</h3>
          <p>View and export employee payslip records</p>
        </div>
      </div>

      <div class="table-container">
        <table id="payslip-table">
          <thead>
            <tr>
              <th>Payslip ID</th>
              <th>Employee ID</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Total Hours</th>
              <th>OT Hours</th>
              <th>SSS</th>
              <th>Pag-IBIG</th>
              <th>PhilHealth</th>
              <th>Created</th>
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
                <td>
                  <!-- ✅ WORKING DOWNLOAD BUTTON -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="download_payslip_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="view-btn">
                      Export PDF
                    </button>
                  </form>
                </td>
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

</body>
</html>
