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
            previewDiv.style.backgroundImage = `url(${data.courseImage})`
        }
    }
}

window.addEventListener('load', loadDesign)
document.getElementById("designSelect").addEventListener("change", loadPreview)