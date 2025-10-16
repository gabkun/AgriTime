import db from '../config/db.js';

const User = {
  getAll: () => {
    return new Promise((resolve, reject) => {
      db.query('SELECT * FROM users', (err, results) => {
        if (err) return reject(err);
        resolve(results);
      });
    });
  },

  getById: (id) => {
    return new Promise((resolve, reject) => {
      db.query('SELECT * FROM users WHERE id = ?', [id], (err, results) => {
        if (err) return reject(err);
        resolve(results[0]);
      });
    });
  },

  // Create user
  create: (data) => {
    const {
      firstName,
      lastName,
      dob,
      email,
      password,
      contactNo,
      role,
      profilePic,
      nationality,
      maritalStatus,
      emergencyContact,
      employeeID
    } = data;

    return new Promise((resolve, reject) => {
    const sql = `
      INSERT INTO users 
      (firstName, lastName, dob, email, password, contactNo, role, profilePic, nationality, maritalStatus, emergencyContact, employeeID, created_at, status)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)
    `;
      db.query(
        sql,
        [firstName, lastName, dob, email, password, contactNo, role, profilePic, nationality, maritalStatus, emergencyContact, employeeID],
        (err, results) => {
          if (err) return reject(err);
          resolve(results.insertId);
        }
      );
    });
  },

  // Update user
  update: (id, data) => {
    return new Promise((resolve, reject) => {
      db.query('UPDATE users SET ? WHERE id = ?', [data, id], (err, results) => {
        if (err) return reject(err);
        resolve(results);
      });
    });
  },

  // Delete user
  delete: (id) => {
    return new Promise((resolve, reject) => {
      db.query('DELETE FROM users WHERE id = ?', [id], (err, results) => {
        if (err) return reject(err);
        resolve(results);
      });
    });
  },
    // ✅ Used for facial login
  findByLastName: (lastName) => {
    return new Promise((resolve, reject) => {
      db.query("SELECT * FROM users WHERE lastName = ?", [lastName], (err, results) => {
        if (err) return reject(err);
        resolve(results[0]);
      });
    });
  },

// ✅ Update salary info based on employeeID (for admin use)
updateSalaryByEmployeeID: (employeeID, data) => {
  const { basicPay, allowances } = data;

  return new Promise((resolve, reject) => {
    const sql = `UPDATE users SET basicPay = ?, allowances = ? WHERE employeeID = ?`;
    db.query(sql, [basicPay, allowances, employeeID], (err, results) => {
      if (err) return reject(err);
      resolve(results);
    });
  });
},


// ✅ Get all users including salary information (for admin view)
getAllUserData: () => {
  return new Promise((resolve, reject) => {
    const sql = `
      SELECT 
        id,
        firstName,
        lastName,
        dob,
        email,
        contactNo,
        role,
        nationality,
        maritalStatus,
        emergencyContact,
        employeeID,
        basicPay,
        allowances,
        profilePic
      FROM users
    `;
    db.query(sql, (err, results) => {
      if (err) return reject(err);
      resolve(results);
    });
  });
},


};

export default User;