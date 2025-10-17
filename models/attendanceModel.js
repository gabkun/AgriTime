import db from '../config/db.js';

const AttendanceModel = {
  // Record Time In
  recordTimeIn: (employeeID) => {
    const query = "INSERT INTO time_in (employeeID, time_in) VALUES (?, NOW())";
    return new Promise((resolve, reject) => {
      db.query(query, [employeeID], (err, result) => {
        if (err) return reject(err);
        resolve(result);
      });
    });
  },

  // Record Time Out
  recordTimeOut: (employeeID) => {
    const query = "INSERT INTO time_out (employeeID, time_out) VALUES (?, NOW())";
    return new Promise((resolve, reject) => {
      db.query(query, [employeeID], (err, result) => {
        if (err) return reject(err);
        resolve(result);
      });
    });
  },

  // Insert new daily status
  insertDailyStatus: (employeeID, status) => {
    const query = "INSERT INTO daily_status (employeeID, attendance_status, timestamp) VALUES (?, ?, NOW())";
    return new Promise((resolve, reject) => {
      db.query(query, [employeeID, status], (err, result) => {
        if (err) return reject(err);
        resolve(result);
      });
    });
  },

  // Update existing daily status
  updateDailyStatus: (employeeID, status) => {
    const query = "UPDATE daily_status SET attendance_status = ?, timestamp = NOW() WHERE employeeID = ?";
    return new Promise((resolve, reject) => {
      db.query(query, [status, employeeID], (err, result) => {
        if (err) return reject(err);
        resolve(result);
      });
    });
  },

  // Check if employee exists
  checkEmployeeExists(employeeID) {
    return new Promise((resolve, reject) => {
      const query = "SELECT * FROM users WHERE employeeID = ?";
      db.query(query, [employeeID], (err, results) => {
        if (err) return reject(err);
        resolve(results);
      });
    });
  },

  // Check if employee has daily status
  checkDailyStatus: (employeeID) => {
    return new Promise((resolve, reject) => {
      const query = `
        SELECT attendance_status, timestamp 
        FROM daily_status 
        WHERE employeeID = ? 
        AND DATE(timestamp) = CURDATE()
        ORDER BY timestamp ASC
      `;
      db.query(query, [employeeID], (err, results) => {
        if (err) return reject(err);
        resolve(results);
      });
    });
  },

  // Get latest timestamp for today
  getLatestTimestamp: (employeeID) => {
    return new Promise((resolve, reject) => {
      const query = `
        SELECT timestamp 
        FROM daily_status 
        WHERE employee_id = ? AND DATE(timestamp) = CURDATE()
        ORDER BY timestamp DESC 
        LIMIT 1
      `;
      db.query(query, [employeeID], (err, result) => {
        if (err) return reject(err);
        resolve(result);
      });
    });
  },

  // ✅ Generate Attendance Report
  getAttendanceReport: (employeeID) => {
    return new Promise((resolve, reject) => {
      const query = `
        SELECT 
          ti.employeeID,
          DATE(ti.time_in) AS date,
          ti.time_in,
          to1.time_out,
          TIMEDIFF(to1.time_out, ti.time_in) AS total_time
        FROM time_in ti
        JOIN time_out to1 
          ON ti.employeeID = to1.employeeID
          AND DATE(ti.time_in) = DATE(to1.time_out)
        WHERE ti.employeeID = ?
        ORDER BY ti.time_in DESC
      `;
      db.query(query, [employeeID], (err, results) => {
        if (err) return reject(err);
        resolve(results);
      });
    });
  },

  // ✅ Generate Payslip (NEW FUNCTION)
  generatePayslip: (
    employeeID,
    startDate,
    endDate,
    totalHours,
    overtimeHours,
    sssDeduction,
    pagibigDeduction,
    philhealthDeduction
  ) => {
    return new Promise((resolve, reject) => {
      // Get employee basic pay
      const getPayQuery = "SELECT basicPay FROM users WHERE employeeID = ?";
      db.query(getPayQuery, [employeeID], (err, result) => {
        if (err) return reject(err);
        if (result.length === 0) return reject(new Error("Employee not found"));

        const basicPay = result[0].basicPay;
        const dailyHours = 8;
        const workingDaysPerMonth = 22;
        const hourlyRate = basicPay / (workingDaysPerMonth * dailyHours);

        const regularPay = totalHours * hourlyRate;
        const overtimePay = overtimeHours * (hourlyRate * 1.25);
        const grossPay = regularPay + overtimePay;

        const totalDeductions =
          Number(sssDeduction) +
          Number(pagibigDeduction) +
          Number(philhealthDeduction);

        const netPay = grossPay - totalDeductions;

        const insertPayslipQuery = `
          INSERT INTO pay_slip 
          (employeeID, startDate, endDate, totalHours, overtimeHours, sssDeduction, pagibigDeduction, philhealthDeduction, created)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        `;

        db.query(
          insertPayslipQuery,
          [
            employeeID,
            startDate,
            endDate,
            totalHours,
            overtimeHours,
            sssDeduction,
            pagibigDeduction,
            philhealthDeduction,
          ],
          (insertErr, insertRes) => {
            if (insertErr) return reject(insertErr);

            resolve({
              message: "Payslip generated successfully",
              payslip: {
                employeeID,
                startDate,
                endDate,
                totalHours,
                overtimeHours,
                hourlyRate: hourlyRate.toFixed(2),
                regularPay: regularPay.toFixed(2),
                overtimePay: overtimePay.toFixed(2),
                grossPay: grossPay.toFixed(2),
                totalDeductions: totalDeductions.toFixed(2),
                netPay: netPay.toFixed(2),
                created: "NOW()",
              },
            });
          }
        );
      });
    });
  },

  getAllDailyStatus: () => {
  return new Promise((resolve, reject) => {
    const query = `
      SELECT 
        ds.employeeID,
        u.firstName,
        ds.attendance_status,
        ds.timestamp
      FROM daily_status ds
      JOIN users u ON ds.employeeID = u.employeeID
      WHERE DATE(ds.timestamp) = CURDATE()
      ORDER BY ds.timestamp DESC
    `;
    db.query(query, (err, results) => {
      if (err) return reject(err);
      resolve(results);
    });
  });
},

};

export default AttendanceModel;
