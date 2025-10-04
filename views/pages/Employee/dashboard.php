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
    // ✅ Safely get attendance_status if available
    $dailyStatus = $decoded["attendance_status"] ?? null;
} else {
    $dailyStatus = null;
}

// ✅ Fetch Daily Timestamp (latest recorded time today)
$timestampUrl = "$apiBaseUrl/timestamp/$employeeID";
$timestampResponse = @file_get_contents($timestampUrl);
$dailyTimestamp = null;

if ($timestampResponse !== FALSE) {
    $decodedTime = json_decode($timestampResponse, true);
    $dailyTimestamp = $decodedTime["timestamp"];
}

// ✅ Handle Time-In and Time-Out actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeID = $user["employeeID"];

    // TIME IN
    if (isset($_POST["time_in"])) {
        $url = "$apiBaseUrl/timein";
        $data = ["employeeID" => $employeeID];

        $options = [
            "http" => [
                "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
                "method"  => "POST",
                "content" => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === FALSE) {
            echo "<script>alert('⚠️ Error connecting to Time-In API');</script>";
        } else {
            $response = json_decode($result, true);
            $message = $response["message"] ?? "Unknown response";
            echo "<script>alert('" . addslashes($message) . "'); window.location.reload();</script>";
        }
    }

    // TIME OUT
    if (isset($_POST["time_out"])) {
        $url = "$apiBaseUrl/timeout";
        $data = ["employeeID" => $employeeID];

        $options = [
            "http" => [
                "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
                "method"  => "POST",
                "content" => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === FALSE) {
            echo "<script>alert('⚠️ Error connecting to Time-Out API');</script>";
        } else {
            $response = json_decode($result, true);
            $message = $response["message"] ?? "Unknown response";
            echo "<script>alert('" . addslashes($message) . "'); window.location.reload();</script>";
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
          <img src="logo.png" alt="AgriTime Logo">
          <h2>AgriTime Payroll Attendance System</h2>
        </div>
        <div class="user-profile">
          <img src="user.jpg" alt="User">
          <span><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
          <p><?php echo htmlspecialchars($roleName); ?></p>
          <h3><?php echo $currentTime; ?></h3>
        </div>
      </header>

      <section class="dashboard">
        <div class="bottom-section">
          <div class="employee-info">
            <img src="user.jpg" alt="Employee" class="profile-pic">
            <h4><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h4>
            <p>Employee ID: <?php echo htmlspecialchars($user['employeeID']); ?></p>
            <h3><?php echo $currentTime; ?></h3>

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

            <!-- ✅ Display Timestamp -->
            <?php if ($dailyTimestamp): ?>
              <p><strong>Last Recorded Time:</strong> <?php echo htmlspecialchars($dailyTimestamp); ?></p>
            <?php else: ?>
              <p><strong>Last Recorded Time:</strong> —</p>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
