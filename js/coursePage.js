let menu=document.getElementById("menu")
function openPopUp() {
   document.getElementById("popup").style.display = "flex";
}

document.getElementById("plusIcon").addEventListener('click',openPopUp)

function openCalendarPopUp(){
   document.getElementById("popupCalendar").style.display = "flex";
}

document.getElementById("calendarIcon").addEventListener("click",openCalendarPopUp)

function closePopup() {
   document.getElementById("popup").style.display = "none";
   document.getElementById("popupCalendar").style.display = "none";
}

document.getElementById("close-btn").addEventListener("click",closePopup)
document.getElementById("calendarCloseButton").addEventListener("click",closePopup)

document.getElementById("selectAddCourse").addEventListener('change',()=>{
   let selectedValue = document.querySelector("option:checked").value
   let codeForm = document.getElementById("codeForm")
   if(selectedValue == "addCourseCode"){
      codeForm.style.display = "block"
   }else{
      codeForm.style.display = "none"
   }
   if(selectedValue == "createCourse"){
      window.open("./kurzusAdd.html", "_self")
   }
})

function clickHandler(){
   menu.classList.toggle("active");
}
menu.addEventListener("click",clickHandler);


let cardData;
import design from "./desgin.json" with {type : "json"}
async function getCardsData() {
   try {
      let eredmeny = await fetch("./php/courseCard_manager.php");
        if(eredmeny.ok){
            cardData = await eredmeny.json()
            GenerateCards()
        }
        else{
            throw eredmeny.status
        }
   } catch (error) {
      console.log(error)
   }
}

function GenerateCards(){
   let cardsContainer = document.getElementById("cards-container")
   let rowDiv = document.createElement("div")
   rowDiv.classList.add("row")
   for(let i = 0; i < cardData.length; i++){
      let colDiv = document.createElement("div")
      colDiv.classList.add("col")

      let card = document.createElement("div")
      card.classList.add("card")
      
      let link = document.createElement("a")
      link.href = "kurzus/" + cardData[i]["KurzusID"]
      

      let cardHeader = document.createElement("div")
      cardHeader.classList.add("card-header")

      let img = document.createElement("img")
      img.classList.add("card-img-top")
      design.forEach(elem => {
         if(elem.designId == cardData[i]["Design"]){
            img.src = elem.image
         }
      });

      let cardBody = document.createElement("div")
      cardBody.classList.add("card-body")

      let cardTitle = document.createElement("h2")
      cardTitle.classList.add("card-title")
      cardTitle.textContent = cardData[i]["KurzusNev"]

      let cardOktatok = document.createElement("h4")
      cardOktatok.classList.add("card-teachers")
      cardOktatok.textContent = cardData[i]["Oktatok"]

      cardBody.appendChild(cardTitle)
      cardBody.appendChild(cardOktatok)
      cardHeader.appendChild(img)
      card.appendChild(cardHeader)
      card.appendChild(cardBody)
      link.appendChild(card)
      colDiv.appendChild(link)
      rowDiv.appendChild(colDiv)
   }
   cardsContainer.appendChild(rowDiv)
}

window.addEventListener("load",getCardsData)