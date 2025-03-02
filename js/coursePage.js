function openPopUp() {
   $("popup").style.display = "flex";
}

$("plusIcon").addEventListener('click',openPopUp)

async function openCalendarPopUp(){
   $("popupCalendar").style.display = "flex";
   
   let div = $('toDoExercises');

   try {
      let deadlineTasks =  await fetch("./api/query/deadline-tasks");

      let tasks = await deadlineTasks.json();
   
      if (tasks.length > 0) {
         div.innerHTML = '';
         tasks = tasks.map(x => {
             return {
                 ...x,
                 deadline: new Date(x.deadline)
             };
         });
         tasks.sort((a, b) => b.deadline - a.deadline);
         tasks.forEach(feladat => {
             div.innerHTML += `<p>${feladat.deadline.toLocaleString()} - <a href="./feladat.html?id=${feladat.content_id}">${feladat.title}</a></p>`;
         })
      }
      else {
         div.innerHTML = '<p style="color: gray; font-style: italic; font-weight: bold;">Egyelőre nincsenek határidős feladatai!</p>';
      }
   }
   catch (e) {
      console.error(e);
   }
}

$("calendarIcon").addEventListener("click", openCalendarPopUp);

function closePopup() {
   $("popup").style.display = "none";
   $("popupCalendar").style.display = "none";
}

$("close-btn").addEventListener("click",closePopup)
$("calendarCloseButton").addEventListener("click",closePopup)

$("userIcon").addEventListener("click", function() {
   const menu = $("dropdownMenu");
   menu.style.display = menu.style.display === "block" ? "none" : "block";
});

// Menü elrejtése, ha valahol máshol kattintanak
document.addEventListener("click", function(event) {
   const menu = $("dropdownMenu");
   const icon = $("userIcon");
   if (!icon.contains(event.target)) {
       menu.style.display = "none";
   }
});

$("selectAddCourse").addEventListener('change',()=>{
   let selectedValue = document.querySelector("option:checked").value
   let codeForm = $("codeForm")
   if(selectedValue == "addCourseCode"){
      codeForm.style.display = "block"
   }else{
      codeForm.style.display = "none"
   }
   if(selectedValue == "createCourse"){
      window.open("./kurzusAdd.html", "_self")
   }
})

let cardData
let design

// Fetch importálás, hogy Firefoxban is megjelenjen a design
async function getDesignJson() {
   let response = await fetch("./js/design.json")
   design = await response.json()
}

async function getCardsData() {
   try {
      let eredmeny = await fetch("./api/query/user-courses");
        if(eredmeny.ok){
            cardData = await eredmeny.json()
            GenerateCards()
        }
        else{
            throw eredmeny.status
        }
   } catch (error) {
      console.log(error)
      // Ha nincs bejelentkezve a felhasználó, akkor átírányítás a login oldalra
      if (error == 401) {
         window.location.replace("login.html");
      }
   }
}

function GenerateCards(){
   let cardsContainer = $("cards-container")
   let rowDiv = create("div", "row")
   for(let i = 0; i < cardData.length; i++){
      let colDiv = create("div", "col")

      let card = create("div", "card")
      
      let link = create("a")
      link.href = "kurzus/" + cardData[i]["course_id"]
      

      let cardHeader = create("div", "card-header")

      let img = create("img", "card-img-top")
      design.forEach(elem => {
         if(elem.designId == cardData[i]["design_id"]){
            img.src = elem.image
         }
      });

      let cardBody = create("div", 'card-body')

      let cardTitle = create("h2", 'card-title')
      cardTitle.textContent = cardData[i]["name"]

      let cardOktatok = create("h4", 'card-teachers')
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

async function logout() {
   let request = await fetch("./api/user/logout")
   if (request.ok) {
      window.location.replace("login.html");
   }
}

async function joinCourse(e) {
   e.preventDefault();
   let code = $('codeInput').value;

   try {
      let reqData = {
         code
      };

      let response = await fetch('./api/member/add', {
         method: 'POST',
         headers: {
            "Content-Type": 'application/json'
         },
         body: JSON.stringify(reqData)
      });

      let result = await response.json();

      if (response.status == 201) {
         await new Promise(() => location.reload());
      }
      else /*if (response.status == 200)*/ {
         alert(result.uzenet)
      }
      
   } catch (error) {
      console.error(error);
   }
}

window.addEventListener("load", getDesignJson);
window.addEventListener("load",getCardsData);
$("logoutButton").addEventListener("click", logout);
$('courseJoinForm').addEventListener('submit', async (e) => await joinCourse(e));