import design from './desgin.json' with {type : "json"}

document.getElementById("closeButton").addEventListener('click',()=>{
    window.open("./kurzusok.html","_self")
})

function designOptionLoad(){
    let select = document.getElementById("DesignSelect")
    design.forEach(elem => {
        let option = document.createElement("option");
        option.value = elem.designId
        option.text = elem.designName
        select.appendChild(option)
    });
}

function previewLoad(){
    let selectDesign = document.querySelector("#DesignSelect")
    let designName = selectDesign.options[selectDesign.selectedIndex].text;
    let preview = document.getElementById("preView")
    let previewImage = document.getElementById("preViewImage")
    previewImage.innerHTML = ""
    design.forEach(elem => {
        if(elem.designName == designName){
            preview.style.display = "flex"
            previewImage.innerHTML += `<img src="${elem.image}" alt="Kurzus téma képe">`
        }
    });
}

document.getElementById("DesignSelect").addEventListener('click', previewLoad)
window.addEventListener('load', designOptionLoad)