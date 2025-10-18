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

// ✅ API base URLs
$attendanceApi = "http://localhost:8080/api/attendance";
$userApi = "http://localhost:8080/api/user";

// ✅ Fetch Daily Status
$employeeID = $user["employeeID"];
$statusUrl = "$attendanceApi/status/$employeeID";

$statusResponse = @file_get_contents($statusUrl);
$dailyStatus = null;

if ($statusResponse !== FALSE) {
    $decoded = json_decode($statusResponse, true);
    $dailyStatus = $decoded["attendance_status"] ?? null;
} else {
    $dailyStatus = null;
}

// ✅ Fetch Timestamp
$timestampUrl = "$attendanceApi/status/$employeeID";
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
        $url = "$attendanceApi/timein";
        $result = callAttendanceAPI($url, ["employeeID" => $employeeID]);

        if ($result === FALSE) {
            echo "<script>alert('⚠️ Error connecting to Time-In API');</script>";
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
        $url = "$attendanceApi/timeout";
        $result = callAttendanceAPI($url, ["employeeID" => $employeeID]);

        if ($result === FALSE) {
            echo "<script>alert('⚠️ Error connecting to Time-Out API');</script>";
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

// ✅ Button states
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

// ✅ Calendar setup
$month = date('n');
$year = date('Y');
$today = date('j');
$monthName = date('F');
$firstDayOfWeek = date('w', strtotime("$year-$month-01"));
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

// ✅ Fetch recent users
$recentUsersUrl = "$userApi/recent/users";
$recentUsersResponse = @file_get_contents($recentUsersUrl);
$recentUsers = [];

if ($recentUsersResponse !== FALSE) {
    $decoded = json_decode($recentUsersResponse, true);
    if (is_array($decoded)) {
        $recentUsers = $decoded;
    }
}
$recentUsers = array_slice($recentUsers, 0, 5);

// ✅ Fetch total employees this month
$totalEmployeesUrl = "$userApi/total/employees";
$totalEmployeesResponse = @file_get_contents($totalEmployeesUrl);
$totalEmployees = 0;

if ($totalEmployeesResponse !== FALSE) {
    $decoded = json_decode($totalEmployeesResponse, true);
    $totalEmployees = $decoded["totalEmployees"] ?? 0;
}

// ✅ Fetch total HR this month
$totalHRUrl = "$userApi/total/hr";
$totalHRResponse = @file_get_contents($totalHRUrl);
$totalHR = 0;

if ($totalHRResponse !== FALSE) {
    $decoded = json_decode($totalHRResponse, true);
    $totalHR = $decoded["totalHR"] ?? 0;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgriTime Payroll Attendance System</title>
  <link rel="stylesheet" href="../../styles/admin.css">
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
          <h2>AgriTime Admin Access</h2>
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
        <!-- Total Employees -->
        <div class="summary-card">
          <div class="card-icon total">
            <i>📅</i>
          </div>
          <div class="card-info">
            <h4>Total Employees as of</h4>
            <p>This Month</p>
            <h2><?php echo htmlspecialchars($totalEmployees); ?></h2>
          </div>
        </div>

        <!-- Total HR -->
        <div class="summary-card">
          <div class="card-icon late">
            <i>⏰</i>
          </div>
          <div class="card-info">
            <h4>Total HR as of</h4>
            <p>This Month</p>
            <h2><?php echo htmlspecialchars($totalHR); ?></h2>
          </div>
        </div>

        <!-- Payslip Board -->
        <div class="summary-card payslip">
          <div class="card-icon payslip-icon">
            <i>💵</i>
          </div>
          <div class="card-info">
            <h4>Total Payslip Generated</h4>
            <h2>20</h2>
          </div>
        </div>
      </div>


 
                  <div class="bottom-section">
                        <div class="calendar-board">
                    <div class="employees">
                            <h2>Recent Employees</h2>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentUsers)): ?>
                                        <?php foreach ($recentUsers as $emp): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($emp['employeeID'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($emp['firstName'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($emp['lastName'] ?? '') ?></td>
                                                <td>
                                                    <?php 
                                                        if (!empty($emp['created_at'])) {
                                                            echo date("M d, Y", strtotime($emp['created_at']));
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="no-data">No recent employees this week.</td>
                                        </tr>
                                    <?php endif; ?>
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
                        


                     </div>
                        <div class="secretary-board">
                        <img src="../assets/admin.png" alt="Secretary" class="secretary-illustration">
                        <div class="secretary-text">
                          <h2>Money-Money!</h2>
                          <p>Time is Money</p>
                          <button class="secretary-btn">View Schedule</button>
                        </div>
                      </div>
                  </div>
                </section>
        </div>
  </div>
</body>
</html>
