import express from "express";
import http from "http";
import { Server } from "socket.io";
import bodyParser from "body-parser";
import cors from "cors";
import dotenv from "dotenv";
import path from "path";

import userRoutes from "./routes/UserRoutes.js";
import attendanceRoutes from "./routes/AttendanceRoutes.js";

dotenv.config();

const app = express();
const server = http.createServer(app);

// ✅ Initialize Socket.IO
const io = new Server(server, {
  cors: {
    origin: "*", // Allow all origins for now (adjust in production)
    methods: ["GET", "POST"],
  },
});

const PORT = process.env.PORT || 4000;

// ✅ Middleware setup
app.use(cors());
app.use(bodyParser.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(process.cwd(), "views")));

// ✅ Attach io to every request
app.use((req, res, next) => {
  req.io = io;
  next();
});

// ✅ Routes
app.use("/api/user", userRoutes);
app.use("/api/attendance", attendanceRoutes);

// ✅ Listen for socket connections
io.on("connection", (socket) => {
  console.log("🟢 A user connected:", socket.id);

  socket.on("disconnect", () => {
    console.log("🔴 User disconnected:", socket.id);
  });
});

// ✅ Start server
server.listen(PORT, "0.0.0.0", () => {
  console.log("🚀 Server running on http://localhost:" + PORT);
});
