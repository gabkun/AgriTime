import express from "express";
import bodyParser from "body-parser";
import cors from "cors"
import dotenv from "dotenv"
import path from "path";

dotenv.config();

const app = express();
const PORT = process.env.PORT || 4000;
app.use(express.static(path.join(process.cwd(), "views")));


app.use(bodyParser.json());
app.use(cors());

app.listen(PORT, '0.0.0.0', () => {
  console.log('gwapo Dominic');
  console.log(`Server running on port http://localhost:${PORT}`);
});