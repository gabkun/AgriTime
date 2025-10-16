<?php
date_default_timezone_set("Asia/Manila");

// ✅ Handle salary update request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updateSalary"])) {
    $employeeID = $_POST["employeeID"];
    $basicPay = $_POST["basicPay"];
    $allowances = $_POST["allowances"];

    // ✅ Log employee ID to browser console
    echo "<script>console.log('🪪 Employee ID:', " . json_encode($employeeID) . ");</script>";

    if (!$employeeID || !$basicPay || !$allowances) {
        echo "<script>alert('❌ Missing required fields.');</script>";
    } else {
        // ✅ Express backend API endpoint (no longer includes employeeID in URL)
        $url = "http://localhost:8080/api/user/update-salary/user";

        // ✅ Prepare JSON payload (now includes employeeID in body)
        $data = json_encode([
            "employeeID" => $employeeID,
            "basicPay" => (float)$basicPay,
            "allowances" => (float)$allowances
        ]);

        // ✅ Log payload to browser console
        echo "<script>console.log('📦 Payload to backend:', " . json_encode($data) . ");</script>";

        // ✅ Stream context for PUT request
        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "PUT",
                "content" => $data,
                "ignore_errors" => true
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        // ✅ Handle backend response
        if ($response === FALSE) {
            echo "<script>alert('❌ Failed to connect to backend API.');</script>";
        } else {
            // Log backend raw response
            echo "<script>console.log('📨 Backend response:', " . json_encode($response) . ");</script>";

            $decoded = json_decode($response, true);

           if (isset($decoded["message"]) && str_contains(strtolower($decoded["message"]), "updated")) {
                echo "<script>
                    alert('✅ Salary updated successfully!');
                    // ✅ Stop page from reloading
                    if (window.event) {
                        window.event.preventDefault();
                    }
                </script>";
                exit; // 🛑 Stop PHP execution to prevent repeated requests
            } else {
                $error = isset($decoded["message"]) ? $decoded["message"] : "Unknown error.";
                echo "<script>alert('❌ Failed to update salary: " . htmlspecialchars($error) . "');</script>";
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
        <input type="text" name="employeeID" id="empID" placeholder="Employee ID" readonly>
        <input type="text" id="empFName" placeholder="First Name" readonly>
        <input type="text" id="empLName" placeholder="Last Name" readonly>
        <input type="date" id="dob" placeholder="Date of Birth" readonly>
        <input type="email" id="empEmail" placeholder="Email" readonly>
        <input type="password" id="empPassword" placeholder="Password" readonly>
        <input type="text" id="empContact" placeholder="Contact No" readonly>
        <select id="empRole" disabled>
          <option value="1">Employee</option>
          <option value="2">HR</option>
        </select>
        <input type="text" id="empNationality" placeholder="Nationality" readonly>
        <input type="text" id="empMarital" placeholder="Marital Status" readonly>
        <input type="text" id="empEmergency" placeholder="Emergency Contact" readonly>
        <input type="number" name="basicPay" id="empBasic" placeholder="Basic Pay" readonly>
        <input type="number" name="allowances" id="empAllowance" placeholder="Allowances" readonly>

        <div class="modal-footer">
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

  // ===== Fetch Employees =====
  async function fetchEmployees() {
    try {
      const res = await fetch("http://localhost:8080/api/user/get/all");
      const data = await res.json();
      return data;
    } catch (err) {
      console.error("Error fetching employees:", err);
      return [];
    }
  }

  // ===== Open Modal =====
function openModal(mode, employeeID, firstName, lastName, role, dob, email, contactNo, nationality, maritalStatus, basicPay, allowances) {
  modal.style.display = "block";

  // Fill values directly from passed arguments
  document.getElementById("empID").value = employeeID || "";
  document.getElementById("empFName").value = firstName || "";
  document.getElementById("empLName").value = lastName || "";
  document.getElementById("dob").value = dob || "";
  document.getElementById("empEmail").value = email || "";
  document.getElementById("empContact").value = contactNo || "";
  document.getElementById("empRole").value = role || 1;
  document.getElementById("empNationality").value = nationality || "";
  document.getElementById("empMarital").value = maritalStatus || "";
  document.getElementById("empBasic").value = basicPay || "";
  document.getElementById("empAllowance").value = allowances || "";

  document.getElementById("modalTitle").innerText =
    mode === "edit" ? "Edit Employee" : "View Employee";
}

  // ===== Enable Salary Edit =====
function enableSalaryEdit() {
    const basicInput = document.getElementById("empBasic");
    const allowanceInput = document.getElementById("empAllowance");
    const saveBtn = document.getElementById("saveSalaryBtn");

    // Remove readonly and disabled completely
    basicInput.removeAttribute("readonly");
    allowanceInput.removeAttribute("readonly");
    basicInput.disabled = false;
    allowanceInput.disabled = false;

    // Apply active input styling for clarity
    basicInput.style.backgroundColor = "#fff";
    allowanceInput.style.backgroundColor = "#fff";
    basicInput.style.border = "1px solid #4CAF50";
    allowanceInput.style.border = "1px solid #4CAF50";

    // Show save button
    saveBtn.style.display = "inline-block";

    // Focus the first input
    basicInput.focus();

    alert("You can now edit the salary fields. Click 'Save Salary' to update.");
  }
  // ===== Close Modal =====
  function closeModal() {
    modal.style.display = "none";
  }

  // ===== Delete Employee (handled via PHP form) =====
  function confirmDelete(employeeID) {
    if (confirm(`Are you sure you want to delete Employee ${employeeID}?`)) {
      document.getElementById("empID").value = employeeID;
      document.getElementById("deleteBtn").click();
    }
  }

  // ===== Close modal when clicking outside =====
  window.onclick = function (event) {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  };
</script>

