import express from 'express';
import multer from 'multer';
import { createUser, getAllUsers, getUserById, updateUser, deleteUser, facialLogin, updateSalaryInfo, getAllUserData, getRecentUsers, getTotalEmployeesThisMonth, getTotalHRThisMonth } from '../controllers/UsersController.js';

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
router.get('/recent/users', getRecentUsers);
router.delete('/:id', deleteUser);
router.get('/total/employees', getTotalEmployeesThisMonth);
router.get('/total/hr', getTotalHRThisMonth);

export default router;
