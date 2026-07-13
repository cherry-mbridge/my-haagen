import { Octokit } from "@octokit/rest";


const octokit = new Octokit({

    auth: process.env.GITHUB_TOKEN

});


export async function updatePR(data){

    const [
        owner,
        repo
    ] = process.env.REPOSITORY.split("/");


    const response = await octokit.pulls.update({

        owner,
        repo,

        pull_number: Number(process.env.PR_NUMBER),

        title: data.title,

        body: data.description ?? data.body

    });


    console.log(response.data.title);
    console.log(response.data.body);

}