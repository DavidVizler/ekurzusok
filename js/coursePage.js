function openPopUp() {
   document.getElementById("popup").style.display = "flex";
}

document.getElementById("plusIcon").addEventListener('click',openPopUp)

async function openCalendarPopUp(){
   document.getElementById("popupCalendar").style.display = "flex";
   
   let div = document.getElementById('toDoExercises');

   try {
      let tasks = [];

      let responseCourses = await fetch("./api/query/user-courses");

      let userCourses = await responseCourses.json();

      let courses = userCourses.map(x => x.course_id);

      for (const courseId of courses) {
         let response = await fetch("./api/query/course-content", {
            method : 'POST',
            headers: {
               'Content-Type': 'application/json',
            },
            body: JSON.stringify({ course_id: courseId })
         });
         
         
         let result = await response.json();
         result = result.filter(x => x.deadline != null);
         tasks.push(...result)
      }

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

document.getElementById("calendarIcon").addEventListener("click", openCalendarPopUp);

function closePopup() {
   document.getElementById("popup").style.display = "none";
   document.getElementById("popupCalendar").style.display = "none";
}

document.getElementById("close-btn").addEventListener("click",closePopup)
document.getElementById("calendarCloseButton").addEventListener("click",closePopup)

document.getElementById("userIcon").addEventListener("click", function() {
   const menu = document.getElementById("dropdownMenu");
   menu.style.display = menu.style.display === "block" ? "none" : "block";
});

// Menü elrejtése, ha valahol máshol kattintanak
document.addEventListener("click", function(event) {
   const menu = document.getElementById("dropdownMenu");
   const icon = document.getElementById("userIcon");
   if (!icon.contains(event.target)) {
       menu.style.display = "none";
   }
});

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
   let cardsContainer = document.getElementById("cards-container")
   let rowDiv = document.createElement("div")
   rowDiv.classList.add("row")
   for(let i = 0; i < cardData.length; i++){
      let colDiv = document.createElement("div")
      colDiv.classList.add("col")

      let card = document.createElement("div")
      card.classList.add("card")
      
      let link = document.createElement("a")
      link.href = "kurzus/" + cardData[i]["course_id"]
      

      let cardHeader = document.createElement("div")
      cardHeader.classList.add("card-header")

      let img = document.createElement("img")
      img.classList.add("card-img-top")
      design.forEach(elem => {
         if(elem.designId == cardData[i]["design_id"]){
            img.src = elem.image
         }
      });

      let cardBody = document.createElement("div")
      cardBody.classList.add("card-body")

      let cardTitle = document.createElement("h2")
      cardTitle.classList.add("card-title")
      cardTitle.textContent = cardData[i]["name"]

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

async function logout() {
   let request = await fetch("./api/user/logout")
   if (request.ok) {
      window.location.replace("login.html");
   }
}

async function joinCourse(e) {
   e.preventDefault();
   let code = document.getElementById('codeInput').value;

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
document.getElementById("logoutButton").addEventListener("click", logout);
document.getElementById('courseJoinForm').addEventListener('submit', async (e) => await joinCourse(e));