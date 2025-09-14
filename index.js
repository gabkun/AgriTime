import express from "express";
import bodyParser from "body-parser";
import cors from "cors";
import dotenv from "dotenv";
import path from "path";
import userRoutes from './routes/UserRoutes.js';

dotenv.config();

const app = express();
const PORT = process.env.PORT || 4000;

app.use(cors());
app.use(bodyParser.json());

app.use(express.static(path.join(process.cwd(), "views")));

app.use("/api/user", userRoutes);

app.listen(PORT, '0.0.0.0', () => {
  console.log('gwapo Dominic');
  console.log(`Server running on port http://localhost:${PORT}`);
});
