let menu = document.getElementById("hamburger-menu")

function clickHandler(){
    menu.classList.toggle("active")
}
menu.addEventListener("click", clickHandler)

let url = window.location.pathname.split("/").pop()
let cardData;
async function getCardsData() {
   try {
      let eredmeny = await fetch("../php/courseCard_manager.php");
        if(eredmeny.ok){
            cardData = await eredmeny.json()
        }
        else{
            throw eredmeny.status
        }
   } catch (error) {
      console.log(error)
   }
}

let kurzusNev = document.getElementById("kurzusNev")

window.addEventListener("load",getCardsData)