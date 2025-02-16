function currentDate(){
    let creatingDate = document.getElementById("creatingDate")
    let actualDate = new Date()
    let currentDate = actualDate.toISOString().split('T')[0];
    creatingDate.innerHTML += currentDate
}

document.getElementById("backToPreviousPage").addEventListener("click",()=>{
    window.history.go(-1)
})

window.addEventListener('load',async()=>{
    let urlParams = new URLSearchParams(window.location.search);
    let tartalomId = urlParams.get('id');
    let reqData = {
        "content_id" : parseInt(tartalomId)
    }
    try {
        let request = await fetch("api/query/content-data",{
            method : "POST",
            headers : {
                "Content-Type" : "application/json"
            },body : JSON.stringify(reqData)
        })
        if(request.ok){
            let response = await request.json()
            showContentData(response)
        }
    } catch (error) {
        console.log(error)
    }
})


function showContentData(adatok){
    console.log(adatok)
    let title = document.getElementById("title")
    let createdDate = document.getElementById("creatingDate")
    let createUser = document.getElementById("createrUser")
    let description = document.getElementById("description")
    title.innerHTML = adatok.title
    createdDate.innerHTML = adatok.published
    createUser.innerHTML = adatok.lastname + " " + adatok.firstname
    description.innerHTML = adatok.description
}
window.addEventListener("load", currentDate)