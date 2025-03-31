function currentDate(){
    let creatingDate = $("creatingDate")
    let actualDate = new Date()
    let currentDate = actualDate.toISOString().split('T')[0];
    creatingDate.innerHTML += currentDate
}

$("backToPreviousPage").addEventListener("click",()=>{
    window.history.go(-1)
})

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
            },body : JSON.stringify(reqData)
        })
        if(request.ok){
            adatok = await request.json()
            showContentData(adatok)
        }
    } catch (error) {
        console.log(error)
    }
})


function showContentData(adatok){
    let title = $("title")
    let createdDate = $("creatingDate")
    let createUser = $("createrUser")
    let description = $("description")
    title.innerHTML = adatok.title
    createdDate.innerHTML = adatok.published
    createUser.innerHTML = adatok.lastname + " " + adatok.firstname
    description.innerHTML = adatok.description
    if(adatok.owned == 0){
        document.getElementById("modifyBtn").classList.add("disabled")
    }
    if(adatok.archived == 1){
        document.getElementById("modifyBtn").disabled = true
        document.getElementById("modifyBtn").style.opacity = 0.6
        document.getElementById("modifyBtn").style.cursor = "not-allowed"
        document.getElementById("deleteBtn").style.opacity = 0.6
        document.getElementById("deleteBtn").style.cursor = "not-allowed"
        document.getElementById("deleteBtn").disabled = true
    }
}
window.addEventListener("load", currentDate)

function showModal(){
    $("edit-modal").style.display = "flex";
    let title = $("ContentTitle").value = adatok.title
    let description = $("description-input").value = adatok.description
}

function confirmationModal(){
    let alertDiv = $('confirmationModalDiv')
    alertDiv.style.display = "flex"

    let modal_content = create("div", 'modal-content')
    alertDiv.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = "Biztosan törölni akarja a tartalmat?"

    let yes_button = create("button")
    modal_content.appendChild(yes_button)
    yes_button.innerHTML = "Igen"
    yes_button.addEventListener("click", DeleteContent)

    let no_button = create("button")
    modal_content.appendChild(no_button)
    no_button.innerHTML = "Nem"

    no_button.addEventListener("click",()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
}

async function ModifyData() {
    let title = $("ContentTitle").value
    let description = $("description-input").value
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    let reqData = {
        "content_id" : parseInt(tartalomId),
        "title" : title,
        "desc" : description,
        "task" : false,
        "maxpoint" : null,
        "deadline" : null
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

async function DeleteContent() {
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    try {
        let request = await fetch('api/content/delete',{
            method : 'POST',
            headers : {'Content-Type' : 'application/json'},
            body : JSON.stringify({"content_id" : parseInt(tartalomId)})
        })
        let response = await request.json()
        if(response.sikeres == false){
            showAlert(response.uzenet)
        }
        else{
            window.location.href = document.referrer;
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

async function GetContentFiles() {
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    let reqData = {
        "content_id" : parseInt(tartalomId)
    }
    try {
        let request = await fetch('api/query/content-files',{
            method : 'POST',
            headers : {'Content-Type' : 'application/json'},
            body : JSON.stringify(reqData)
        })
        if(request.ok){
            let response = await request.json()
            console.log(response)
            showFiles(response)
        }
    } catch (error) {
        console.log(error)
    }
}

function showFiles(files){
    let ki = document.querySelector('.content')
    for(let file of files){
        let fileDiv = document.createElement("div")
        fileDiv.classList.add("fileDiv")
        fileDiv.id = "fileDiv" + file.file_id
        let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg")
        svg.setAttribute("xlmns","https://www.w3.org/2000/svg")
        svg.setAttribute("fill","none")
        svg.setAttribute("viewBox","0 0 24 24")
        svg.setAttribute("stroke-width","1.5")
        svg.setAttribute("stroke","currentColor")
        svg.classList.add("size-6")

        let path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute("stroke-linecap","round")
        path.setAttribute("stroke-linejoin","round")
        path.setAttribute("d","M3.75 6.75h16.5M3.75 12H12m-8.25 5.25h16.5")

        let h1 = document.createElement("h1")
        h1.classList.add("fileName")
        h1.innerHTML = file.name

        let urlParams = getUrlParams();
        let id = urlParams.get('id');

        fileDiv.addEventListener('click', () => {
            window.location.href = `downloader?file_id=${file.file_id}&attached_to=content&id=${id}`;
        });

        fileDiv.appendChild(svg)
        svg.appendChild(path)
        fileDiv.appendChild(h1)
        ki.appendChild(fileDiv)
    }
}

$("save-btn").addEventListener("click",ModifyData)

document.getElementById("modifyBtn").addEventListener("click", showModal);
document.getElementById("deleteBtn").addEventListener("click", confirmationModal)

document.querySelector(".close").addEventListener("click", function () {
    document.getElementById("edit-modal").style.display = "none";
});

window.addEventListener('load', GetContentFiles)