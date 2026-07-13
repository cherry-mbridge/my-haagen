import fs from "fs";

const diff = fs.readFileSync("diff.patch", "utf8");

console.log(diff);