<?php
// register.php
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname     = $_POST['fname'] ?? '';
    $lname     = $_POST['lname'] ?? '';
    $dob       = $_POST['dob'] ?? '';
    $gender    = $_POST['gender'] ?? '';
    $email     = $_POST['email'] ?? '';
    $password  = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';
    $contact   = $_POST['contact'] ?? '';
    $shift     = $_POST['shift'] ?? '';
    $role      = $_POST['role'] ?? '';
    $photo     = $_FILES['photo']['name'] ?? '';

    if ($password !== $cpassword) {
        $message = "❌ Passwords do not match!";
    } else {
        $message = "✅ Registration successful for $fname $lname!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - AgriTime Payroll Attendance System</title>
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Arial, sans-serif;
      display: flex;
      min-height: 100vh;
    }
    .left, .right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .left {
      background: #f0f9ff;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .right {
      background: linear-gradient(135deg, #a3e635, #34d399);
      color: white;
      text-align: center;
    }
    form {
      width: 100%;
      max-width: 400px;
      background: white;
      padding: 25px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    form h2 {
      margin-top: 0;
      text-align: center;
      color: #222;
    }
    form p {
      font-size: 14px;
      text-align: center;
      color: #555;
      margin-bottom: 20px;
    }
    label {
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 5px;
      display: block;
      color: #333;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }
    input:focus, select:focus {
      outline: none;
      border-color: #34d399;
      box-shadow: 0 0 4px rgba(52, 211, 153, 0.5);
    }
    button {
      background: #22c55e;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 5px;
      color: white;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background: #16a34a;
    }
    .message {
      margin-top: 10px;
      font-weight: bold;
      text-align: center;
      color: green;
    }
    .footer-text {
      margin-top: 12px;
      text-align: center;
      font-size: 14px;
    }
    .footer-text a {
      color: #2563eb;
      text-decoration: none;
    }
    .footer-text a:hover {
      text-decoration: underline;
    }
    @media (max-width: 768px) {
      body {
        flex-direction: column;
      }
      .left, .right {
        flex: none;
        width: 100%;
        min-height: 50vh;
      }
      form {
        max-width: 90%;
      }
    }
  </style>
</head>
<body>
  <div class="left">
    <form method="POST" enctype="multipart/form-data">
      <h2>Register</h2>
      <p>Welcome to the Attendance Registration page.<br>Please fill in the form to create your account.</p>

      <label>First Name</label>
      <input type="text" name="fname" required placeholder="cjsjdj">

      <label>Last Name</label>
      <input type="text" name="lname" required>

      <label>Date of Birth</label>
      <input type="date" name="dob" required>

      <label>Gender</label>
      <select name="gender" required>
        <option value="">Select Gender</option>
        <option>Male</option>
        <option>Female</option>
      </select>

      <label>Email Address</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Confirm Password</label>
      <input type="password" name="cpassword" required>

      <label>Contact No.</label>
      <input type="text" name="contact" required>

      <label>Shift Time</label>
      <select name="shift" required>
        <option value="">Select Shift</option>
        <option>Day</option>
        <option>Night</option>
      </select>

      <label>Role</label>
      <select name="role" required>
        <option value="">Select Role</option>
        <option>Employee</option>
        <option>Manager</option>
        <option>Admin</option>
      </select>

      <label>Profile Picture</label>
      <input type="file" name="photo" accept="image/*">

      <button type="submit">Register</button>

      <?php if (!empty($message)): ?>
        <p class="message"><?= $message ?></p>
      <?php endif; ?>

      <div class="footer-text">
        Already have an account? <a href="login.php">Login here</a>
      </div>
    </form>
  </div>
  
  <div class="right">
    <img src="Agri.jpg" alt="Agri Logo" width="120">
    <h2>AgriTime Payroll<br>Attendance System</h2>
  </div>
</body>
</html>

