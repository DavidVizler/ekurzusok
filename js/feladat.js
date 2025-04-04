function currentDateLoad() {
    $("creatingDate").innerHTML = getCurrentTime();
}

window.addEventListener("load", currentDateLoad)

$('fileInput').addEventListener('change', (e) => displaySelectedFiles(e.target.files));

$("uploadFileButton").addEventListener("click",()=>{
    $("fileInput").click()
})

$("backToPreviousPage").addEventListener("click",()=>{
    window.history.go(-1)
})

async function getSubmissionData() {
    let urlParams = getUrlParams();
    let contentId = urlParams.get('id');
    try {
        let [response, result] = await API.getOwnSubmission(contentId);
        if (response.ok) {
            submissionLoad(result);
        } else {
            throw response.status;
        }
    } catch (error) {
        console.log(error);
    }
}

function submissionLoad(submission_data) {
    if (submission_data.submission_exists) {
        getSubmissionFiles(submission_data.submission_id);

        if (submission_data.submitted) {
            $("uploadExerciseButton").disabled = true;
            $("uploadExerciseButton").classList.add("disabledButton")
            $("uploadFileButton").disabled = true;
            $("uploadFileButton").classList.add("disabledButton")
            $("uploadExerciseButton").innerHTML = "Feladat leadva"
        }
    }

}

async function getSubmissionFiles(submission_id) {
    try {
        let [response, result] = await API.getSubmissionFiles(submission_id);
        if (response.ok) {
            submissionFilesLoad(result, submission_id);
        } else {
            throw response.status;
        }
    } catch (error) {
        console.log(error);
    }
}

function submissionFilesLoad(files, submission_id) {
    if (files.length > 0) {
        let ki = $("selectedFiles")
        ki.innerHTML = '';
        for (let file of files) {
            let urlParams = getUrlParams();
            let contentId = urlParams.get('id');

            let a = create('a', 'download');
            a.href = `downloader?file_id=${file.file_id}&attached_to=submission&id=${submission_id}`;
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
            h1.innerHTML = file.name;


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
}

let adatok;
window.addEventListener('load',async()=>{
    let urlParams = getUrlParams();
    let contentId = urlParams.get('id');
    try {
        let [response, result] = await API.getContentData(contentId);
        if(response.ok) {
            adatok = result;
            showContentData()
            GetContentFiles()
            if (adatok.role == 1) {
                getSubmissionData()
                checkDeadline()
            }
        }
        else if (response.status == 401) {
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
    createdDate.innerHTML = adatok.published
    createUser.innerHTML = adatok.lastname + " " + adatok.firstname
    limitDate.innerHTML = "<b>Határidő: </b>" + (adatok.deadline != null ? adatok.deadline : 'Nincsen határidő beállítva')
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
    if(adatok.owned == 0){
        document.querySelector('.uploadOwnExercise').style.display = "flex"
        $('files').removeAttribute("style")
    }
}

function showModal(){
    $("edit-modal").style.display = "flex";
    let title = $("ContentTitle").value = adatok.title
    let max_points =  $("maxPoints").value = adatok.max_points
    $("description-input").value = adatok.description;
    let limitDate = $("deadline-input").value = adatok.deadline;
}

async function ModifyData() {
    let title = $("ContentTitle").value
    let max_points = $("maxPoints").value
    if(max_points == ""){max_points = null}
    let limitDate = convertDate($("deadline-input").value);
    let description = $("description-input").value
    let urlParams = getUrlParams();
    let contentId = urlParams.get('id');
    try {
        let [response, result] = await API.contentModifyData(contentId, title, description, true, max_points, limitDate);
        if(result.sikeres == true){
            setTimeout(function(){
                location.reload()
            }, 100)
        }
        else{
            showAlert(result.uzenet)
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
    let contentId = urlParams.get('id');
    try {
        let [response, result] = await API.deleteContent(contentId);
        if(result.sikeres == false){
            showAlert(result.uzenet)
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
        } else {
            window.location.reload();
        }
    }
    catch (e) {
        console.error(e);
    }
}

async function submitSubmission() {
    let urlParams = getUrlParams();
    let contentId = urlParams.get('id');

    try {
        let [response, result] = await API.submitSubmission(contentId);
        if(result.sikeres == false){
            showResultModal(result.uzenet)    
        } else {
            window.location.reload();
        }
    } catch (error) {
        console.log(error);
    }
}

async function GetContentFiles() {
    let urlParams = getUrlParams();
    let contentId = urlParams.get('id');
    try {
        let [response, result] = await API.getContentFiles(contentId);
        if(response.ok){
            console.log(result)
            showFiles(result)
        }
    } catch (error) {
        console.log(error)
    }
}

function showFiles(files) {
    let ki = $("contentFilesContainer")
    ki.innerHTML = '';
    for (let file of files) {
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
        h1.innerHTML = file.name;


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

function navigateToSubmissions(){
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    if(tartalomId){
        window.location.href = `submissions.html?id=${tartalomId}`;
    }
}

async function deleteFile(contentId, fileId) {
    try {
        let [response, result] = await API.contentRemoveFile(contentId, fileId);
        GetContentFiles();
    }
    catch (e) {
        console.error(e);
    }
}

function checkDeadline() {
    if (adatok.deadline < getCurrentTime()) {
        $("uploadExerciseButton").disabled = true;
        $("uploadExerciseButton").classList.add("disabledButton")
        $("uploadFileButton").disabled = true;
        $("uploadFileButton").classList.add("disabledButton")
    }
}

$("fileInput").addEventListener('change', submitFiles)
$('uploadExerciseButton').addEventListener('click', submitSubmission)
$('showSubmissions').addEventListener('click', navigateToSubmissions)