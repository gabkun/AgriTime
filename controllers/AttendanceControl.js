import AttendanceModel from '../models/attendanceModel.js'
import PDFDocument from "pdfkit";
import path from "path";
import fs from "fs";

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

export const breaktime = async (req, res) => {
  const { employeeID } = req.body;
  if (!employeeID)
    return res.status(400).json({ message: 'Employee ID is required' });

  try {
    console.log("📥 Incoming employeeID:", employeeID);

    // ✅ Check if employee exists
    const employeeExists = await AttendanceModel.checkEmployeeExists(employeeID);
    if (employeeExists.length === 0) {
      return res.status(404).json({ message: 'Employee not found' });
    }

    // ✅ Check if already break in today
    const statusExists = await AttendanceModel.checkDailyStatus(employeeID);
    if (statusExists.length > 0 && statusExists[0].attendance_status == 3) {
      return res.status(400).json({ message: 'Already on break.' });
    }

    // ✅ Record break-in
    await AttendanceModel.recordBreakIn(employeeID);
    console.log("✅ Break-in recorded for employee:", employeeID);

    // ✅ Update or insert daily status to 3 (On Break)
    if (statusExists.length === 0) {
      await AttendanceModel.insertDailyStatus(employeeID, 3);
    } else {
      await AttendanceModel.updateDailyStatus(employeeID, 3);
    }

    if (req.io) req.io.emit('attendanceUpdated');

    return res.status(200).json({ message: 'Break-in recorded successfully' });
  } catch (err) {
    console.error("❌ Error during break-in process:", err);
    return res.status(500).json({ message: 'Error during break-in process' });
  }
};

export const breakout = async (req, res) => {
  const { employeeID } = req.body;
  if (!employeeID)
    return res.status(400).json({ message: 'Employee ID is required' });

  try {
    console.log("📤 Incoming employeeID:", employeeID);

    // ✅ Check if employee exists
    const employeeExists = await AttendanceModel.checkEmployeeExists(employeeID);
    if (employeeExists.length === 0) {
      return res.status(404).json({ message: 'Employee not found' });
    }

    // ✅ Find latest break record with no stop_break
    const breakRecord = await AttendanceModel.getActiveBreak(employeeID);
    if (breakRecord.length === 0) {
      return res.status(400).json({ message: 'No active break found.' });
    }

    // ✅ Record break-out
    await AttendanceModel.recordBreakOut(employeeID);
    console.log("✅ Break-out recorded for employee:", employeeID);

    // ✅ Update daily status back to 1 (Active)
    await AttendanceModel.updateDailyStatus(employeeID, 1);

    if (req.io) req.io.emit('attendanceUpdated');

    return res.status(200).json({ message: 'Break-out recorded successfully' });
  } catch (err) {
    console.error("❌ Error during break-out process:", err);
    return res.status(500).json({ message: 'Error during break-out process' });
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

export const getLateDaysReport = async (req, res) => {
  const { employeeID } = req.params;

  if (!employeeID) {
    return res.status(400).json({ message: "Employee ID is required" });
  }

  try {
    const report = await AttendanceModel.getAttendanceReport(employeeID);

    if (!report || report.length === 0) {
      return res.status(404).json({ message: "No attendance records found" });
    }

    // Set reference time for 8:00 AM Manila time
    const lateThreshold = new Date();
    lateThreshold.setHours(8, 0, 0, 0);

    const lateRecords = [];

    report.forEach((r) => {
      if (!r.time_in) return;

      const timeIn = new Date(r.time_in);
      const timeInPH = new Date(timeIn.toLocaleString("en-US", { timeZone: "Asia/Manila" }));

      // If employee timed in after 8:00 AM
      if (timeInPH.getHours() > 8 || (timeInPH.getHours() === 8 && timeInPH.getMinutes() > 0)) {
        lateRecords.push({
          date: new Date(r.date).toLocaleDateString("en-PH", { timeZone: "Asia/Manila" }),
          time_in: timeInPH.toLocaleTimeString("en-PH", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: true,
            timeZone: "Asia/Manila",
          }),
        });
      }
    });

    res.status(200).json({
      employeeID,
      totalLateDays: lateRecords.length,
      lateRecords,
    });
  } catch (err) {
    console.error("❌ Error generating late days report:", err);
    res.status(500).json({
      message: "Error generating late days report",
      error: err.message,
    });
  }
};


export const generatePayslip = async (req, res) => {
  const {
    employeeID,
    startDate,
    endDate,

    overtimeHours = 0,
    o_amount = 0,

    rd_days = 0,
    rd_amount = 0,

    holiday_days = 0,
    h_amount = 0,

    sssDeduction = 0,
    pagibigDeduction = 0,
    philhealthDeduction = 0,
  } = req.body;

  // ✅ STEP 1: validate required fields
  if (!employeeID || !startDate || !endDate) {
    return res.status(400).json({ message: "Missing required fields" });
  }

  try {

    // ✅ STEP 2: get attendance (if you still want totalHours)
    const records = await AttendanceModel.getAttendanceReport(employeeID);

    if (!records || records.length === 0) {
      return res.status(404).json({
        message: "No attendance records found."
      });
    }

    const start = new Date(startDate);
    const end = new Date(endDate);

    let totalHours = 0;

    records.forEach((r) => {
      const d = new Date(r.date);

      if (d >= start && d <= end && r.total_time) {

        const [h, m, s] = r.total_time.split(":").map(Number);

        totalHours += h + m/60 + s/3600;
      }
    });

    totalHours = Number(totalHours.toFixed(2));


    // ✅ STEP 3: PUT IT HERE (THIS IS THE CORRECT LOCATION)
    const user = await AttendanceModel.getUserPay(employeeID);

    const monthlyBasicPay = Number(user.basicPay || 0);

    const dailyBasicPay = monthlyBasicPay / 22;


    // ✅ STEP 4: call model and PASS dailyBasicPay
    const payslip = await AttendanceModel.generatePayslip(
      employeeID,
      startDate,
      endDate,

      totalHours,

      Number(overtimeHours),
      Number(o_amount),

      Number(rd_days),
      Number(rd_amount),

      Number(holiday_days),
      Number(h_amount),

      Number(dailyBasicPay.toFixed(2)), // ✅ PASS THIS HERE

      Number(sssDeduction),
      Number(pagibigDeduction),
      Number(philhealthDeduction)
    );


    // ✅ STEP 5: return response
    return res.status(200).json(payslip);


  } catch (err) {

    console.error(err);

    return res.status(500).json({
      message: "Error generating payslip",
      error: err.message
    });

  }
};

export const getAllDailyStatus = async (req, res) => {
  try {
    // ✅ Fetch all daily statuses for today
    const allStatus = await AttendanceModel.getAllDailyStatus();

    if (allStatus.length === 0) {
      return res.status(404).json({
        message: "No attendance records found for today.",
        data: [],
      });
    }

    // ✅ Format timestamps to Philippine time
    const formattedStatus = allStatus.map((record) => {
      const formattedTime = new Date(record.timestamp).toLocaleTimeString("en-PH", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
        timeZone: "Asia/Manila",
      });
      return {
        employeeID: record.employeeID,
        name: record.name,
        attendance_status: record.attendance_status,
        time: formattedTime,
      };
    });

    return res.status(200).json({
      message: "Fetched all employees' daily status successfully.",
      data: formattedStatus,
    });
  } catch (err) {
    console.error("❌ Error fetching all daily statuses:", err);
    return res.status(500).json({
      message: "Error fetching all daily statuses.",
      error: err.message,
    });
  }
};

export const downloadPayslip = async (req, res) => {
  const { employeeID } = req.params;

  if (!employeeID) {
    return res.status(400).json({ message: "Employee ID is required" });
  }

  try {
    // 1️⃣ Get the latest payslip record
    const payslipRecords = await AttendanceModel.getLatestPayslip(employeeID);

    if (!payslipRecords || payslipRecords.length === 0) {
      return res.status(404).json({ message: "No payslip found for this employee." });
    }

    const payslip = payslipRecords[0];

    // 2️⃣ Get employee info
    const user = await AttendanceModel.getEmployeeDetails(employeeID);
    if (!user) {
      return res.status(404).json({ message: "Employee not found." });
    }

    // =============================
    // ✅ FIXED CALCULATIONS (MATCH DB LOGIC)
    // =============================
    // Earnings are based on payslip record (manual amounts + daily basic)
    // gross in DB = dailyBasicPay + o_amount + h_amount + rd_amount
    // NOTE: allowances are DISPLAY ONLY unless you decide to include it in gross too.
    const dailyBasicPay = Number(payslip.dailyBasicPay || 0);
    const monthlyBasicPay = Number(payslip.basicpay || 0);

    const overtimePay = Number(payslip.o_amount || 0);
    const holidayPay = Number(payslip.h_amount || 0);
    const restDayPay = Number(payslip.rd_amount || 0);

    // Use DB gross to avoid mismatch
    const grossPay = Number(payslip.gross || (dailyBasicPay + overtimePay + holidayPay + restDayPay));

    const totalDeductions =
      Number(payslip.sssDeduction || 0) +
      Number(payslip.pagibigDeduction || 0) +
      Number(payslip.philhealthDeduction || 0);

    const netPay = grossPay - totalDeductions;

    // 3️⃣ Initialize PDF
    const doc = new PDFDocument({ margin: 50 });
    const fileName = `Payslip_${employeeID}_${Date.now()}.pdf`;
    const filePath = path.join("./uploads", fileName);
    const writeStream = fs.createWriteStream(filePath);
    doc.pipe(writeStream);

    // =============================
    // HEADER WITH LOGO (same styling)
    // =============================
    const logoPath = path.join("./uploads", "logo.jpg"); // adjust if needed
    if (fs.existsSync(logoPath)) {
      doc.image(logoPath, 60, 40, { width: 60 });
    }

    doc
      .fillColor("#64A70B")
      .fontSize(18)
      .font("Helvetica-Bold")
      .text("AgriTime Payroll Attendance System", 130, 55)
      .moveDown(2);

    doc
      .fillColor("black")
      .fontSize(10)
      .font("Helvetica")
      .text("Company Name Here Corp", { align: "right" })
      .text("Barangay Taculing, Bacolod City,", { align: "right" })
      .text("Negros Occidental, 6000", { align: "right" })
      .moveDown(2);

    // =============================
    // EMPLOYEE INFO
    // =============================
    doc
      .fontSize(11)
      .font("Helvetica")
      .text(`Employee Number: ${employeeID}`)
      .text(`Employee Name: ${user.firstName} ${user.lastName}`)
      .text(
        `Period Covered: ${new Date(payslip.startDate).toLocaleDateString("en-US", {
          year: "numeric",
          month: "short",
          day: "2-digit",
        })} to ${new Date(payslip.endDate).toLocaleDateString("en-US", {
          year: "numeric",
          month: "short",
          day: "2-digit",
        })}`
      )
      .text(
        `Date Generated: ${new Date(payslip.created).toLocaleDateString("en-US", {
          year: "numeric",
          month: "short",
          day: "2-digit",
        })}`
      )
      .moveDown(1.5);

    // =============================
    // EARNINGS & DEDUCTIONS HEADER
    // =============================
    const headerY = doc.y;
    doc
      .rect(50, headerY, 500, 20)
      .fill("#64A70B");

    doc
      .fillColor("white")
      .fontSize(11)
      .font("Helvetica-Bold")
      .text("EARNINGS", 70, headerY + 5)
      .text("DEDUCTIONS", 350, headerY + 5);

    doc.moveDown(2);
    doc.fillColor("black").font("Helvetica").fontSize(10);

    // =============================
    // DATA SECTION (UPDATED)
    // =============================
    const earningsStartY = doc.y;
    const rowHeight = 18;

    // ✅ Earnings now reflect the payslip calculation
    const earnings = [
      { label: "Basic Pay (Monthly)", value: `PHP${monthlyBasicPay.toFixed(2)}` },
      { label: "Daily Basic Pay", value: `PHP${dailyBasicPay.toFixed(2)}` },

      { label: "Overtime Hours", value: `${Number(payslip.overtimeHours || 0).toFixed(2)} hrs` },
      { label: "Overtime Pay", value: `PHP${overtimePay.toFixed(2)}` },

      { label: "Holiday Days", value: `${Number(payslip.holiday_days || 0)}` },
      { label: "Holiday Pay", value: `PHP${holidayPay.toFixed(2)}` },

      { label: "Rest Day Days", value: `${Number(payslip.rd_days || 0)}` },
      { label: "Rest Day Pay", value: `PHP${restDayPay.toFixed(2)}` },

      { label: "Total Hours Worked", value: `${Number(payslip.totalHours || 0).toFixed(2)} hrs` },

      // Display allowances from users table (DISPLAY ONLY)
      { label: "Allowances (Display)", value: `PHP${Number(user.allowances || 0).toFixed(2)}` },
    ];

    const deductions = [
      { label: "SSS Deduction", value: `PHP${Number(payslip.sssDeduction || 0).toFixed(2)}` },
      { label: "Pag-IBIG Deduction", value: `PHP${Number(payslip.pagibigDeduction || 0).toFixed(2)}` },
      { label: "PhilHealth Deduction", value: `PHP${Number(payslip.philhealthDeduction || 0).toFixed(2)}` },
    ];

    const maxRows = Math.max(earnings.length, deductions.length);

    for (let i = 0; i < maxRows; i++) {
      const y = earningsStartY + i * rowHeight;

      // prevent overflow to next page
      if (y > doc.page.height - 140) {
        doc.addPage();

        // redraw header on new page (same style)
        const newHeaderY = 60;
        doc
          .rect(50, newHeaderY, 500, 20)
          .fill("#64A70B");

        doc
          .fillColor("white")
          .fontSize(11)
          .font("Helvetica-Bold")
          .text("EARNINGS", 70, newHeaderY + 5)
          .text("DEDUCTIONS", 350, newHeaderY + 5);

        doc.moveDown(2);
        doc.fillColor("black").font("Helvetica").fontSize(10);

        // reset baseline for rows after page break
        // push remaining rows starting at current doc.y
        const pageStartY = doc.y;
        for (let j = i; j < maxRows; j++) {
          const yy = pageStartY + (j - i) * rowHeight;

          if (earnings[j]) {
            doc.text(earnings[j].label, 70, yy);
            doc.text(earnings[j].value, 220, yy, { width: 120, align: "right" });
          }

          if (deductions[j]) {
            doc.text(deductions[j].label, 350, yy);
            doc.text(deductions[j].value, 520, yy, { width: 60, align: "right" });
          }
        }

        // done printing rows after break
        break;
      }

      if (earnings[i]) {
        doc.text(earnings[i].label, 70, y);
        doc.text(earnings[i].value, 220, y, { width: 120, align: "right" });
      }

      if (deductions[i]) {
        doc.text(deductions[i].label, 350, y);
        doc.text(deductions[i].value, 520, y, { width: 60, align: "right" });
      }
    }

    // =============================
    // TOTALS (FIXED)
    // =============================
    doc.moveDown(3);
    doc.font("Helvetica-Bold").fontSize(11);

    doc.text(`GROSS PAY: PHP${grossPay.toFixed(2)}`, 70);
    doc.text(`TOTAL DEDUCTIONS: PHP${totalDeductions.toFixed(2)}`, 350);

    doc.moveDown(1.5);

    // NET PAY
    doc
      .fontSize(14)
      .fillColor("#64A70B")
      .text(`NET PAY: PHP${netPay.toFixed(2)}`, { align: "center" })
      .fillColor("black")
      .moveDown(3);

    // FOOTER
    doc
      .fontSize(9)
      .text("This is a system-generated payslip. No signature required.", {
        align: "center",
      });

    doc.end();

    // 4️⃣ Send PDF file
    writeStream.on("finish", () => {
      res.download(filePath, fileName, (err) => {
        if (err) console.error(err);
        if (fs.existsSync(filePath)) fs.unlinkSync(filePath);
      });
    });
  } catch (err) {
    console.error("❌ Error generating payslip PDF:", err);
    res.status(500).json({ message: "Error generating PDF", error: err.message });
  }
};

export const getAllpayslip = async (req, res) => {
  try {
    const payslips = await AttendanceModel.getAllpayslip();
    if (payslips.length === 0) {
      return res.status(404).json({ message: "No payslips found." });
    }
    res.status(200).json({
      message: "Fetched all payslips successfully.",
      data: payslips,
    });
  } catch (error) {
    console.error("Error fetching payslips:", error);
    res.status(500).json({
      message: "Internal Server Error.",
      error: error.message,
    });
  }
};

export const getPayslipsByEmployeeID = async (req, res) => {
  try {
    const { employeeID } = req.params;

    if (!employeeID) {
      return res.status(400).json({ message: "employeeID is required." });
    }

    const payslips = await AttendanceModel.getPayslipsByEmployeeID(employeeID);

    if (payslips.length === 0) {
      return res.status(404).json({
        message: `No payslips found for employeeID: ${employeeID}`,
      });
    }

    res.status(200).json({
      message: "Fetched payslips per employee successfully.",
      data: payslips,
    });
  } catch (error) {
    console.error("Error fetching payslips per employee:", error);
    res.status(500).json({
      message: "Internal Server Error.",
      error: error.message,
    });
  }
};

export const downloadPayslipData = async (req, res) => {
  const { id } = req.params; // ✅ PAYSLIP ID

  if (!id) {
    return res.status(400).json({ message: "Payslip ID is required" });
  }

  try {
    // 1️⃣ Get payslip by ID
    const payslip = await AttendanceModel.getPayslipById(id);

    if (!payslip) {
      return res.status(404).json({ message: "Payslip not found." });
    }

    // 2️⃣ Get employee info USING employeeID from payslip
    const user = await AttendanceModel.getEmployeeDetails(payslip.employeeID);

    if (!user) {
      return res.status(404).json({ message: "Employee not found." });
    }

    // 3️⃣ Initialize PDF
    const doc = new PDFDocument({ margin: 50 });
    const fileName = `Payslip_${id}_${Date.now()}.pdf`;
    const filePath = path.join("./uploads", fileName);
    const writeStream = fs.createWriteStream(filePath);

    doc.pipe(writeStream);

    // =============================
    // HEADER WITH LOGO
    // =============================
    const logoPath = path.join("./uploads", "logo.jpg");
    if (fs.existsSync(logoPath)) {
      doc.image(logoPath, 60, 40, { width: 60 });
    }

    doc
      .fillColor("#64A70B")
      .fontSize(18)
      .font("Helvetica-Bold")
      .text("AgriTime Payroll Attendance System ADMIN COPY", 130, 55)
      .moveDown(2);

    doc
      .fillColor("black")
      .fontSize(10)
      .font("Helvetica")
      .text("Company Name Here Corp", { align: "right" })
      .text("Barangay Taculing, Bacolod City", { align: "right" })
      .text("Negros Occidental, 6000", { align: "right" })
      .moveDown(2);

    // =============================
    // EMPLOYEE INFO
    // =============================
    doc
      .fontSize(11)
      .text(`Employee ID: ${payslip.employeeID}`)
      .text(`Employee Name: ${user.firstName} ${user.lastName}`)
      .text(
        `Period Covered: ${new Date(payslip.startDate).toLocaleDateString()} 
        to ${new Date(payslip.endDate).toLocaleDateString()}`
      )
      .text(`Date Generated: ${new Date(payslip.created).toLocaleDateString()}`)
      .moveDown(1.5);

    // =============================
    // EARNINGS & DEDUCTIONS HEADER
    // =============================
    doc
      .rect(50, doc.y, 500, 20)
      .fill("#64A70B")
      .fillColor("white")
      .fontSize(11)
      .font("Helvetica-Bold")
      .text("EARNINGS", 70, doc.y + 5)
      .text("DEDUCTIONS", 350, doc.y + 5);

    doc.moveDown(2);
    doc.fillColor("black").font("Helvetica").fontSize(10);

    // =============================
    // DATA
    // =============================
    const earnings = [
      { label: "Basic Pay", value: `PHP ${user.basicPay}` },
      { label: "Allowances", value: `PHP ${user.allowances}` },
      { label: "Total Hours Worked", value: `${payslip.totalHours} hrs` },
      { label: "Overtime Hours", value: `${payslip.overtimeHours} hrs` },
    ];

    const deductions = [
      { label: "SSS Deduction", value: `PHP ${payslip.sssDeduction}` },
      { label: "Pag-IBIG Deduction", value: `PHP ${payslip.pagibigDeduction}` },
      { label: "PhilHealth Deduction", value: `PHP ${payslip.philhealthDeduction}` },
    ];

    const startY = doc.y;
    const rowHeight = 18;

    for (let i = 0; i < Math.max(earnings.length, deductions.length); i++) {
      const y = startY + i * rowHeight;

      if (earnings[i]) {
        doc.text(earnings[i].label, 70, y);
        doc.text(earnings[i].value, 200, y);
      }

      if (deductions[i]) {
        doc.text(deductions[i].label, 350, y);
        doc.text(deductions[i].value, 480, y);
      }
    }

    // =============================
    // TOTALS
    // =============================
    const grossPay = Number(user.basicPay) + Number(user.allowances);
    const totalDeductions =
      Number(payslip.sssDeduction) +
      Number(payslip.pagibigDeduction) +
      Number(payslip.philhealthDeduction);

    const netPay = grossPay - totalDeductions;

    doc.moveDown(3);
    doc.font("Helvetica-Bold").fontSize(11);
    doc.text(`GROSS: PHP ${grossPay.toFixed(2)}`, 70);
    doc.text(`TOTAL DEDUCTIONS: PHP ${totalDeductions.toFixed(2)}`, 350);

    doc
      .moveDown(1.5)
      .fontSize(14)
      .fillColor("#64A70B")
      .text(`NET PAY: PHP ${netPay.toFixed(2)}`, { align: "center" })
      .fillColor("black")
      .moveDown(3);

    doc
      .fontSize(9)
      .text("This is a system-generated payslip. No signature required.", {
        align: "center",
      });

    doc.end();

    // 4️⃣ Download
    writeStream.on("finish", () => {
      res.download(filePath, fileName, () => {
        fs.unlinkSync(filePath);
      });
    });

  } catch (err) {
    console.error("❌ Error generating payslip:", err);
    res.status(500).json({ message: "Error generating payslip" });
  }
};

export const getAttendanceSummary = async (req, res) => {
  const { employeeID } = req.params;

  if (!employeeID) {
    return res.status(400).json({ message: "Employee ID is required" });
  }

  try {
    const summary = await AttendanceModel.getAttendanceSummary(employeeID);

    if (!summary) {
      return res.status(404).json({ message: "No attendance records found" });
    }

    res.status(200).json({
      employeeID,
      totalDaysWorked: summary.total_days_worked,
      totalLateComings: summary.total_late_comings,
    });

  } catch (err) {
    console.error("❌ Error fetching attendance summary:", err);
    res.status(500).json({
      message: "Error fetching attendance summary",
      error: err.message,
    });
  }
};
