import AttendanceModel from '../models/attendanceModel.js';

export const timein = async (req, res) => {
  const { employeeID } = req.body;
  if (!employeeID) 
    return res.status(400).json({ message: 'Employee ID is required' });

  const employee_id = employeeID;

  try {
    console.log("📥 Incoming employeeID:", employee_id);

    // ✅ Check if employee exists
    const employeeExists = await AttendanceModel.checkEmployeeExists(employee_id);
    if (employeeExists.length === 0) {
      return res.status(404).json({ message: 'Employee not found' });
    }

    // ✅ Check if already timed in today
    const statusExists = await AttendanceModel.checkDailyStatus(employee_id);
    if (statusExists.length > 0 && statusExists[0].attendance_status == 1) {
      console.log("⚠️ Employee already timed in today:", employee_id);
      return res.status(400).json({ message: 'You have already timed in today.' });
    }

    // ✅ Record new time-in
    await AttendanceModel.recordTimeIn(employee_id);
    console.log("✅ Time in successfully recorded for employee:", employee_id);

    if (statusExists.length === 0) {
      await AttendanceModel.insertDailyStatus(employee_id, 1);
    } else {
      await AttendanceModel.updateDailyStatus(employee_id, 1);
    }

    if (req.io) req.io.emit('attendanceUpdated');

    return res.status(200).json({ message: 'Time in recorded successfully' });

  } catch (err) {
    console.error("❌ Error during time in process:", err);
    return res.status(500).json({ message: 'Error during time in process' });
  }
};

export const timeout = async (req, res) => {
  const { employeeID } = req.body;
  if (!employeeID)
    return res.status(400).json({ message: 'Employee ID is required' });

  const employee_id = employeeID;

  try {
    console.log("📥 Incoming employeeID:", employee_id);

    const employeeExists = await AttendanceModel.checkEmployeeExists(employee_id);
    if (employeeExists.length === 0) {
      return res.status(404).json({ message: 'Employee not found' });
    }

    // ✅ Check if already timed out today
    const statusExists = await AttendanceModel.checkDailyStatus(employee_id);

    if (statusExists.length === 0) {
      return res.status(400).json({ message: 'You must time in first before timing out.' });
    }

    if (statusExists[0].attendance_status == 0) {
      console.log("⚠️ Employee already timed out today:", employee_id);
      return res.status(400).json({ message: 'You have already timed out today.' });
    }

    // ✅ Record time-out
    await AttendanceModel.recordTimeOut(employee_id);
    console.log("✅ Time out successfully recorded for employee:", employee_id);

    await AttendanceModel.updateDailyStatus(employee_id, 0);

    if (req.io) req.io.emit('attendanceUpdated');

    return res.status(200).json({ message: 'Time out recorded successfully' });

  } catch (err) {
    console.error("❌ Error during time out process:", err);
    return res.status(500).json({ message: 'Error during time out process' });
  }
};


// controllers/AttendanceControl.js
export const getDailyStatus = async (req, res) => {
  const { employeeID } = req.params;

  try {
    // ✅ Query today's attendance only
    const status = await AttendanceModel.checkDailyStatus(employeeID);

    if (status.length === 0) {
      return res.status(404).json({ 
        message: 'No attendance record for today.',
        attendance_status: null,
        time: null
      });
    }

    // ✅ Get only records created today (Philippine time)
    const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });

    const todayRecords = status.filter(record => {
      const recordDate = new Date(record.timestamp).toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
      return recordDate === today;
    });

    if (todayRecords.length === 0) {
      // No records for today
      return res.status(404).json({ 
        message: 'No attendance record for today.',
        attendance_status: null,
        time: null
      });
    }

    // ✅ Get the most recent record for today
    const latestStatus = todayRecords[todayRecords.length - 1];
    const { attendance_status, timestamp } = latestStatus;

    // ✅ Format time (12-hour format)
    const timeOnly = new Date(timestamp).toLocaleTimeString('en-PH', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true,
      timeZone: 'Asia/Manila'
    });

    // ✅ Send back response
    return res.status(200).json({
      employeeID,
      attendance_status,
      time: timeOnly
    });

  } catch (err) {
    console.error('❌ Error fetching daily status:', err);
    return res.status(500).json({
      message: 'Error fetching daily status',
      error: err.message,
    });
  }
};



export const getDailyTimestamp = async (req, res) => {
  const { employeeID } = req.params;
  try {
    const result = await AttendanceModel.getLatestTimestamp(employeeID);
    if (result.length === 0) {
      return res.json({ timestamp: null });
    } else {
      // ✅ Return only the time portion (HH:MM:SS)
      const time = new Date(result[0].timestamp).toLocaleTimeString("en-PH", {
        hour12: true,
        hour: "numeric",
        minute: "2-digit",
        second: "2-digit"
      });
      return res.json({ timestamp: time });
    }
  } catch (err) {
    res.status(500).json({ message: "Error fetching timestamp", error: err.message });
  }
};

export const getAttendanceReport = async (req, res) => {
  const { employeeID } = req.params;

  if (!employeeID) {
    return res.status(400).json({ message: "Employee ID is required" });
  }

  try {
    const report = await AttendanceModel.getAttendanceReport(employeeID);

    if (report.length === 0) {
      return res.status(404).json({ message: "No attendance records found" });
    }

    // ✅ Format response
    const formattedReport = report.map((r) => ({
      date: new Date(r.date).toLocaleDateString("en-PH", {
        timeZone: "Asia/Manila",
      }),
      time_in: new Date(r.time_in).toLocaleTimeString("en-PH", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
        timeZone: "Asia/Manila",
      }),
      time_out: r.time_out
        ? new Date(r.time_out).toLocaleTimeString("en-PH", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: true,
            timeZone: "Asia/Manila",
          })
        : "—",
      total_hours: r.total_time ? r.total_time : "—",
    }));

    res.status(200).json({
      employeeID,
      totalDays: formattedReport.length,
      records: formattedReport,
    });
  } catch (err) {
    console.error("❌ Error generating attendance report:", err);
    res.status(500).json({
      message: "Error generating attendance report",
      error: err.message,
    });
  }
};

export const generatePayslip = async (req, res) => {
  const { employeeID, startDate, endDate, sssDeduction, pagibigDeduction, philhealthDeduction } = req.body;

  if (!employeeID || !startDate || !endDate) {
    return res.status(400).json({ message: "Missing required fields" });
  }

  try {
    // ✅ 1. Fetch all attendance records
    const records = await AttendanceModel.getAttendanceReport(employeeID);

    if (!records || records.length === 0) {
      return res.status(404).json({ message: "No attendance records found for this employee." });
    }

    // ✅ 2. Filter records by date range (exclude weekends)
    const filteredRecords = records.filter((r) => {
      const date = new Date(r.date);
      const day = date.getDay(); // 0 = Sunday, 6 = Saturday
      return date >= new Date(startDate) && date <= new Date(endDate) && day !== 0 && day !== 6;
    });

    if (filteredRecords.length === 0) {
      return res.status(404).json({ message: "No valid working days found in the selected date range." });
    }

    // ✅ 3. Calculate totalHours and overtimeHours
    let totalHours = 0;
    let overtimeHours = 0;

    filteredRecords.forEach((r) => {
      if (r.total_time) {
        const [hours, minutes, seconds] = r.total_time.split(":").map(Number);
        const workedHours = hours + minutes / 60 + seconds / 3600;
        totalHours += workedHours;

        if (workedHours > 8) {
          overtimeHours += workedHours - 8; // overtime = hours beyond 8
        }
      }
    });

    // ✅ 4. Generate payslip using model
    const payslip = await AttendanceModel.generatePayslip(
      employeeID,
      startDate,
      endDate,
      totalHours.toFixed(2),
      overtimeHours.toFixed(2),
      sssDeduction || 0,
      pagibigDeduction || 0,
      philhealthDeduction || 0
    );

    // ✅ 5. Send response
    res.status(200).json(payslip);

  } catch (err) {
    console.error("❌ Error generating payslip:", err);
    res.status(500).json({
      message: "Error generating payslip",
      error: err.message,
    });
  }
};