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

$("selectFileButton").addEventListener("click",()=>{
    $("fileInput").click()
})

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

function showResultModal(uzenet){
    let alertDiv = $('confirmationModalDiv')
    alertDiv.style.display = "flex"

    let modal_content = create("div", 'modal-content')
    alertDiv.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = uzenet + "!"

    let ok_button = create("button")
    modal_content.appendChild(ok_button)
    ok_button.innerHTML = "OK"

    ok_button.addEventListener("click",()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    }) 
}

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

    if(adatok.owned == 0){
        $("modifyBtn").classList.add("disabled")
        $("showSubmissions").classList.add("disabled")
        $("deleteBtn").classList.add("disabled")
    }
    if(adatok.archived == 1){
        $("modifyBtn").disabled = true
        $("deleteBtn").disabled = true
        $("uploadFileButton").disabled = true
        $("uploadExerciseButton").disabled = true
        
        $("deleteBtn").classList.add("disabledButton")
        $("modifyBtn").classList.add("disabledButton")
        $("uploadFileButton").classList.add("disabledButton")
        $("uploadExerciseButton").classList.add("disabledButton")
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
    let limitDate = new Date($("deadline-input").value).toJSON()
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
            }, 100)
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

$("modifyBtn").addEventListener("click", showModal);

// Modal bezárása a "close" gombra kattintva
document.querySelector(".close").addEventListener("click", function () {
    $("edit-modal").style.display = "none";
});

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

$("deleteBtn").addEventListener("click", confirmationModal)

async function submitFiles() {
    try {

        let fileInput = $('fileInput');
        let formData = new FormData();
        
        let urlParams = getUrlParams();
        let tartalomId = urlParams.get('id');
        formData.append("content_id", tartalomId);

        for (const file of fileInput.files) {
            formData.append('files[]', file);
        }

        let response = await fetch('api/submission/upload-files', {
            method: 'POST',
            body: formData,
            redirect: "follow"
        });
        let valasz = await response.json()
        if(valasz.sikeres == false){
            showResultModal(valasz.uzenet)
        }
    }
    catch (e) {
        console.error(e);
    }
}

async function submitSubmission() {
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');

    try {
        let request = await fetch("api/submission/submit", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({"content_id": parseInt(tartalomId)})
        });
        let response = await request.json()
        if(response.sikeres == false){
            showResultModal(response.uzenet)    
        }
    } catch (error) {
        console.log(error);
    }
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
    let ki = $("contentFilesContainer")
    for(let file of files){
        let fileDiv = create('div', 'fileDiv');
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

        let h1 = create('h1', 'fileName');
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

function navigateToSubmissions(){
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    if(tartalomId){
        window.location.href = `submissions.html?id=${tartalomId}`;
    }
}

$('uploadFileButton').addEventListener('click', submitFiles);
$('uploadExerciseButton').addEventListener('click', submitSubmission)
window.addEventListener("load",GetContentFiles)
$('showSubmissions').addEventListener('click', navigateToSubmissions)