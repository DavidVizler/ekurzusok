function currentDateLoad(){
    let creatingDate = document.getElementById("creatingDate")
    let actualDate = new Date()
    let currentDate = actualDate.toISOString().split("T")[0]
    let time = actualDate.getHours() + ":" + actualDate.getMinutes()
    creatingDate.innerHTML += currentDate + ", " + time
}

window.addEventListener("load", currentDateLoad)

document.getElementById("uploadFileButton").addEventListener("click",()=>{
    document.getElementById("fileInput").click()
})

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
            },
            body : JSON.stringify(reqData)
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
    let max_points =  document.getElementById("maxPoint")
    let createUser = document.getElementById("createrUser")
    let limitDate = document.getElementById("timeLimitDate")
    let description = document.getElementById("description")
    title.innerHTML = adatok.title
    createdDate.innerHTML = adatok.published
    createUser.innerHTML = adatok.lastname + " " + adatok.firstname
    limitDate.innerHTML = "Határidő: " + adatok.deadline
    description.innerHTML = adatok.description
    if(adatok.max_points == null){
        max_points.innerHTML = "Nincs ponthatár beállítva!"
    }else{
        max_points.innerHTML = "Pontszám: " + adatok.max_points
    }
}