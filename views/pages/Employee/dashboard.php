<?php 
session_start();
date_default_timezone_set("Asia/Manila");

// ✅ Redirect if not logged in
if (!isset($_SESSION["user"])) {
    header("Location: /");
    exit;
}

$user = $_SESSION["user"];
$currentTime = date('g : i A');

$employeeID = $user["employeeID"];
echo "<script>console.log('Employee ID: " . addslashes($employeeID) . "');</script>";


// ✅ API base URLs
$apiBaseUrl = "http://localhost:8080/api/attendance";
$payslipBaseUrl = "http://localhost:8080/api/attendance";

// ✅ PAYSLIP DOWNLOAD REQUEST (no cURL)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["download"])) {
    $employeeID = $user["employeeID"];
    $downloadUrl = "http://localhost:8080/api/attendance/download/" . urlencode($employeeID);

    $tempFile = tempnam(sys_get_temp_dir(), "payslip_") . ".pdf";

    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "Accept: application/pdf\r\n"
        ]
    ]);

    $pdfContent = @file_get_contents($downloadUrl, false, $context);

    if ($pdfContent === false) {
        echo "<script>alert('Error downloading payslip. Please try again.');</script>";
    } else {
        file_put_contents($tempFile, $pdfContent);

        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"Payslip_{$employeeID}.pdf\"");
        readfile($tempFile);
        unlink($tempFile);
        exit;
    }
}
// ✅ Fetch Daily Status
$employeeID = $user["employeeID"];
$statusUrl = "$apiBaseUrl/status/$employeeID";

$statusResponse = @file_get_contents($statusUrl);
$dailyStatus = null;

if ($statusResponse !== FALSE) {
    $decoded = json_decode($statusResponse, true);
    $dailyStatus = $decoded["attendance_status"] ?? null;
}

$timestampResponse = @file_get_contents($statusUrl);
$dailyTimestamp = null;

if ($timestampResponse !== FALSE) {
    $decodedTime = json_decode($timestampResponse, true);
    $dailyTimestamp = $decodedTime["time"] ?? null;
}

// ✅ Handle Time-In and Time-Out actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $user["employeeID"];

    // Common function to send POST request
    function callAttendanceAPI($url, $data) {
        $options = [
            "http" => [
                "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
                "method"  => "POST",
                "content" => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        return @file_get_contents($url, false, $context);
    }

    // ✅ TIME IN
    if (isset($_POST["time_in"])) {
        $url = "$apiBaseUrl/timein";
        $result = callAttendanceAPI($url, ["employeeID" => $employeeID]);

        if ($result === FALSE) {
            echo "<script>alert('Error connecting to server.');</script>";
        } else {
            $response = json_decode($result, true);
            $message = $response["message"] ?? "Unknown response";

            if (strpos(strtolower($message), 'success') !== false) {
                echo "<script>alert('" . addslashes($message) . "'); window.location.reload();</script>";
            } else {
                echo "<script>alert('" . addslashes($message) . "');</script>";
            }
        }
    }

    // ✅ TIME OUT
    if (isset($_POST["time_out"])) {
        $url = "$apiBaseUrl/timeout";
        $result = callAttendanceAPI($url, ["employeeID" => $employeeID]);

        if ($result === FALSE) {
            echo "<script>alert('Error connecting to server.');</script>";
        } else {
            $response = json_decode($result, true);
            $message = $response["message"] ?? "Unknown response";

            if (strpos(strtolower($message), 'success') !== false) {
                echo "<script>alert('" . addslashes($message) . "'); window.location.reload();</script>";
            } else {
                echo "<script>alert('" . addslashes($message) . "');</script>";
            }
        }
    }

    // ✅ BREAK IN
if (isset($_POST["break_in"])) {
    $url = "$apiBaseUrl/breakin";
    $result = callAttendanceAPI($url, ["employeeID" => $employeeID]);

    if ($result === FALSE) {
        echo "<script>alert('Redirecting to Dashboard.');</script>";
    } else {
        $response = json_decode($result, true);
        $message = $response["message"] ?? "Unknown response";

        if (strpos(strtolower($message), 'success') !== false) {
            echo "<script>alert('" . addslashes($message) . "'); window.location.reload();</script>";
        } else {
            echo "<script>alert('" . addslashes($message) . "');</script>";
        }
    }
}

      // ✅ BREAK OUT
      if (isset($_POST["break_out"])) {
          $url = "$apiBaseUrl/breakout";
          $result = callAttendanceAPI($url, ["employeeID" => $employeeID]);

          if ($result === FALSE) {
              echo "<script>alert('Redirecting to Dashboard.');</script>";
          } else {
              $response = json_decode($result, true);
              $message = $response["message"] ?? "Unknown response";

              if (strpos(strtolower($message), 'success') !== false) {
                  echo "<script>alert('" . addslashes($message) . "'); window.location.reload();</script>";
              } else {
                  echo "<script>alert('" . addslashes($message) . "');</script>";
              }
          }
      }
}

// ✅ Role Mapping
$roleName = match($user['role']) {
    1 => 'Employee',
    2 => 'HR',
    3 => 'Admin',
    default => 'Unknown'
};

// ✅ Button States
$timeInDisabled = '';
$timeOutDisabled = '';
$breakDisabled = '';

if ($dailyStatus === null) {
    $timeOutDisabled = 'disabled';
    $breakDisabled = 'disabled';
} elseif ($dailyStatus == 1) {
    $timeInDisabled = 'disabled';
} elseif ($dailyStatus == 0) {
    $timeInDisabled = 'disabled';
    $timeOutDisabled = 'disabled';
    $breakDisabled = 'disabled';
} elseif ($dailyStatus == 2) {
    $timeInDisabled = 'disabled';
    $breakDisabled = 'disabled';
}

// ✅ Calendar Setup
$month = date('n'); // 1–12
$year = date('Y');
$today = date('j');
$monthName = date('F');
$firstDayOfWeek = date('w', strtotime("$year-$month-01"));
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

$attendanceReportUrl = "$apiBaseUrl/report/" . urlencode($employeeID);
$attendanceResponse = @file_get_contents($attendanceReportUrl);
$totalDays = 0;

if ($attendanceResponse !== FALSE) {
    $decodedReport = json_decode($attendanceResponse, true);
    $totalDays = $decodedReport["totalDays"] ?? 0;
}

// ✅ Fetch Late Report (Late Count)
$lateReportUrl = "$apiBaseUrl/report/late/" . urlencode($employeeID);
$lateResponse = @file_get_contents($lateReportUrl);
$totalLateDays = 0;

if ($lateResponse !== FALSE) {
    $decodedLate = json_decode($lateResponse, true);
    $totalLateDays = $decodedLate["totalLateDays"] ?? 0;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriTime Payroll Attendance System</title>
  <link rel="stylesheet" href="../../styles/employee.css">
</head>

<script>
    document.addEventListener("DOMContentLoaded", () => {
      const toggleBtn = document.getElementById("toggle-btn");
      const sidebar = document.getElementById("sidebar");

      if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", () => {
          sidebar.classList.toggle("hidden");
        });
      } else {
        console.warn("Sidebar or toggle button not found.");
      }
    });
</script>

<body>
  <div class="container">
    <?php include('sidebar.php'); ?>

    <div class="main-content">
      <header class="header">
        <div class="logo">
      <img src="../assets/Agri.jpg" alt="Agri Logo" width="150">
          <h2>AgriTime Payroll Attendance System</h2>
        </div>
        <div class="user-profile">
                <img src="../assets/grit.jpg" alt="Agri Logo" width="120">
          <span><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
          <p><?php echo htmlspecialchars($roleName); ?></p>
          <h3><?php echo $currentTime; ?></h3>
        </div>
      </header>

       
      <section class="dashboard">
          <!--   Dashboard Summary Section -->
            <div class="summary-section">
              <!-- Total Days Worked -->
                  <div class="summary-card">
                    <div class="card-icon total">
                       <img src="../assets/cal.png" alt="cal" class="dashboard-icon">
                    </div>
                    <div class="card-info">
                      <h4>Total Days Worked</h4>
                      <p>This Month</p>
                      <h2>10 Days</h2>
                    </div>
                  </div>

                  <!-- Late Count -->
                  <div class="summary-card">
                    <div class="card-icon late">
                        <img src="../assets/clock.png" alt="clock" class="dashboard-icon">
                    </div>
                    <div class="card-info">
                      <h4>Late Arrivals</h4>
                      <p>This Month</p>
                      <h2>2</h2>
                    </div>
                  </div>

                  <!-- Payslip Board -->
                  <div class="summary-card payslip">
                    <div class="card-icon payslip-icon">
                       <img src="../assets/money.png" alt="money" class="dashboard-icon">
                    </div>
                    <div class="card-info">
                      <h4>Payslip Request</h4>
                      <p>Download your payslip</p>
                      <form method="POST">
                        <button type="submit" name="download" class="download-btn">Download</button>
                      </form>
                    </div>
                  </div>
                </div>

 
                  <div class="bottom-section">
                        <div class="calendar-board">
                          <div class="calendar">
                          <h2><?php echo "$monthName $year"; ?></h2>
                          <table>
                            <thead>
                              <tr>
                                <?php foreach ($dayNames as $day): ?>
                                  <th><?php echo $day; ?></th>
                                <?php endforeach; ?>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <?php
                                  // Empty cells before the first day
                                  for ($i = 0; $i < $firstDayOfWeek; $i++) {
                                    echo "<td></td>";
                                  }

                                  // Print days
                                  for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $isToday = ($day == $today) ? 'today' : '';
                                    echo "<td class='$isToday'>$day</td>";

                                    // Line break after Saturday
                                    if (($firstDayOfWeek + $day) % 7 == 0) {
                                      echo "</tr><tr>";
                                    }
                                  }

                                  // Fill the rest of the last row if needed
                                  $remaining = (7 - ($firstDayOfWeek + $daysInMonth) % 7) % 7;
                                  for ($i = 0; $i < $remaining; $i++) {
                                    echo "<td></td>";
                                  }
                                ?>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                    </div>
                    <div class="employee-infoTimedIn">
                       <div class="employee-board">             
                         <img src="../assets/grit.jpg" alt="Employee" class="profile-pic">
                          <h4><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h4>
                        
                          <p>Employee ID: <?php echo htmlspecialchars($user['employeeID']); ?></p>
                          <h3><?php echo $currentTime; ?></h3>
                        </div>
                        
                     <div class="buttons">  
                <form method="POST" style="display:inline;">
                  <button type="submit" name="time_in" class="timein-btn" <?php echo $timeInDisabled; ?>>Time-In</button>
                </form>

                <form method="POST" style="display:inline;">
                  <button type="submit" name="time_out" class="timeout-btn" <?php echo $timeOutDisabled; ?>>Time-Out</button>
                </form>

                <?php if ($dailyStatus == 1): ?>
                  <!-- ✅ Show Break In if Timed In -->
                  <form method="POST" style="display:inline;">
                    <button type="submit" name="break_in" class="break-btn">Break In</button>
                  </form>
                <?php elseif ($dailyStatus == 3): ?>
                  <!-- ✅ Show Break Out if On Break -->
                  <form method="POST" style="display:inline;">
                    <button type="submit" name="break_out" class="break-btn">Break Out</button>
                  </form>
                <?php else: ?>
                  <!-- ✅ Default Disabled Button -->
                  <button class="break-btn" disabled>Break</button>
                <?php endif; ?>
              </div>
                      <!-- ✅ Display Daily Status -->
                      <p style="margin-top:10px;">
                        <strong>Status Today:</strong>
                      <?php 
                      echo ($dailyStatus === null) ? "Not yet timed in" : 
                          (($dailyStatus == 1) ? "Timed In" : 
                          (($dailyStatus == 0) ? "Timed Out" : "On Break"));
                      ?>
                      </p>

                    <?php if (!empty($dailyTimestamp)): ?>
                      <p><strong>Last Recorded Time:</strong> <?php echo htmlspecialchars($dailyTimestamp); ?></p>
                    <?php else: ?>
                      <p><strong>Last Recorded Time:</strong> —</p>
                    <?php endif; ?>
                    </div>
                    
                      <div class="employee-middleboard">
                            <img src="../assets/farmer.png" alt="Farmer" class="farmer-illustration">   
                            <div class ="calendar-board">
                            <h2 class="workwork">Work Work!</h3>
                            </div>
                      </div>
                  </div>
                </section>
        </div>
  </div>
</body>
</html>
