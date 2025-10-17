import express from 'express';
import { timein, timeout, getDailyStatus, getDailyTimestamp, getAttendanceReport, generatePayslip, getAllDailyStatus, downloadPayslip, getAllpayslip   } from '../controllers/AttendanceControl.js';

const router = express.Router();

router.post('/timein', timein);
router.post('/timeout', timeout);
router.get('/status/:employeeID', getDailyStatus);
router.get("/timestamp/:employeeID", getDailyTimestamp);
router.get("/report/:employeeID", getAttendanceReport);
router.get('/get/allstatus', getAllDailyStatus);
router.post("/generate", generatePayslip);
router.get("/download/:employeeID", downloadPayslip);
router.get('/get/all/payslip', getAllpayslip);

export default router;
