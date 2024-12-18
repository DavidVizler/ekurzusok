let pageLink = document.getElementById("backtoPage")
pageLink.addEventListener("click", ()=>{
    history.back()
})


async function getDesign() {
    let response = await fetch("../js/desgin.json")
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

window.addEventListener('load', loadDesign)
document.getElementById("designSelect").addEventListener("change", loadPreview)
document.querySelector(".openPreviewDiv").addEventListener('click', toggleArrow)