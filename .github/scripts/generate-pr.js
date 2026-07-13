import fs from "fs";

const diff = fs.readFileSync("diff.patch", "utf8");

const prompt = {
  model: "gemini-2.5-flash",
  prompt: `
You are a Senior Software Engineer.

Analyze this git diff.

Return JSON only.

${diff}
`
};

fs.writeFileSync(
  "prompt.json",
  JSON.stringify(prompt, null, 2)
);

console.log("prompt.json created");