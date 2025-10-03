import fs from 'fs';
import path from 'path';
import User from '../models/UserModel.js';

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


// Update user
export const updateUser = async (req, res) => {
  try {
    await User.update(req.params.id, req.body);
    res.status(200).json({ message: 'User updated successfully' });
  } catch (err) {
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


