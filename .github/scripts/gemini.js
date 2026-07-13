import { GoogleGenAI } from "@google/genai";
import { createPrompt } from "./prompt.js";


const ai = new GoogleGenAI({
    apiKey: process.env.GEMINI_API_KEY
});


export async function generatePR(diff){

    const prompt = createPrompt(diff);


    const response =
        await ai.models.generateContent({

            model: "gemini-2.5-flash",

            contents: prompt
        });


    let text = response.text;


    text = text
        .replace("```json","")
        .replace("```","")
        .trim();


    return JSON.parse(text);

}