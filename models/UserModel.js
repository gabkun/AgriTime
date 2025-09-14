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
        (firstName, lastName, dob, email, password, contactNo, role, profilePic, nationality, maritalStatus, emergencyContact, employeeID)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
};

export default User;