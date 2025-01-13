let menu = document.getElementById("hamburger-menu")

const deadlineModal = document.getElementById("deadlineModal");
const openModalLink = document.getElementById("openModal");
const openModalUsersLink = document.getElementById("openModalUsers");
const closeButton = document.querySelector(".close-button");
const closeButtonUsers = document.querySelector(".close-buttonUsers");
const usersModal = document.getElementById("usersModal");

openModalLink.addEventListener("click", (event) => {
  event.preventDefault();
  deadlineModal.style.display = "flex";
  document.body.classList.add("modal-open");
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

function ModifyActualData(){
    let kurzusNev = document.getElementById("kurzusNev")
    let oktatok = document.getElementById("oktatok")
    let title = document.querySelector("title")
    let header = document.getElementById("header")
    let kurzusLeiras = document.getElementById('kurzusLeiras');
    for (const data of cardData) {
        if(data.KurzusID == url){
            kurzusNev.innerHTML = data.KurzusNev
            oktatok.innerHTML = data.Oktatok
            title.innerHTML = data.KurzusNev
            kurzusLeiras.innerHTML = data.Leiras;
            for (const design of designData) {
                if(design.designId == data.Design){
                    header.style.backgroundImage = `url('${design.courseImage}')`
                    header.style.filter = "brightness(50%);"
                }
            }
        }
    }
}

async function getCourseContent(courseId) {
    try {
        let reqData = {
            getdata: 'course_content',
            course_id: courseId
        }
        let response = await fetch('../php/data_query.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
            getdata: 'course_members',
            course_id: courseid
        }
        let valasz = await fetch('../php/data_query.php',{
            method : "POST",
            headers: {
                'Content-Type': 'application/json',
                //'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        userslist = await valasz.json();
        if(valasz.ok){
            console.log(userslist);
            showCourseUsers(userslist);
        }
    } catch (e) {
        console.error(e);
        alert("Sikertelen adatlekérés!");
    }
}

async function showCourseUsers() {
    let usersDiv = document.querySelector('.courseUsers')
    console.log(usersDiv)
    userslist.forEach(user => {
        let checkbox = document.createElement("input")
        checkbox.type = "checkbox"
        let name = document.createElement("label")
        name.textContent = user.lastname + " " + user.firstname
        let br = document.createElement('br')
        usersDiv.appendChild(checkbox)
        usersDiv.appendChild(name)
        usersDiv.appendChild(br)
    });
}

function showCourseContent(content) {
    let contentList = document.getElementById('contentList');
    contentList.innerHTML = '';

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