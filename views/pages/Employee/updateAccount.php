<?php
session_start();
date_default_timezone_set("Asia/Manila");

if (!isset($_SESSION["user"])) {
  header("Location: /");
  exit;
}

$user = $_SESSION["user"];
$employeeID = $user["employeeID"];

// ✅ Only handle POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Collect form data
  $firstName = $_POST["firstName"] ?? '';
  $lastName = $_POST["lastName"] ?? '';
  $birthday = $_POST["birthday"] ?? '';
  $gender = $_POST["gender"] ?? '';
  $phone = $_POST["phone"] ?? '';
  $shiftTime = $_POST["shiftTime"] ?? '';
  $branch = $_POST["branch"] ?? '';

  // ✅ API endpoint
  $url = "http://localhost:8080/api/user/update/" . urlencode($employeeID);

  // ✅ Prepare data for JSON body
  $data = [
    "firstName" => $firstName,
    "lastName" => $lastName,
    "dob" => $birthday,
    "gender" => $gender,
    "contactNo" => $phone,
    "shiftTime" => $shiftTime,
    "branch" => $branch
  ];

  // ✅ Prepare HTTP POST request
  $options = [
    "http" => [
      "header"  => "Content-Type: application/json\r\n",
      "method"  => "POST",
      "content" => json_encode($data)
    ]
  ];

  $context = stream_context_create($options);
  $result = @file_get_contents($url, false, $context);

  if ($result === FALSE) {
    echo "<script>alert('Failed to connect to server. Try again later.'); window.location.href='myAccount.php';</script>";
    exit;
  }

  $response = json_decode($result, true);

  if (isset($response["message"]) && stripos($response["message"], "success") !== false) {
    // ✅ Update session data
    $_SESSION["user"]["firstName"] = $firstName;
    $_SESSION["user"]["lastName"] = $lastName;
    $_SESSION["user"]["dob"] = $birthday;
    $_SESSION["user"]["gender"] = $gender;
    $_SESSION["user"]["contactNo"] = $phone;
    $_SESSION["user"]["shiftTime"] = $shiftTime;
    $_SESSION["user"]["branch"] = $branch;

    echo "<script>alert('Profile updated successfully!'); window.location.href='myAccount.php';</script>";
  } else {
    $errorMsg = $response["message"] ?? "Error updating user.";
    echo "<script>alert('Update failed: " . addslashes($errorMsg) . "'); window.location.href='myAccount.php';</script>";
  }
}
?>
