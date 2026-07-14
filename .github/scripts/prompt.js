export function createPrompt(diff) {

return `
You are a senior software engineer.

Analyze this Pull Request diff.

Return ONLY JSON.

Format:

{
"title":"",
"description":""
}


Rules:

- Title must follow Conventional Commit.
- Description must be Markdown.
- Do not add explanations.

Diff:

${diff}

`;

}