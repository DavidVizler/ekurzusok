let pageLink = $("backtoPage")
pageLink.addEventListener("click", ()=>{
    history.back()
})


async function getDesign() {
    let response = await fetch("../js/design.json")
    let design = await response.json()
    return design
}

async function loadDesign() {
    let designData = await getDesign()
    let designSelect = $("designSelect")
    for (let data of designData) {
        let option = create("option")
        option.value = data.designId
        option.textContent = data.designName
        designSelect.appendChild(option)
    }
}

async function loadPreview() {
    let designData = await getDesign()
    let selectedDesign = $("designSelect").value
    let previewDiv = $("previewDiv")

    for(let data of designData){
        if(data.designId == selectedDesign){
            previewDiv.style.display = "flex"
            previewDiv.style.backgroundImage = `url(${data.courseImage})`
        }
    }
}

async function toggleArrow(){
    let arrow = document.querySelector('.arrow')
    let previewDiv = $("previewDiv")

    arrow.classList.toggle('flipped')

    if (previewDiv.style.display === "none" || previewDiv.style.display === "") {
        previewDiv.style.display = "flex";
    } else {
        previewDiv.style.display = "none";
    }
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
}

function confirmationModal(){
    let modal = create("div", 'modal')
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "block"
    alertDiv.appendChild(modal)

    let modal_content = create("div", 'modal-content')
    modal.appendChild(modal_content)

    let message = create('p');
    modal_content.appendChild(message)
    message.innerHTML = "Ez egy nem visszavonható művelet! Biztosan szeretné törölni a kurzust?"

    let yes_button = create('button');
    modal_content.appendChild(yes_button)
    yes_button.innerHTML = "Igen"
    yes_button.addEventListener("click",DeleteCourse)

    let no_button = create('button');
    modal_content.appendChild(no_button)
    no_button.innerHTML = "Nem"
    no_button.addEventListener('click',()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
}

async function modifySettings(e) {
    e.preventDefault();
    try {
        let urlParams = getUrlParams();
        if (!urlParams.has('id')) {
            throw new Error("Nincs 'id' paraméter megadva az URL-ben.");
        }

        let courseId = parseInt(urlParams.get('id'));
        
        let name = $('courseName').value;
        let desc = $('courseDescription').value;
        let design = parseInt($('designSelect').value);

        let reqData = {
            id: courseId,
            name,
            desc,
            design
        };

        let response = await fetch('../api/course/modify-data', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(reqData)
        });

        let result = await response.json();

        if (result.sikeres) {
            location.href = './' + courseId;
        }else{
            resultModal(result.uzenet)
        }
    }
    catch (e) {
        console.error(e);
        resultModal("A módosítások elmentése nem sikerült! Kérjük próbálja meg később.");
    }
}

async function DeleteCourse() {
    try {
        let urlParams = getUrlParams();
        if (!urlParams.has('id')) {
            throw new Error("Nincs 'id' paraméter megadva az URL-ben.");
        }

        let courseId = parseInt(urlParams.get('id'));
        let request = await fetch('../api/course/delete',{
            method : 'POST',
            headers : {'Content-Type' : 'application/json'},
            body : JSON.stringify({"id" : courseId})
        })
        let response = await request.json()
        if(response.sikeres){
            resultModal("Sikeres törlés")
            location.href = '../kurzusok.html';
        }
        else{
            resultModal(response.uzenet)
        }
    } catch (error) {
        console.log(error)
    }
}

async function onLoad() {
    try {

        let urlParams = getUrlParams();
        if (!urlParams.has('id') || isNaN(urlParams.get('id'))) {
            location.href = '../kurzusok.html';
        }

        let courseId = parseInt(urlParams.get('id'));

        await loadCurrentValues(courseId);
    }
    catch (e) {
        location.href = '../kurzusok.html';
    }
    loadPreview();
}

async function loadCurrentValues(courseId) {
    try {
        let reqData = {
            course_id: courseId
        }

        let response = await fetch('../api/query/course-data', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(reqData)
        });

        let courseData = await response.json();
        
        $('courseName').value = courseData['name'];
        $('courseDescription').value = courseData['description'];
        $('designSelect').value = courseData['design_id'];
        $('code').value = courseData["code"]
    }
    catch (e) {
        console.error(e);
    }
}

window.addEventListener('load', loadDesign)
$("designSelect").addEventListener("change", loadPreview)
document.querySelector(".openPreviewDiv").addEventListener('click', toggleArrow)
$("deleteCourseButton").addEventListener('click', confirmationModal)
window.addEventListener('load', async () => await onLoad());
$('settingsForm').addEventListener('submit', async (e) => await modifySettings(e));