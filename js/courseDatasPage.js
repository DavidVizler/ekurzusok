let menu = document.getElementById("hamburger-menu")

//import designData from './desgin.json' with {type : "json"}
let designData 

async function getDesignJson() {
    let response = await fetch("../js/desgin.json")
    designData = await response.json()
 }

function clickHandler(){
    menu.classList.toggle("active")
}
menu.addEventListener("click", clickHandler)

let url = window.location.pathname.split("/").pop()
let cardData;
async function getCardsData() {
    try {
      let eredmeny = await fetch("../php/courseCard_manager.php", {
        headers: {
           "X-Requested-With": "XMLHttpRequest"
        }
    });
        if(eredmeny.ok){
            cardData = await eredmeny.json()
            ModifyActualData()
        }
        else{
            throw eredmeny.status
        }
    } catch (error) {
        console.log(error)
    }
}

function ModifyActualData(){
    let kurzusNev = document.getElementById("kurzusNev")
    let oktatok = document.getElementById("oktatok")
    let title = document.querySelector("title")
    let header = document.getElementById("header")
    for (const data of cardData) {
        if(data.KurzusID == url){
            kurzusNev.innerHTML = data.KurzusNev
            oktatok.innerHTML = data.Oktatok
            title.innerHTML = data.KurzusNev
            for (const design of designData) {
                if(design.designId == data.Design){
                    header.style.backgroundImage = `url('${design.courseImage}')`
                    header.style.filter = "brightness(50%);"
                }
            }
        }
    }
}

window.addEventListener("load",getDesignJson)
window.addEventListener("load",getCardsData)