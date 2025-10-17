import express from 'express';
import { timein, timeout, getDailyStatus, getDailyTimestamp, getAttendanceReport, generatePayslip  } from '../controllers/AttendanceControl.js';

const router = express.Router();

router.post('/timein', timein);
router.post('/timeout', timeout);
router.get('/status/:employeeID', getDailyStatus);
router.get("/timestamp/:employeeID", getDailyTimestamp);
router.get("/report/:employeeID", getAttendanceReport);
router.post("/generate", generatePayslip);

export default router;
