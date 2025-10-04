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
  const query = "SELECT * FROM daily_status WHERE employeeID = ?";
  return new Promise((resolve, reject) => {
    db.query(query, [employeeID], (err, results) => {
      if (err) return reject(err);
      resolve(results);
    });
  });
},

getLatestTimestamp: (employeeID) => {
  return new Promise((resolve, reject) => {
    const query = `
      SELECT timestamp 
      FROM tbl_attendance 
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

};

export default AttendanceModel;
