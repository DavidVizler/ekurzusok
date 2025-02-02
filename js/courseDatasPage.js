let menu = document.getElementById("hamburger-menu")

const deadlineModal = document.getElementById("deadlineModal");
const openModalLink = document.getElementById("openModal");
const openModalUsersLink = document.getElementById("openModalUsers");
const closeButton = document.querySelector(".close-button");
const closeButtonUsers = document.querySelector(".close-buttonUsers");
const usersModal = document.getElementById("usersModal");

let courseId = window.location.pathname.split("/").pop();

async function fillDeadlineList() {
    try {
        let response = await fetch("../api/query/course-content", {
            method : 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ course_id: courseId })
        });

        let div = document.querySelector('.deadlineExercises');
        
        let result = await response.json();
        if (result.length > 0) {
            div.innerHTML = '';
            /// Nincs még határidő tulajdonság a megérkezett adatoknál
            // result.filter(x => x.deadline != null);
            // result.sort((a, b) => b.deadline - a.deadline);
            // result.forEach(feladat => {
            //     div.innerHTML += `<p>${feladat.deadline} - ${feladat.title}</p>`;
            // })
        }
        else {
            div.innerHTML += '<p style="color: gray; font-style: italic; font-weight: bold;">Egyelőre nincsenek határidős feladatai!</p>';
        }
    }
    catch (e) {
        console.error(e);
    }
}

openModalLink.addEventListener("click", (event) => {
  event.preventDefault();
  deadlineModal.style.display = "flex";
  document.body.classList.add("modal-open");
  fillDeadlineList();
});

openModalUsersLink.addEventListener("click", (event) =>{
    event.preventDefault();
    usersModal.style.display = "flex";
    document.body.classList.add("modal-open");
});

closeButton.addEventListener("click", () => {
  deadlineModal.style.display = "none";
  document.body.classList.remove("modal-open");
});

closeButtonUsers.addEventListener("click", ()=>{
    usersModal.style.display = "none";
    
})

//import designData from './design.json' with {type : "json"}
let designData 

async function getDesignJson() {
    let response = await fetch("../js/design.json")
    designData = await response.json()
 }

function clickHandler(){
    menu.classList.toggle("active")
}
menu.addEventListener("click", clickHandler)

let cardData;
async function getCardsData() {
    try {
        let eredmeny = await fetch("../api/query/course-data",{
            method : 'POST',
            headers: {
                'Content-Type': 'application/json',
            },body:JSON.stringify({course_id:courseId})
        });
        if(eredmeny.ok){
            cardData = await eredmeny.json()
            ModifyActualData(cardData)
        }
        else if (eredmeny.status == 403) {
            location.href = '../login.html';
        }
        else{
            throw eredmeny.status
        }
    } catch (error) {
        console.log(error)
    }
}

function ModifyActualData(cardData){
    let kurzusNev = document.getElementById("kurzusNev")
    let oktatok = document.getElementById("oktatok")
    let title = document.querySelector("title")
    let header = document.getElementById("header")
    let kurzusLeiras = document.getElementById('kurzusLeiras');
    kurzusNev.innerHTML = cardData.name
    oktatok.innerHTML = cardData.lastname +" " +  cardData.firstname
    title.innerHTML = cardData.name
    kurzusLeiras.innerHTML = cardData.description;
    for (const design of designData) {
        if(design.designId == cardData.design_id){
            header.style.backgroundImage = `url('${design.courseImage}')`
            header.style.filter = "brightness(50%);"
        }
    }
}

async function getCourseContent(courseId) {
    try {
        let reqData = {
            course_id: courseId
        }
        let response = await fetch('../api/query/course-content', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(reqData)
        });

        let contentList = await response.json();

        if (response.ok) {
            showCourseContent(contentList);
        }
    }
    catch (e) {
        console.error(e);
        alert("Nem sikerült az adatokat lekérni a szerverről.");
    }
}

let userslist
async function getCourseUsers(courseid){
    try {
        let data = {
            course_id: courseid
        }
        let valasz = await fetch('../api/query/course-members',{
            method : "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        userslist = await valasz.json();
        if(valasz.ok){
            console.log(userslist);
            showCourseUsers();
        }
    } catch (e) {
        console.error(e);
        alert("Sikertelen adatlekérés!");
    }
}

async function showCourseUsers() {
    let usersDiv = document.querySelector('.courseUsers')
    let ownerp = document.createElement('p')
    let deleteButton = document.getElementById('deleteButton')
    ownerp.textContent = "Oktató: " + userslist[0].lastname + " " + userslist[0].firstname
    console.log(userslist)
    let tags = document.createElement('p')
    tags.textContent = "Tagok:"
    usersDiv.appendChild(ownerp)
    usersDiv.appendChild(tags)
    let scrollDiv = document.createElement("div");
    scrollDiv.id = "scrollDiv";
    let ul = document.createElement("ul")
    for(let i = 1; i < userslist.length; i++){
        if(userslist[i].user_id != null){
            let userRadio = document.createElement('input')
            userRadio.type = "radio"
            userRadio.name = "radioButtons"
            userRadio.value = userslist[i].user_id
            userRadio.style.marginBottom = "10px"
            let name = document.createElement("label")
            name.textContent = userslist[i].lastname + " " + userslist[i].firstname
            let br = document.createElement('br')
            scrollDiv.appendChild(userRadio)
            scrollDiv.appendChild(name)
            scrollDiv.appendChild(br)
            deleteButton.style.display = "flex"
        }else{
            let li = document.createElement('li')
            li.value = userslist[i].felhasznaloId
            li.textContent = userslist[i].lastname + " " + userslist[i].firstname
            ul.appendChild(li)
            scrollDiv.appendChild(ul)
        }
    }
    usersDiv.appendChild(scrollDiv)
}

async function deleteUserFromCourse(){
    let urlParts = location.href.split('/')
    let course_id = parseInt(urlParts[urlParts.length-1])
    let user_id = document.querySelector('input:checked').value
    try {
        let data = {
            "user_id" : parseInt(user_id),
            "course_id" : course_id
        }
        let keres = await fetch('../api/member/remove',{
            method : "POST",
            headers : {
                'Content-Type' : 'application/json'
            }, body : JSON.stringify(data)
        })
        if(keres.ok){
            let response = await keres.json()
            alert("Sikeres felhasználó törlés a kurzusból!")
            if(response.sikeres == true){
                location.reload()
            }
        }else{
            alert(response.uzenet)
        }
    } catch (error) {
        console.log(error)
    }
}

function showCourseContent(content) {
    let contentList = document.getElementById('contentList');
    // contentList.innerHTML = '';

    content.forEach(c => {
        
        let div = document.createElement('div');
        div.classList.add('ContentTypeDiv');
        div.classList.add(c.task ? 'feladatDiv' : 'tananyagDiv');
        
        let iconDiv = document.createElement('div');
        iconDiv.classList.add('Icon');
        
        let a = document.createElement('a');
        a.href =  c.task ? '../feladat.html' :'../tananyag.html';
        
        let svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('stroke-width', '1');
        svg.setAttribute('stroke', 'currentColor');
        svg.classList.add('size-6');
        
        let path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');
        if (c.task) {
            path.setAttribute('d', 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z');
        }
        else {
            path.setAttribute('d', 'M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z');
        }

        let divContent = document.createElement('div');
        divContent.classList.add(c.task ? 'feladatDivContent' : 'tananyagDivContent');

        let h1 = document.createElement('h1');
        h1.innerText = c.title;
        
        svg.appendChild(path);
        a.appendChild(svg);
        iconDiv.appendChild(a);
        div.appendChild(iconDiv);
        divContent.appendChild(h1);
        div.appendChild(divContent);
        contentList.appendChild(div);
    });
}


document.getElementById('deleteButton').addEventListener('click', deleteUserFromCourse)

window.addEventListener("load",getDesignJson)
window.addEventListener("load",getCardsData)
window.addEventListener('load', () => {
    let urlParts = location.href.split('/');
    let courseId = parseInt(urlParts[urlParts.length - 1]);
    if (isNaN(courseId)) {
        location.href = '../kurzusok.html';
    }
    else {
        let addContentButton = document.getElementById('addContentButton');
        let settingsButton = document.getElementById('settingsButton');
        addContentButton.href += `?id=${courseId}`;
        settingsButton.href += `?id=${courseId}`;

        getCourseContent(courseId);
        getCourseUsers(courseId);
    }
});