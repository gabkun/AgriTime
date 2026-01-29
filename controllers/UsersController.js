import fs from 'fs';
import path from 'path';
import User from '../models/UserModel.js';
import AttendanceModel from "../models/AttendanceModel.js";

// Get all users
export const getAllUsers = async (req, res) => {
  try {
    const users = await User.getAll();
    res.status(200).json(users);
  } catch (err) {
    res.status(500).json({ message: 'Error fetching users', error: err.message });
  }
};

// Get a user by ID
export const getUserById = async (req, res) => {
  try {
    const user = await User.getById(req.params.id);
    if (!user) return res.status(404).json({ message: 'User not found' });
    res.status(200).json(user);
  } catch (err) {
    res.status(500).json({ message: 'Error fetching user', error: err.message });
  }
};

// Create a new user
export const createUser = async (req, res) => {
  try {
    const {
      firstName,
      lastName,
      gender,
      dob,
      email,
      password,
      contactNo,
      role,
      nationality,
      maritalStatus,
      emergencyContact,
      employeeID
    } = req.body;

    // Create folder name using only LASTNAME
    const folderName = lastName;
    const folderPath = path.join(process.cwd(), 'views', 'labels', folderName);

    // Create folder if it doesn't exist
    if (!fs.existsSync(folderPath)) {
      fs.mkdirSync(folderPath, { recursive: true });
    }

    // ⚠️ IMPORTANT: Move uploaded images to the folder
    if (req.files && req.files.length > 0) {
      req.files.forEach((file) => {
        const destinationPath = path.join(folderPath, file.originalname);
        fs.renameSync(file.path, destinationPath);
      });
    }

    // ✅ Save only the folder directory as the profilePic field
    const profilePic = `views/labels/${folderName}`;

    // Insert into database
    const userId = await User.create({
      firstName,
      lastName,
      gender,
      dob,
      email,
      password,
      contactNo,
      role,
      profilePic, // <— now stores the folder path only
      nationality,
      maritalStatus,
      emergencyContact,
      employeeID
    });

    res.status(201).json({ message: 'User created successfully', userId });
  } catch (err) {
    console.error('Error creating user:', err);
    res.status(500).json({ message: 'Error creating user', error: err.message });
  }
};

export const updateEmployee = async (req, res) => {
  try {
    const {
      id,
      firstName,
      lastName,
      dob,
      email,
      password,
      contactNo,
      nationality,
      maritalStatus,
      emergencyContact
    } = req.body;

    // ✅ Check if user exists
    const user = await User.getById(id);
    if (!user) return res.status(404).json({ message: 'User not found' });

    // ⚠️ Preserve restricted fields
    const restrictedFields = {
      employeeID: user.employeeID,
      role: user.role,
      status: user.status,
      created_at: user.created_at
    };

    // ✅ Manage folder name and path (only if lastName changes)
    const folderName = lastName && lastName !== user.lastName ? lastName : user.lastName;
    const oldFolderPath = path.join(process.cwd(), 'views', 'labels', user.lastName);
    const folderPath = path.join(process.cwd(), 'views', 'labels', folderName);

    if (lastName && lastName !== user.lastName) {
      if (fs.existsSync(oldFolderPath)) {
        fs.renameSync(oldFolderPath, folderPath);
      } else {
        fs.mkdirSync(folderPath, { recursive: true });
      }
    } else if (!fs.existsSync(folderPath)) {
      fs.mkdirSync(folderPath, { recursive: true });
    }

    // ✅ Move uploaded files if any
    if (req.files && req.files.length > 0) {
      req.files.forEach(file => {
        const destinationPath = path.join(folderPath, file.originalname);
        fs.renameSync(file.path, destinationPath);
      });
    }

    // ✅ Build updated data dynamically: only overwrite fields that exist in req.body
    const updatedData = {
      ...user, // keep existing data
      ...(firstName !== undefined && { firstName }),
      ...(lastName !== undefined && { lastName }),
      ...(dob !== undefined && { dob }),
      ...(email !== undefined && { email }),
      ...(password !== undefined && { password }),
      ...(contactNo !== undefined && { contactNo }),
      ...(nationality !== undefined && { nationality }),
      ...(maritalStatus !== undefined && { maritalStatus }),
      ...(emergencyContact !== undefined && { emergencyContact }),
      profilePic: `views/labels/${folderName}`,
      ...restrictedFields // make sure restricted fields stay intact
    };

    // ✅ Update user
    const result = await User.update(id, updatedData);

    if (result) {
      return res.status(200).json({ message: 'User updated successfully' });
    } else {
      return res.status(500).json({ message: 'Failed to update user' });
    }

  } catch (err) {
    console.error('Error updating user:', err);
    return res.status(500).json({ message: 'Error updating user', error: err.message });
  }
};


// Update user
export const updateUser = async (req, res) => {
  try {
    const allowedFields = ['firstName', 'lastName', 'dob', 'gender', 'contactNo'];
    const dataToUpdate = {};

    allowedFields.forEach(field => {
      if (req.body[field] !== undefined) {
        dataToUpdate[field] = req.body[field];
      }
    });

    await User.updateByEmployeeID(req.params.employeeID, dataToUpdate);
    res.status(200).json({ message: 'User updated successfully' });
  } catch (err) {
    console.error('Error updating user:', err);
    res.status(500).json({ message: 'Error updating user', error: err.message });
  }
};

// Delete user
export const deleteUser = async (req, res) => {
  try {
    await User.delete(req.params.id);
    res.status(200).json({ message: 'User deleted successfully' });
  } catch (err) {
    res.status(500).json({ message: 'Error deleting user', error: err.message });
  }
};

export const facialLogin = async (req, res) => {
  try {
    const label = req.body.label || req.body.faceImage;

    console.log("Facial Login Attempt Received");
    console.log("Raw Request Body:", req.body);

    if (!label) {
      console.warn("No face label detected in request body.");
      return res.status(400).json({ message: "No face label detected." });
    }

    const lastName = label;
    const folderPath = path.join(process.cwd(), "views", "labels", lastName);

    console.log("Detected Face Label:", lastName);
    console.log("Expected Folder Path:", folderPath);

    // Log the time of detection
    const loginTime = new Date().toLocaleString("en-PH", { timeZone: "Asia/Manila" });
    console.log("Login Attempt Time (PH):", loginTime);

    // Check if user folder exists
    if (!fs.existsSync(folderPath)) {
      console.warn(`No folder found for ${lastName} at ${folderPath}`);
      return res.status(404).json({ message: "No matching face data found." });
    }

    // Find user by last name in database
    const user = await User.findByLastName(lastName);

    console.log("Database Query: SELECT * FROM users WHERE lastName = ?", [lastName]);

    if (!user) {
      console.warn(`User not found in database for last name: ${lastName}`);
      return res.status(404).json({ message: "User not found." });
    }

    // Log user data
    console.log("User Found:", {
      id: user.id,
      firstName: user.firstName,
      lastName: user.lastName,
      role: user.role,
      email: user.email,
      employeeID: user.employeeID,
      profilePic: user.profilePic,
    });

    console.log("Facial login successful for:", `${user.firstName} ${user.lastName}`);
    console.log("Redirecting to dashboard...");

    // SUCCESS RESPONSE
    res.status(200).json({
      message: "Login successful",
      user,
      redirect: "/dashboard",
      loginTime,
    });
  } catch (err) {
    console.error("Error during facial login:", err);
    res.status(500).json({ message: "Server error during facial login", error: err.message });
  }
};

export const updateSalaryInfo = async (req, res) => {
  try {
    const { employeeID } = req.params;
    const { basicPay, allowances } = req.body;

    if (!employeeID || basicPay == null || allowances == null) {
      return res.status(400).json({ message: "Employee ID, basic pay, and allowances are required" });
    }

    const result = await User.updateSalaryByEmployeeID(employeeID, { basicPay, allowances });

    if (result.affectedRows === 0) {
      return res.status(404).json({ message: "Employee not found" });
    }

    res.status(200).json({ message: "Salary information updated successfully" });
  } catch (err) {
    res.status(500).json({ message: "Error updating salary information", error: err.message });
  }
};


export const getAllUserData = async (req, res) => {
  try {
    const users = await User.getAllUserData();
    res.status(200).json(users);
  } catch (err) {
    res.status(500).json({ message: "Error fetching all user data", error: err.message });
  }
};

export const getRecentUsers = async (req, res) => {
  try {
    const users = await User.getRecentUsers();
    if (users.length === 0) {
      return res.status(200).json({ message: "No users created this week." });
    }
    res.status(200).json(users);
  } catch (err) {
    res.status(500).json({ message: "Error fetching recent users", error: err.message });
  }
};

export const getTotalEmployeesThisMonth = async (req, res) => {
  try {
    const total = await User.getTotalEmployeesThisMonth();
    res.status(200).json({ totalEmployees: total.totalEmployees });
  } catch (err) {
    res.status(500).json({ message: "Error fetching total employees", error: err.message });
  }
};

// ✅ Total HR this month (role = 2)
export const getTotalHRThisMonth = async (req, res) => {
  try {
    const total = await User.getTotalHRThisMonth();
    res.status(200).json({ totalHR: total.totalHR });
  } catch (err) {
    res.status(500).json({ message: "Error fetching total HR", error: err.message });
  }
};

export const facialTimeOut = async (req, res) => {
  try {
    const label = req.body.label || req.body.faceImage;

    console.log("📸 Facial Time-Out Attempt Received");
    console.log("Raw Request Body:", req.body);

    if (!label) {
      return res.status(400).json({ message: "No face label detected." });
    }

    const lastName = label;
    const folderPath = path.join(process.cwd(), "views", "labels", lastName);

    console.log("Detected Face Label:", lastName);

    // ✅ Check if facial data exists
    if (!fs.existsSync(folderPath)) {
      return res.status(404).json({ message: "No matching face data found." });
    }

    // ✅ Find user by last name
    const user = await User.findByLastName(lastName);

    if (!user) {
      return res.status(404).json({ message: "User not found." });
    }

    console.log("👤 User Identified:", {
      employeeID: user.employeeID,
      name: `${user.firstName} ${user.lastName}`,
    });

    const employee_id = user.employeeID;

    // ================= TIME OUT LOGIC =================

    const employeeExists = await AttendanceModel.checkEmployeeExists(employee_id);
    if (employeeExists.length === 0) {
      return res.status(404).json({ message: "Employee not found." });
    }

    const statusExists = await AttendanceModel.checkDailyStatus(employee_id);

    if (statusExists.length === 0) {
      return res.status(400).json({
        message: "You must time in first before timing out.",
      });
    }

    if (statusExists[0].attendance_status == 0) {
      return res.status(400).json({
        message: "You have already timed out today.",
      });
    }

    // ✅ Record time out
    await AttendanceModel.recordTimeOut(employee_id);
    await AttendanceModel.updateDailyStatus(employee_id, 0);

    if (req.io) req.io.emit("attendanceUpdated");

    // ✅ Console success (NO DASHBOARD REDIRECT)
    console.log("✅ TIME OUT SUCCESSFULLY:", employee_id);

    return res.status(200).json({
      message: "Time out recorded successfully",
      employeeID: employee_id,
      name: `${user.firstName} ${user.lastName}`,
      timeOutTime: new Date().toLocaleString("en-PH", {
        timeZone: "Asia/Manila",
      }),
    });

  } catch (err) {
    console.error("❌ Error during facial time out:", err);
    return res.status(500).json({
      message: "Server error during facial time out",
      error: err.message,
    });
  }
}

export const facialTimeIn = async (req, res) => {
  try {
    const label = req.body.label || req.body.faceImage;

    console.log("📸 Facial Time-In Attempt Received");
    console.log("Raw Request Body:", req.body);

    // ❌ No face detected
    if (!label) {
      return res.status(400).json({ message: "No face label detected." });
    }

    const lastName = label;
    const folderPath = path.join(process.cwd(), "views", "labels", lastName);

    // ❌ No facial data folder
    if (!fs.existsSync(folderPath)) {
      return res.status(404).json({ message: "No matching face data found." });
    }

    // ❌ User not found
    const user = await User.findByLastName(lastName);
    if (!user) {
      return res.status(404).json({ message: "User not found." });
    }

    console.log("👤 User Identified:", {
      employeeID: user.employeeID,
      name: `${user.firstName} ${user.lastName}`,
    });

    const employeeID = user.employeeID;

    // ❌ Employee not registered
    const employeeExists = await AttendanceModel.checkEmployeeExists(employeeID);
    if (employeeExists.length === 0) {
      return res.status(404).json({ message: "Employee not found." });
    }

    // 🔒 HARD BLOCK: already has ANY status today
    const alreadyHasStatus = await AttendanceModel.hasAnyStatusToday(employeeID);

    if (alreadyHasStatus) {
      console.log("⛔ Facial time-in blocked (already exists today):", employeeID);
      return res.status(409).json({
        message: "You have already timed in today. Facial time-in is allowed only once."
      });
    }

    // ==================================================
    // ✅ FIRST TIME-IN OF THE DAY
    // ==================================================

    // 1️⃣ Record attendance time-in
    await AttendanceModel.recordTimeIn(employeeID);

    // 2️⃣ Insert daily status ONCE
    await AttendanceModel.insertDailyStatus(employeeID, 1);

    if (req.io) req.io.emit("attendanceUpdated");

    console.log("✅ Facial time-in successful:", employeeID);

    return res.status(200).json({
      message: "Time in recorded successfully",
      employeeID,
      name: `${user.firstName} ${user.lastName}`,
      timeIn: new Date().toLocaleString("en-PH", {
        timeZone: "Asia/Manila",
      }),
    });

  } catch (err) {
    console.error("❌ Error during facial time in:", err);
    return res.status(500).json({
      message: "Error during facial time in process",
      error: err.message,
    });
  }
};