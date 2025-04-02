function currentDate(){
    $("creatingDate").innerHTML = getCurrentTime();
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
            GetContentFiles()
        }
        else if (request.status == 401) {
            window.location.href = './login.html';
        }
    } catch (error) {
        console.log(error)
    }
})


function showContentData(adatok){
    console.log(adatok)
    let title = $("title")
    let createdDate = $("creatingDate")
    let createUser = $("createrUser")
    let description = $("description")
    title.innerHTML = adatok.title
    createdDate.innerHTML = adatok.published
    createUser.innerHTML = adatok.lastname + " " + adatok.firstname
    description.innerHTML = adatok.description
    if(adatok.owned == 0){
        $("modifyBtn").classList.add("disabled")
    }
    if(adatok.archived == 1){
        $("modifyBtn").disabled = true
        $("modifyBtn").style.opacity = 0.6
        $("modifyBtn").style.cursor = "not-allowed"
        $("deleteBtn").style.opacity = 0.6
        $("deleteBtn").style.cursor = "not-allowed"
        $("deleteBtn").disabled = true
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
        let urlParams = getUrlParams();
        let contentId = urlParams.get('id');

        let a = create('a', 'download');
        a.href = `downloader?file_id=${file.file_id}&attached_to=content&id=${contentId}`;
        a.target = '_self';

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

        a.appendChild(fileDiv)
        fileDiv.appendChild(svg)
        svg.appendChild(path)
        fileDiv.appendChild(h1)

        if (adatok.owned) {
            let deleteBtn = document.createElementNS('http://www.w3.org/2000/svg','svg');
            deleteBtn.style.marginLeft = 'auto';
            deleteBtn.style.padding = '10px 20px';

            deleteBtn.setAttribute("xlmns","https://www.w3.org/2000/svg")
            deleteBtn.setAttribute("fill","none")
            deleteBtn.setAttribute("viewBox","0 0 24 24")
            deleteBtn.setAttribute("stroke-width","1.5")
            deleteBtn.setAttribute("stroke","currentColor")
            deleteBtn.classList.add("size-6")
            deleteBtn.classList.add("delete")

            let path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute("stroke-linecap","round")
            path.setAttribute("stroke-linejoin","round")
            path.setAttribute("d","M6 18 18 6M6 6l12 12")

            deleteBtn.appendChild(path)

            deleteBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                await deleteFile(contentId, file.file_id)
            });
            fileDiv.appendChild(deleteBtn);
        }

        ki.appendChild(a)
    }
}

async function deleteFile(contentId, fileId) {
    try {
        let response = await fetch('api/content/remove-file', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content_id: parseInt(contentId), file_id: fileId })
        });
        GetContentFiles();
    }
    catch (e) {
        console.error(e);
    }
}

$("save-btn").addEventListener("click",ModifyData)

$("modifyBtn").addEventListener("click", showModal);
$("deleteBtn").addEventListener("click", confirmationModal)

document.querySelector(".close").addEventListener("click", function () {
    $("edit-modal").style.display = "none";
});
