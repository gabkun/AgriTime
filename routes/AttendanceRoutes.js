import express from 'express';
import { timein, timeout, getDailyStatus, getDailyTimestamp } from '../controllers/AttendanceControl.js';

const router = express.Router();

router.post('/timein', timein);
router.post('/timeout', timeout);
router.get('/status/:employeeID', getDailyStatus);
router.get("/timestamp/:employeeID", getDailyTimestamp);

export default router;
