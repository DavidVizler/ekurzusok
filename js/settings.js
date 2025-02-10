let pageLink = document.getElementById("backtoPage")
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
    let designSelect = document.getElementById("designSelect")
    for (let data of designData) {
        let option = document.createElement("option")
        option.value = data.designId
        option.textContent = data.designName
        designSelect.appendChild(option)
    }
}

async function loadPreview() {
    let designData = await getDesign()
    let selectedDesign = document.getElementById("designSelect").value
    let previewDiv = document.getElementById("previewDiv")

    for(let data of designData){
        if(data.designId == selectedDesign){
            previewDiv.style.display = "flex"
            previewDiv.style.backgroundImage = `url(${data.courseImage})`
        }
    }
}

async function toggleArrow(){
    let arrow = document.querySelector('.arrow')
    let previewDiv = document.getElementById("previewDiv")

    arrow.classList.toggle('flipped')

    if (previewDiv.style.display === "none" || previewDiv.style.display === "") {
        previewDiv.style.display = "flex";
    } else {
        previewDiv.style.display = "none";
    }
}

async function modifySettings(e) {
    e.preventDefault();
    try {
        let urlParams = new URL(location.href).searchParams;
        if (!urlParams.has('id')) {
            throw new Error("Nincs 'id' paraméter megadva az URL-ben.");
        }

        let courseId = parseInt(urlParams.get('id'));
        
        let name = document.getElementById('courseName').value;
        let desc = document.getElementById('courseDescription').value;
        let design = document.getElementById('designSelect').value;

        let reqData = {
            course_id: courseId,
            name,
            desc,
            design
        };

        let response = await fetch('../api/course/modify-data', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(reqData)
        });

        let result = await response.json();

        if (result.sikeres) {
            alert("Módosítások elmentve.");
            location.href = './' + courseId;
        }
        else {
            throw new Error(result);
        }
    }
    catch (e) {
        console.error(e);
        alert("A módosítások elmentése nem sikerült! Kérjük próbálja meg később.");
    }
}

async function onLoad() {
    try {

        let urlParams = new URL(location.href).searchParams;
        if (!urlParams.has('id') || isNaN(urlParams.get('id'))) {
            location.href = '../kurzusok.html';
        }

        let courseId = parseInt(urlParams.get('id'));

        await loadCurrentValues(courseId);
    }
    catch (e) {
        location.href = '../kurzusok.html';
    }
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
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(reqData)
        });

        let courseData = await response.json();
        
        document.getElementById('courseName').value = courseData['name'];
        document.getElementById('courseDescription').value = courseData['description'];
        document.getElementById('designSelect').value = courseData['design_id'];
        document.getElementById('activeCode').value = courseData['code'];
        document.getElementById('archivalt').checked = courseData['archived'] == 1;
    }
    catch (e) {
        console.error(e);
    }
}

window.addEventListener('load', loadDesign)
document.getElementById("designSelect").addEventListener("change", loadPreview)
document.querySelector(".openPreviewDiv").addEventListener('click', toggleArrow)
window.addEventListener('load', async () => await onLoad());
document.getElementById('settingsForm').addEventListener('submit', async (e) => await modifySettings(e));