import express from 'express';
import multer from 'multer';
import { createUser, getAllUsers, getUserById, updateUser, deleteUser, facialLogin } from '../controllers/UsersController.js';

const router = express.Router();

// Temporary upload folder before moving it
const upload = multer({ dest: 'temp_uploads/' });

// Routes
router.get('/', getAllUsers);
router.get('/:id', getUserById);
router.post('/', upload.array('profilePic', 5), createUser);
router.post("/facial-login", facialLogin); // 👈 for face login
router.put('/:id', updateUser);
router.delete('/:id', deleteUser);

export default router;
