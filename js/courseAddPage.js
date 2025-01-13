// import design from './design.json' with {type : "json"}

let design

// Fetch importálás, hogy Firefoxban is megjelenjen a design
async function getDesignJson() {
    let response = await fetch("./js/design.json")
    design = await response.json()
    designOptionLoad()
}

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

            cardBody.appendChild(cardTitle)
            cardHeader.appendChild(img)
            card.appendChild(cardHeader)
            card.appendChild(cardBody)
            preview.appendChild(card)
        }
    });
}

async function sendNewCourseData() {
    let name = document.getElementById("KurzusNev").value
    let desc = document.getElementById("Leiras").value
    let courseDesgin = document.getElementById("DesignSelect").value

    let newCourseData = {
        "manage" : "course",
        "action" : "create",
        "name" : name,
        "desc" : desc,
        "design" : courseDesgin
    }

    let request = fetch("./php/data_manager.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"

        },
        body: JSON.stringify(newCourseData)
    })
}

window.addEventListener('load', getDesignJson)
window.addEventListener('load', () => {
    document.getElementById('newCourseForm').addEventListener('submit', (e) => {
        sendNewCourseData()
        e.preventDefault()
    });
})

document.getElementById("DesignSelect").addEventListener('change', previewLoad)

document.getElementById("closeButton").addEventListener('click',()=>{
    window.open("./kurzusok.html","_self")
})
