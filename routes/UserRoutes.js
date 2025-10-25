import express from 'express';
import multer from 'multer';
import { createUser, getAllUsers, getUserById, updateUser, deleteUser, facialLogin, updateSalaryInfo, getAllUserData, updateEmployee, getRecentUsers, getTotalEmployeesThisMonth, getTotalHRThisMonth } from '../controllers/UsersController.js';

const router = express.Router();

// Temporary upload folder before moving it
const upload = multer({ dest: 'temp_uploads/' });

// Routes
router.get('/', getAllUsers);
router.get('/:id', getUserById);
router.post('/', upload.array('profilePic', 5), createUser);
router.post("/facial-login", facialLogin); // 👈 for face login
router.put('/employee/update/:id', updateEmployee);
router.post('/update-salary/:employeeID', updateSalaryInfo);
router.get('/recent/users', getRecentUsers);
router.delete('/:id', deleteUser);
router.get('/total/employees', getTotalEmployeesThisMonth);
router.post('/update/:employeeID', updateUser);
router.get('/total/hr', getTotalHRThisMonth);

export default router;
