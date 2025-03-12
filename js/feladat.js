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

let adatok;
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
            adatok = await request.json()
            showContentData()
        }
        else if (request.status == 401) {
            window.location.href = './login.html';
        }
    } catch (error) {
        console.log(error)
    }
})

function showContentData(){
    console.log(adatok)
    let title = $("title")
    let createdDate = $("creatingDate")
    let max_points =  $("maxPoint")
    let createUser = $("createrUser")
    let limitDate = $("timeLimitDate")
    let description = $("description")
    title.innerHTML = adatok.title
    createdDate.innerHTML = convertDate(adatok.published).toLocaleString()
    createUser.innerHTML = adatok.lastname + " " + adatok.firstname
    limitDate.innerHTML = "<b>Határidő: </b>" + (adatok.deadline != null ? convertDate(adatok.deadline).toLocaleString() : 'Nincsen határidő beállítva')
    description.innerHTML = adatok.description
    if(adatok.max_points == null){
        max_points.innerHTML = "Nincs ponthatár beállítva!"
    }else{
        max_points.innerHTML = "<b>Ponthatár: </b>" + adatok.max_points + " p"
    }
}

function showModal(){
    $("edit-modal").style.display = "flex";
    let title = $("ContentTitle").value = adatok.title
    let max_points =  $("maxPoints").value = adatok.max_points
    let deadline = convertDate(adatok.deadline);
    deadline.setMinutes(deadline.getMinutes() - deadline.getTimezoneOffset());
    let limitDate = $("deadline-input").value = deadline.toISOString().slice(0, 16);
    let description = $("description-input").value = adatok.description
}

async function ModifyData() {
    let title = $("ContentTitle").value
    let max_points = $("maxPoints").value
    if(max_points == ""){max_points = null}
    let limitDate = $("deadline-input").value
    let description = $("description-input").value
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    let reqData = {
        "content_id" : parseInt(tartalomId),
        "title" : title,
        "desc" : description,
        "task" : true,
        "maxpoint" : parseInt(max_points),
        "deadline" : limitDate
    }
    try {
        let request = await fetch("api/content/modify-data",{
            method : "POST",
            headers : {"Content-Type" : "application/json"},
            body : JSON.stringify(reqData)
        })
        let response = await request.json()
        if(response.sikeres == true){
            setTimeout(function(){
                location.reload()
            }, 1500)
        }
        else{
            showAlert(response.uzenet)
        }
    } catch (error) {
        console.log(error)
    }
}

function showAlert(uzenet){
    let alertDiv = $("alertDiv")
    alertDiv.style.display = "flex"
    alertDiv.innerHTML = uzenet
}

$("save-btn").addEventListener("click",ModifyData)

document.getElementById("modifyBtn").addEventListener("click", showModal);

// Modal bezárása a "close" gombra kattintva
document.querySelector(".close").addEventListener("click", function () {
    document.getElementById("edit-modal").style.display = "none";
});