function currentDateLoad(){
    let creatingDate = $("creatingDate")
    let actualDate = new Date()
    let currentDate = actualDate.toISOString().split("T")[0]
    let time = actualDate.getHours() + ":" + actualDate.getMinutes()
    creatingDate.innerHTML += currentDate + ", " + time
}

function displaySelectedFiles(files) {
    $('selectedFiles').innerHTML = '';
    if (files.length == 0) {
        let div = create('div');

        let em = create('em');
        em.innerText = "Nincs kiválasztott fájl";

        div.appendChild(em);

        $('selectedFiles').appendChild(div);
        return
    }
    for (const file of files) {
        let div = create('div');
        let title = create('h5');
        title.innerText = file.name;
        div.appendChild(title);
        $('selectedFiles').appendChild(div);
    }
}

window.addEventListener("load", currentDateLoad)

$('fileInput').addEventListener('change', (e) => displaySelectedFiles(e.target.files));

$("uploadFileButton").addEventListener("click",()=>{
    $("fileInput").click()
})

$("backToPreviousPage").addEventListener("click",()=>{
    window.history.go(-1)
})

$('modifyBtn').addEventListener('click', () => {
    // Feladat szerkesztése
});

window.addEventListener('load',async()=>{
    let urlParams = getUrlParams();
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
    let title = $("title")
    let createdDate = $("creatingDate")
    let max_points =  $("maxPoint")
    let createUser = $("createrUser")
    let limitDate = $("timeLimitDate")
    let description = $("description")
    title.innerHTML = adatok.title
    createdDate.innerHTML = adatok.published
    createUser.innerHTML = adatok.lastname + " " + adatok.firstname
    limitDate.innerHTML = "<b>Határidő: </b>" + adatok.deadline
    description.innerHTML = adatok.description
    if(adatok.max_points == null){
        max_points.innerHTML = "Nincs ponthatár beállítva!"
    }else{
        max_points.innerHTML = "<b>Ponthatár: </b>" + adatok.max_points + " p"
    }
}