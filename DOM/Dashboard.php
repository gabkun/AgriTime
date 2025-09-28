<?php
session_start();

// Secure dashboard (redirect if not logged in)
if (!isset($_SESSION['user'])) {
    header("Location: Loginpage.php");
    exit();
}
$username = $_SESSION['user'];  // from login
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - AgriTime Payroll</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
    body { display: flex; min-height: 100vh; }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background: #f9fafb;
      border-right: 1px solid #e5e7eb;
      padding: 20px;
    }
    .sidebar h2 {
      font-size: 18px;
      margin-bottom: 30px;
      color: #16a34a;
    }
    .sidebar ul { list-style: none; }
    .sidebar li {
      margin: 15px 0;
    }
    .sidebar a {
      text-decoration: none;
      color: #111;
      font-size: 15px;
    }
    .sidebar a:hover { color: #16a34a; }

    /* Main */
    .main {
      flex: 1;
      background: #f3f4f6;
      padding: 20px;
    }

    .topbar {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      margin-bottom: 20px;
    }
    .user {
      display: flex;
      align-items: center;
    }
    .user img {
      width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;
    }
    .user span { font-weight: bold; }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .card h3 { font-size: 16px; margin-bottom: 10px; }
    .card p { font-size: 20px; font-weight: bold; color: #111; }

    /* Attendance section */
    .attendance {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .calendar, .status {
      flex: 1;
      min-width: 300px;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .time-buttons button {
      display: block;
      margin: 10px 0;
      padding: 12px;
      width: 100%;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }
    .btn-break { background: #f97316; color: white; }
    .btn-break:hover { background: #ea580c; }
    .btn-timein { background: #16a34a; color: white; }
    .btn-timein:hover { background: #15803d; }

    /* Responsive */
    @media (max-width: 768px) {
      body { flex-direction: column; }
      .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #e5e7eb; }
      .topbar { justify-content: center; }
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>AgriTime</h2>
    <ul>
      <li><a href="#">📊 Dashboard</a></li>
      <li><a href="#">📑 Attendance Report</a></li>
      <li><a href="#">👤 My Account</a></li>
      <li><a href="logout.php">🚪 Logout</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main">

    <!-- Top bar -->
    <div class="topbar">
      <div class="user">
        <img src="user.png" alt="User">
        <span><?php echo htmlspecialchars($username); ?></span>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="cards">
      <div class="card">
        <h3>Total Days Work (This Month)</h3>
        <p>10 days</p>
      </div>
      <div class="card">
        <h3>Late (This Month)</h3>
        <p>2</p>
      </div>
      <div class="card">
        <h3>Total Overtime</h3>
        <p>40 mins 55 secs</p>
      </div>
      <div class="card">
        <h3>Payslip</h3>
        <button class="btn-timein">Download</button>
      </div>
    </div>

    <!-- Attendance Section -->
    <div class="attendance">
      <div class="calendar">
        <h3>Attendance - February 2023</h3>
        <p>[Calendar here]</p>
      </div>
      <div class="status">
        <h3><?php echo htmlspecialchars($username); ?></h3>
        <p>Working Hours: 8 hrs 0 mins</p>
        <p>Remaining Hours: 8 hrs 0 mins</p>

        <div class="time-buttons">
          <button class="btn-break">Break</button>
          <button class="btn-timein">Time-In</button>
        </div>
      </div>
    </div>

  </div>

</body>
</html>
