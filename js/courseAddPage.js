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
    let kurzusNev = document.getElementById("KurzusNev")
    let oktatoNeve = document.getElementById("OktatoNeve")
    preview.innerHTML = ""
    design.forEach(elem => {
        if(elem.designName == designName){
            preview.style.display = "flex"
            preview.style.justifyContent = "center"
            preview.style.alignItems = "center"
            let card = document.createElement("div")
            card.classList.add("card")

            let cardHeader = document.createElement("div")
            cardHeader.classList.add("card-header")

            let img = document.createElement("img")
            img.src = elem.image
            img.alt = "Kurzus témája"
            img.classList.add("card-img-top")

            let cardBody = document.createElement("div")
            cardBody.classList.add("card-body")

            let cardTitle = document.createElement("h5")
            cardTitle.textContent = kurzusNev.value
            cardTitle.classList.add("card-title")

            let cardOktatok = document.createElement("h6")
            cardOktatok.classList.add("card-teachers")
            cardOktatok.textContent = oktatoNeve.value

            cardBody.appendChild(cardTitle)
            cardBody.appendChild(cardOktatok)
            cardHeader.appendChild(img)
            card.appendChild(cardHeader)
            card.appendChild(cardBody)
            preview.appendChild(card)
        }
    });
}

document.getElementById("DesignSelect").addEventListener('change', previewLoad)
window.addEventListener('load', designOptionLoad)