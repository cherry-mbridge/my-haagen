import fs from "fs";
import { generatePR } from "./gemini.js";
import { updatePR } from "./github.js";

const diff = fs.readFileSync(
  "../../diff.patch",
  "utf8"
);

if (!diff.trim()) {
  console.log("No changes found");
  process.exit(0);
}


const result = await generatePR(diff);

console.log(result);


await updatePR({
    title: result.title,
    description: result.description
});

console.log("PR updated");