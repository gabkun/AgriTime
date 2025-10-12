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

// ✅ API base URL
$apiBaseUrl = "http://localhost:8080/api/attendance";

// ✅ Fetch Daily Status
$employeeID = $user["employeeID"];
$statusUrl = "$apiBaseUrl/status/$employeeID";

$statusResponse = @file_get_contents($statusUrl);
$dailyStatus = null;

if ($statusResponse !== FALSE) {
    $decoded = json_decode($statusResponse, true);
    $dailyStatus = $decoded["attendance_status"] ?? null;
} else {
    $dailyStatus = null;
}

$timestampUrl = "$apiBaseUrl/status/$employeeID";
$timestampResponse = @file_get_contents($timestampUrl);
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
            echo "<script>alert('⚠️ Error connecting to Time-In API');</script>";
        } else {
            $response = json_decode($result, true);
            $message = $response["message"] ?? "Unknown response";

            // ✅ Reload after success
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
            echo "<script>alert('⚠️ Error connecting to Time-Out API');</script>";
        } else {
            $response = json_decode($result, true);
            $message = $response["message"] ?? "Unknown response";

            // ✅ Reload only if success
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

// ✅ Button states
$timeInDisabled = '';
$timeOutDisabled = '';
$breakDisabled = '';

if ($dailyStatus === null) {
    // No record today → enable time in only
    $timeOutDisabled = 'disabled';
    $breakDisabled = 'disabled';
} elseif ($dailyStatus == 1) {
    // Already timed in → disable time in, enable time out and break
    $timeInDisabled = 'disabled';
} elseif ($dailyStatus == 0) {
    // Already timed out → disable everything
    $timeInDisabled = 'disabled';
    $timeOutDisabled = 'disabled';
    $breakDisabled = 'disabled';
} elseif ($dailyStatus == 2) {
    // On break → disable time in, disable break, enable time out
    $timeInDisabled = 'disabled';
    $breakDisabled = 'disabled';
}


  date_default_timezone_set('Asia/Manila');

  $month = date('n'); // 1-12
  $year = date('Y');
  $today = date('j');
  $monthName = date('F');

  // First day of the month (0 = Sunday)
  $firstDayOfWeek = date('w', strtotime("$year-$month-01"));
  $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

  $dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriTime Payroll Attendance System</title>
  <link rel="stylesheet" href="../../styles/employee.css">
</head>

<body>
  <div class="container">
    <?php include('sidebar.php'); ?>

    <div class="main-content">
      <header class="header">
        <div class="logo">
      <img src="../assets/Agri.jpg" alt="Agri Logo" width="150">
          <h2>AgriTime Payroll Attendance sssSystem</h2>
        </div>
        <div class="user-profile">
                <img src="../assets/user.png" alt="Agri Logo" width="120">
          <span><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
          <p><?php echo htmlspecialchars($roleName); ?></p>
          <h3><?php echo $currentTime; ?></h3>
        </div>
      </header>

       
      <section class="dashboard">
        <!-- middle section -->
          <div class= "middle-section">
                <div class="employee-middleboard">
                      <h4 class="boardText">Total Days Work</h3>
                      <h4 class="boardText">This Month</h2>
                      <h1>10 days</h1>
                </div>
                <div class="employee-middleboard">
                      <h4 class="boardText">Late</h3>
                      <h4 class="boardText">This Month</h2>
                      <h1>2</h1>
                </div>
                  <div class="employee-middleboard">
                      <h4 class="boardText">Total Overtime Hours/Febraury</h3>
                      <h4 class="boardText">00 hours</h2>
                      <h4 class="boardText">40 minutes</h2>
                      <h4 class="boardText">55 seconds</h2>
                </div>
                <div class="employee-middleboard">
                      <h4>Request PaySlip</h1>
                        <div class="buttons">
                          <img src="assets/payslip.png" alt="paySlip" class="profile-pic">
                          <form method="POST" style="display:inline;">
                              <button type="submit" name="download" class="timein-btn" >Download</button>
                          </form>
                        </div>
                </div>
          </div>
          <!-- middle section -->
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
                        
                          <p>Employeefff ID: <?php echo htmlspecialchars($user['employeeID']); ?></p>
                          <h3><?php echo $currentTime; ?></h3>
                        </div>
                        
                      <div class="buttons">
                        <form method="POST" style="display:inline;">
                          <button type="submit" name="time_in" class="timein-btn" <?php echo $timeInDisabled; ?>>Time-In</button>
                        </form>

                        <form method="POST" style="display:inline;">
                          <button type="submit" name="time_out" class="timeout-btn" <?php echo $timeOutDisabled; ?>>Time-Out</button>
                        </form>

                        <button class="break-btn" <?php echo $breakDisabled; ?>>Break</button>
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
                          <h4 class="boardText">insert Image here</h3>     
                      </div>
                  </div>
                </section>
        </div>
  </div>
</body>
</html>
