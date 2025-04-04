let contentType = $("typeContent")
contentType.addEventListener("click", ()=>{
    let selectElement = document.querySelector('select');
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let type = selectedOption.value;
    if(type == "feladat"){
        $("feladatDiv").style.display = "flex"
        $("feladatDiv").style.flexDirection = "column"
        $("tananyagDiv").style.display = "none"
    }
    if(type == "tananyag"){
        $("tananyagDiv").style.display = "flex"
        $("tananyagDiv").style.flexDirection = "column"
        $("feladatDiv").style.display = "none"
    }
    else{
        $("tananyagDiv").style.display = "none"
    }
})

let pageLink = $("backtoPage").addEventListener("click", ()=>{
    history.back()
})

async function contentPublish(reqData) {
    try {
        let urlParams = getUrlParams();
        if (!urlParams.has('id')) {
            alert('Hiba!');
            return;
        }

        let courseId = parseInt(urlParams.get('id'));

        reqData.append("course_id", courseId);

        let response = await fetch('../api/content/create', {
            method: 'POST',
            body: reqData,
            redirect: "follow"
        });

        // TODO
        let result = await response.json();
        if(result.sikeres == false){
            resultModal(result.uzenet)
        }

        console.log(result);
        if (response.ok) {
            window.location.href = "./" + courseId
        }
    }
    catch (e) {
        console.error(e);
        alert("Hiba történt a kérés feldolgozás közben. Kérjük próbálja meg újra később!");
    }
}

// Feladat
async function onNewTask(e) {
    e.preventDefault();
    let title = $('fcim').value;
    let desc = $('fleiras').value;
    let due = convertDate($('fhatarido').value);
    let points;
    if ($('fpont').value == "") {
        points = null;
    } else {
        points = parseInt($('fpont').value);
    }
    let filesInput = $('ffile');

    if (title == '') {
        alert("A cím megadása kötelező!");
        return;
    }

    let reqData = new FormData();

    reqData.append("title", title);
    reqData.append("desc", desc);
    reqData.append("task", true);
    reqData.append("deadline", due);
    reqData.append("maxpoint", points);
    
    for (const file of filesInput.files) {
        reqData.append('files[]', file);
    }

    await contentPublish(reqData);
}

function resultModal(result){
    let modal = create("div", 'modal')
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "block"
    alertDiv.appendChild(modal)

    let modal_content = create("div", 'modal-content')
    modal.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = result

    let ok_button = create("button")
    modal_content.appendChild(ok_button)
    ok_button.innerHTML = "OK"

    ok_button.addEventListener("click",()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
    ok_button.focus()
}

// Tananyag
async function onNewMaterial(e) {
    e.preventDefault();
    let title = $('tcim').value;
    let desc = $('tleiras').value;
    let filesInput = $('tfile');

    if (title == '') {
        alert("A cím megadása kötelező!");
        return;
    }

    let reqData = new FormData();

    reqData.append("title", title);
    reqData.append("desc", desc);
    reqData.append("task", false);

    for (const file of filesInput.files) {
        reqData.append('files[]', file);
    }

    await contentPublish(reqData);
}

$('ujFeladatForm').addEventListener('submit', async (e) => onNewTask(e));
$('ujTananyagForm').addEventListener('submit', async (e) => onNewMaterial(e));
window.addEventListener('load', () => {
    let urlParams = getUrlParams();
    if (!urlParams.has('id')) {
        location.href = '../kurzusok.html';
    }
})