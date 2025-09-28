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
    const folderName = lastName.trim().toUpperCase();
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
