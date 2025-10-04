import AttendanceModel from '../models/attendanceModel.js';

export const timein = async (req, res) => {
  const { employeeID } = req.body;
  if (!employeeID) 
    return res.status(400).json({ message: 'Employee ID is required' });

  const employee_id = employeeID;

  try {
    console.log("📥 Incoming employeeID:", employee_id);

    // ✅ Check if employee exists in users table
    const employeeExists = await AttendanceModel.checkEmployeeExists(employee_id);
    if (employeeExists.length === 0) {
      console.warn("⚠️ Employee does not exist:", employee_id);
      return res.status(404).json({ message: 'Employee not found' });
    }

    // ✅ Record time-in
    await AttendanceModel.recordTimeIn(employee_id);
    console.log("✅ Time in successfully recorded for employee:", employee_id);

    // ✅ Check daily status
    const statusExists = await AttendanceModel.checkDailyStatus(employee_id);
    console.log("🧩 Daily status check result:", statusExists);

    if (statusExists.length === 0) {
      // Insert new status if not exists
      await AttendanceModel.insertDailyStatus(employee_id, 1);
      console.log("🆕 Daily status created for employee:", employee_id);
    } else {
      // Update existing status
      await AttendanceModel.updateDailyStatus(employee_id, 1);
      console.log("♻️ Daily status updated for employee:", employee_id);
    }

    // ✅ Emit socket update
    if (req.io) req.io.emit('attendanceUpdated');

    return res.status(200).json({ 
      message: 'Time in recorded successfully' 
    });

  } catch (err) {
    console.error("❌ Error during time in process:", err);
    return res.status(500).json({ 
      message: 'Error during time in process', 
      error: err.message || err.toString(), 
      stack: err.stack 
    });
  }
};


export const timeout = async (req, res) => {
  const { employeeID } = req.body;
  if (!employeeID) 
    return res.status(400).json({ message: 'Employee ID is required' });

  const employee_id = employeeID;

  try {
    console.log("📥 Incoming employeeID:", employee_id);

    // ✅ Check if employee exists
    const employeeExists = await AttendanceModel.checkEmployeeExists(employee_id);
    if (employeeExists.length === 0) {
      console.warn("⚠️ Employee does not exist:", employee_id);
      return res.status(404).json({ message: 'Employee not found' });
    }

    // ✅ Record time-out
    await AttendanceModel.recordTimeOut(employee_id);
    console.log("✅ Time out successfully recorded for employee:", employee_id);

    // ✅ Check daily status
    const statusExists = await AttendanceModel.checkDailyStatus(employee_id);
    console.log("🧩 Daily status check result:", statusExists);

    if (statusExists.length === 0) {
      await AttendanceModel.insertDailyStatus(employee_id, 0);
      console.log("🆕 Daily status created for employee:", employee_id);
    } else {
      await AttendanceModel.updateDailyStatus(employee_id, 0);
      console.log("♻️ Daily status updated for employee:", employee_id);
    }

    // ✅ Emit socket update
    if (req.io) req.io.emit('attendanceUpdated');

    return res.status(200).json({ 
      message: 'Time out recorded successfully' 
    });

  } catch (err) {
    console.error("❌ Error during time out process:", err);
    return res.status(500).json({ 
      message: 'Error during time out process', 
      error: err.message || err.toString(), 
      stack: err.stack 
    });
  }
};

// controllers/AttendanceControl.js
export const getDailyStatus = async (req, res) => {
  const { employeeID } = req.params;

  try {
    const status = await AttendanceModel.checkDailyStatus(employeeID);

    if (status.length === 0) {
      return res.status(404).json({ message: 'No daily status found for this employee.' });
    }

    // Get the most recent record
    const latestStatus = status[status.length - 1];
    const { attendance_status, timestamp } = latestStatus;

    // ✅ Convert timestamp to 12-hour time (hour:minute AM/PM)
    const timeOnly = new Date(timestamp).toLocaleTimeString('en-PH', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true,
    });

    // ✅ Return the result
    return res.status(200).json({
      employeeID,
      attendance_status,
      time: timeOnly,
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
