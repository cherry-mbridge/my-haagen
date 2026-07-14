export function cleanJSON(text){

    return text
        .replace("```json","")
        .replace("```","")
        .trim();

}