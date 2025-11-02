<?php
date_default_timezone_set("Asia/Manila");

// ✅ Handle salary update request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updateSalary"])) {
    $employeeID = $_POST["userID"]; // 🔥 FIXED
    $basicPay = $_POST["basicPay"];
    $allowances = $_POST["allowances"];

    echo "<script>console.log('🪪 Employee ID:', " . json_encode($employeeID) . ");</script>";

    if (!$employeeID || !$basicPay || !$allowances) {
        echo "<script>alert('❌ Missing required fields.');</script>";
    } else {
        $url = "http://localhost:8080/api/user/update-salary/" . urlencode($employeeID);

        $data = json_encode([
            "basicPay" => (float)$basicPay,
            "allowances" => (float)$allowances
        ]);

        echo "<script>console.log('📦 Salary Payload:', " . json_encode($data) . ");</script>";

        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "POST",
                "content" => $data,
                "ignore_errors" => true
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === FALSE) {
            echo "<script>alert('❌ Failed to connect to backend API.');</script>";
        } else {
            $decoded = json_decode($response, true);
            echo "<script>console.log('📨 Backend response:', " . json_encode($response) . ");</script>";

            if (isset($decoded["message"]) && str_contains(strtolower($decoded["message"]), "updated")) {
                echo "<script>alert('✅ Salary updated successfully!');</script>";
                exit;
            } else {
                $error = isset($decoded["message"]) ? $decoded["message"] : "Unknown error.";
                echo "<script>alert('❌ Failed to update salary: " . htmlspecialchars($error) . "');</script>";
            }
        }
    }
}

// ✅ Handle user update request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updateUser"])) {
    $userID = $_POST["userID"];
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $dob = $_POST["dob"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $contactNo = $_POST["contactNo"];
    $nationality = $_POST["nationality"];
    $maritalStatus = $_POST["maritalStatus"];
    $emergencyContact = $_POST["emergencyContact"];
    $basicPay = $_POST["basicPay"];
    $allowances = $_POST["allowances"];

    echo "<script>console.log('🪪 Updating User ID:', " . json_encode($userID) . ");</script>";

    if (!$userID || !$firstName || !$lastName || !$dob || !$email) {
        echo "<script>alert('❌ Missing required fields for update.');</script>";
    } else {
        $url = "http://localhost:8080/api/user/update/" . urlencode($userID);

        $data = json_encode([
            "firstName" => $firstName,
            "lastName" => $lastName,
            "dob" => $dob,
            "email" => $email,
            "password" => $password,
            "contactNo" => $contactNo,
            "nationality" => $nationality,
            "maritalStatus" => $maritalStatus,
            "emergencyContact" => $emergencyContact,
            "basicPay" => (float)$basicPay,
            "allowances" => (float)$allowances
        ]);

        echo "<script>console.log('📦 Update Payload:', " . json_encode($data) . ");</script>";

        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "POST",
                "content" => $data,
                "ignore_errors" => true
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === FALSE) {
            echo "<script>alert('Redirecting to Dashboard.');</script>";
        } else {
            $decoded = json_decode($response, true);
            echo "<script>console.log('📨 Backend response:', " . json_encode($response) . ");</script>";

            if (isset($decoded["message"]) && str_contains(strtolower($decoded["message"]), "updated")) {
                echo "<script>alert('✅ User updated successfully!');</script>";
                exit;
            } else {
                $error = isset($decoded["message"]) ? $decoded["message"] : "Redirecting to Dashboard";
                echo "<script>alert('Redirecting to Dashboard " . htmlspecialchars($error) . "');</script>";
            }
        }
    }
}
?>

<!-- ===== Employee Modal ===== -->
<div id="employeeModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTitle">View Employee</h3>
      <span class="close-btn" onclick="closeModal()">&times;</span>
    </div>

    <div class="modal-body">
      <div class="profile-section">
        <img id="empProfilePic" src="../assets/user.jpg" alt="Profile Picture" width="100%">
        <div class="upload-wrapper">
          <label for="empProfileUpload" class="upload-label">📸 Upload Profile</label>
          <input type="file" id="empProfileUpload" accept="image/*">
        </div>
      </div>

      <form method="POST" class="form-grid">
        <input type="hidden" name="userID" id="empID" readonly>

        <input type="text" name="firstName" id="empFName" placeholder="First Name" readonly>
        <input type="text" name="lastName" id="empLName" placeholder="Last Name" readonly>
        <input type="date" name="dob" id="dob" placeholder="Date of Birth" readonly>
        <input type="email" name="email" id="empEmail" placeholder="Email" readonly>
        <input type="password" name="password" id="empPassword" placeholder="Password" readonly>
        <input type="text" name="contactNo" id="empContact" placeholder="Contact No" readonly>
        <input type="text" name="nationality" id="empNationality" placeholder="Nationality" readonly>
        <input type="text" name="maritalStatus" id="empMarital" placeholder="Marital Status" readonly>
        <input type="text" name="emergencyContact" id="empEmergency" placeholder="Emergency Contact" readonly>
        <input type="number" name="basicPay" id="empBasic" placeholder="Basic Pay" readonly>
        <input type="number" name="allowances" id="empAllowance" placeholder="Allowances" readonly>

        <div class="modal-footer">
          <button type="button" class="edit-btn" onclick="enableUserEdit()">✏️ Edit User</button>
          <button type="submit" name="updateUser" class="save-btn" id="saveUserBtn" style="display:none; background:#2196F3;">🔄 Update User</button>
          <button type="button" class="edit-salary-btn" onclick="enableSalaryEdit()">💰 Edit Salary</button>
          <button type="submit" name="updateSalary" class="save-btn" id="saveSalaryBtn" style="display:none; background:#4CAF50;">Save Salary</button>
          <button type="submit" name="deleteEmployee" class="delete-btn" id="deleteBtn" style="background:#f44336;">Delete Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const modal = document.getElementById("employeeModal");

  async function fetchEmployees() {
    try {
      const res = await fetch("http://localhost:8080/api/user/");
      const data = await res.json();
      return data;
    } catch (err) {
      console.error("Error fetching employees:", err);
      return [];
    }
  }

  function openModal(mode, employeeID, firstName, lastName, role, dob, email, contactNo, nationality, maritalStatus, basicPay, allowances) {
    modal.style.display = "block";
    document.getElementById("empID").value = employeeID || "";
    document.getElementById("empFName").value = firstName || "";
    document.getElementById("empLName").value = lastName || "";
    document.getElementById("dob").value = dob || "";
    document.getElementById("empEmail").value = email || "";
    document.getElementById("empContact").value = contactNo || "";
    document.getElementById("empNationality").value = nationality || "";
    document.getElementById("empMarital").value = maritalStatus || "";
    document.getElementById("empBasic").value = basicPay || "";
    document.getElementById("empAllowance").value = allowances || "";
    document.getElementById("modalTitle").innerText =
      mode === "edit" ? "Edit Employee" : "View Employee";
  }

  function enableUserEdit() {
    const editableFields = [
      "empFName", "empLName", "dob", "empEmail", "empPassword",
      "empContact", "empNationality", "empMarital", "empEmergency", "empBasic", "empAllowance"
    ];

    editableFields.forEach(id => {
      const field = document.getElementById(id);
      field.removeAttribute("readonly");
      field.disabled = false;
      field.style.backgroundColor = "#fff";
      field.style.border = "1px solid #2196F3";
    });

    document.getElementById("saveUserBtn").style.display = "inline-block";
    alert("You can now edit user fields. Click 'Update User' to save changes.");
  }

  function enableSalaryEdit() {
    const basicInput = document.getElementById("empBasic");
    const allowanceInput = document.getElementById("empAllowance");
    const saveBtn = document.getElementById("saveSalaryBtn");

    basicInput.removeAttribute("readonly");
    allowanceInput.removeAttribute("readonly");
    basicInput.disabled = false;
    allowanceInput.disabled = false;
    basicInput.style.backgroundColor = "#fff";
    allowanceInput.style.backgroundColor = "#fff";
    basicInput.style.border = "1px solid #4CAF50";
    allowanceInput.style.border = "1px solid #4CAF50";

    saveBtn.style.display = "inline-block";
    basicInput.focus();

    alert("You can now edit salary fields. Click 'Save Salary' to update.");
  }

  function closeModal() {
    modal.style.display = "none";
  }

  function confirmDelete(employeeID) {
    if (confirm(`Are you sure you want to delete Employee ${employeeID}?`)) {
      document.getElementById("empID").value = employeeID;
      document.getElementById("deleteBtn").click();
    }
  }

  window.onclick = function(event) {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  };
</script>
