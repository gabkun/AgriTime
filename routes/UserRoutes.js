import express from 'express';
import multer from 'multer';
import { createUser, getAllUsers, getUserById, updateUser, deleteUser, facialLogin, updateSalaryInfo, getAllUserData } from '../controllers/UsersController.js';

const router = express.Router();

// Temporary upload folder before moving it
const upload = multer({ dest: 'temp_uploads/' });

// Routes
router.get('/', getAllUsers);
router.get('/:id', getUserById);
router.post('/', upload.array('profilePic', 5), createUser);
router.post("/facial-login", facialLogin); // 👈 for face login
router.put('/:id', updateUser);
router.put('/update-salary/user', updateSalaryInfo);
router.get('/get/all', getAllUserData);
router.delete('/:id', deleteUser);

export default router;
