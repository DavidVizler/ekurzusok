// let menu = $("hamburger-menu")

const deadlineModal = $("deadlineModal");
const openModalLink = $("openModal");
const openModalUsersLink = $("openModalUsers");
const closeButton = document.querySelector(".close-button");
const closeButtonUsers = document.querySelector(".close-buttonUsers");
const usersModal = $("usersModal");

let courseId = getUrlEndpoint();

async function fillDeadlineList() {
    try {
        let response = await fetch("../api/query/course-content", {
            method : 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ course_id: parseInt(courseId) })
        });

        let div = document.querySelector('.deadlineExercises');
        
        let result = await response.json();
        result = result.filter(x => x.deadline != null);

        if (result.length > 0) {
            div.innerHTML = '';
            result = result.map(x => {
                return {
                    ...x,
                    deadline: convertDate(x.deadline)
                };
            });
            // let now = new Date();
            // result = result.filter(x => x.deadline > now);
            result.sort((a, b) => b.deadline - a.deadline);
            result.forEach(feladat => {
                div.innerHTML += `<p>${feladat.deadline.toLocaleString()} - <a href="../feladat.html?id=${feladat.content_id}">${feladat.title}</a></p>`;
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

let designData 

async function getDesignJson() {
    let response = await fetch("../js/design.json")
    designData = await response.json()
 }

let cardData;
async function getCardsData() {
    try {
        let eredmeny = await fetch("../api/query/course-data",{
            method : 'POST',
            headers: {
                'Content-Type': 'application/json',
            },body:JSON.stringify({course_id:parseInt(courseId)})
        });
        if(eredmeny.ok){
            cardData = await eredmeny.json()
            ModifyActualData(cardData)
        }
        else if (eredmeny.status == 403 || eredmeny.status == 401) {
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
    let addButton = document.querySelector(".addIcon")
    let settingsButton = document.querySelector(".settingIcon")
    let deleteUserButton = $("deleteButton")

    let kurzusNev = $("kurzusNev")
    let oktatok = $("oktatok")
    let title = document.querySelector("title")
    let header = $("header")
    let kurzusLeiras = $('kurzusLeiras');
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
   if(cardData.archived == 1){
        addButton.removeAttribute("href")
        addButton.classList.add("disabled")
        settingsButton.removeAttribute("href")
        settingsButton.classList.add("disabled")
        deleteUserButton.classList.add("disabled")

        const menu = $("menu"); // A hamburger menü div-je
        const warningBanner = create('div', 'archived-warning');
        warningBanner.innerHTML = "Ez a kurzus archivált! A tartalmak elérhetők, de nem módosíthatók.";

        // A menu div UTÁN illesztjük be
        menu.parentNode.insertBefore(warningBanner, menu.nextSibling);
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
    }
}

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
        if(valasz.ok){
            let userslist = await valasz.json();
            showCourseUsers(userslist)
            viewByRole(userslist)
        }else{
            throw valasz.status
        }
        
    } catch (e) {
        console.error(e);
    }
}

function viewByRole() {
    let addContentButton = $("addContentButton");
    let settingsButton = $("settingsButton");
    let navbar = $("contentNavbar");
    if (cardData.role == 1) {
        addContentButton.style.display = "none";
        settingsButton.style.display = "none";
        navbar.style.display = "none"
    }
}

async function showCourseUsers(userslist) {
    let usersDiv = document.querySelector('.courseUsers')
    let ownerp = create('p')
    let deleteButton = $('deleteButton')
    let tags = create('p')
    tags.textContent = "Tagok:"
    usersDiv.appendChild(ownerp)
    usersDiv.appendChild(tags)
    let scrollDiv = create("div");
    scrollDiv.id = "scrollDiv";
    let ul = create("ul")
    for(let i = 0; i < userslist.length; i++){
        if(userslist[i].role != 1){
            ownerp.textContent = "Oktató: " + userslist[i].lastname + " " + userslist[i].firstname
        }
        if(cardData.role == 3){
            let userRadio = create('input')
            userRadio.type = "radio"
            userRadio.name = "radioButtons"
            userRadio.value = userslist[i].user_id
            userRadio.style.marginBottom = "10px"
            let name = create("label")
            name.textContent = userslist[i].lastname + " " + userslist[i].firstname
            let br = create('br')
            scrollDiv.appendChild(userRadio)
            scrollDiv.appendChild(name)
            scrollDiv.appendChild(br)
            deleteButton.style.display = "flex"
        }
        if(userslist[i].role == 1 && cardData.role != 3){
            let li = create('li')
            li.value = userslist[i].felhasznaloId
            li.textContent = userslist[i].lastname + " " + userslist[i].firstname
            ul.appendChild(li)
            scrollDiv.appendChild(ul)
        }
    }
    usersDiv.appendChild(scrollDiv)
}

async function deleteUserFromCourse(){
    let course_id = parseInt(getUrlEndpoint())
    let user_id = document.querySelector('input:checked')?.value
    if (user_id == null) {
        alert("Nincsen kiválasztva személy!");
        return
    }
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
    let contentList = $('contentList');
    let notPublishedDiv = $("not_published")
    let button = $("publishButton")
    let links = $("links")
    let notpublishedLink = $("link2")

    content.forEach(c => {
        let div = create('div', 'ContentTypeDiv', c.task ? 'feladatDiv' : 'tananyagDiv', );
        div.id = c.content_id
        
        let container = create('a','containerdiv')

        let iconDiv = create('div', 'Icon');
        
        let a = create('a');
        container.href =  c.task ? `../feladat.html?id=${c.content_id}` : `../tananyag.html?id=${c.content_id}`;
        
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

        let divContent = create('div', c.task ? 'feladatDivContent' : 'tananyagDivContent');

        let h1 = create('h1');
        h1.innerText = c.title;
        
        svg.appendChild(path);
        a.appendChild(svg);
        iconDiv.appendChild(a);
        container.appendChild(iconDiv);
        divContent.appendChild(h1);

        if (c.published == null) {
            let radioButton = create('input');
            radioButton.type = 'radio';
            radioButton.classList.add("radioButton")
            radioButton.name = 'unpublishedContent[]';
            radioButton.value = c.content_id;
            divContent.appendChild(radioButton);
        }

        container.appendChild(divContent);
        div.appendChild(container)
        if(c.published == null){
            notPublishedDiv.appendChild(div)
            button.style.display = "block"
        }else{
            contentList.appendChild(div)
        }

        if(button.style.display == "none"){
            notpublishedLink.style.display = "none"
            links.style.setProperty('--items', '1');
            links.style.justifyContent = 'center';
        }
        else{
            links.style.setProperty('--items', '2');
        }
    });

    $("publishButton").addEventListener("click",function(){PublishContent(document.querySelectorAll(".radioButton"))})
}

async function PublishContent(radios) {
    let selectedValue;
    radios.forEach(radio => {
        if (radio.checked) {
            selectedValue = parseInt(radio.value);
        }
    });
    try {
        let request = await fetch("../api/content/publish",{
            method : "POST",
            headers : {"Content-Type" : "application/json"},
            body : JSON.stringify({"content_id" : selectedValue})
        })
        let response = await request.json()
        if(response.sikeres){
            location.reload()
        }
    } catch (error) {
        console.log(error)
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const link1 = $('link1');
    const link2 = $('link2');
    const content1 = $('contentList');
    const content2 = $('not_published');

    content1.classList.add("active")
    link1.addEventListener('click', function(event) {
        event.preventDefault();
        content1.classList.add('active');
        content2.classList.remove('active');
    });

    link2.addEventListener('click', function(event) {
        event.preventDefault();
        content2.classList.add('active');
        content1.classList.remove('active');
    });
});

$('deleteButton').addEventListener('click', deleteUserFromCourse)

// Kurzus elhagyása
async function leaveCourse() {
    let courseId = parseInt(getUrlEndpoint());
    let response = await fetch('../api/member/leave', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ course_id: courseId })
    });
    
    let result = await response.json();

    if (response.ok) {
        location.href = '../kurzusok.html';
    }
    else {
        showAlert(result.uzenet);
    }
}

function confirmationModal(){
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "flex"

    let modal_content = create("div", 'confirmation-modal-content')
    alertDiv.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = "Biztosan ki szeretne lépni a kurzusból?"

    let yes_button = create("button")
    modal_content.appendChild(yes_button)
    yes_button.innerHTML = "Igen"
    yes_button.addEventListener("click", leaveCourse)

    let no_button = create("button")
    modal_content.appendChild(no_button)
    no_button.innerHTML = "Nem"

    no_button.addEventListener("click",()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
}

function showAlert(uzenet){
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "flex"

    let modal_content = create("div", 'alert-modal-content')
    alertDiv.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = uzenet

    let ok_button = create("button")
    modal_content.appendChild(ok_button)
    ok_button.innerHTML = "OK"
    ok_button.id = "ok_button"

    ok_button.addEventListener("click",()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
}

$('leaveButton').addEventListener('click',confirmationModal);

window.addEventListener("load",getDesignJson)
window.addEventListener("load",getCardsData)
window.addEventListener("load", () => {
    let courseId = parseInt(getUrlEndpoint());
    if (isNaN(courseId)) {
        location.href = '../kurzusok.html';
    }
    else {
        let addContentButton = $('addContentButton');
        let settingsButton = $('settingsButton');
        addContentButton.href += `?id=${courseId}`;
        settingsButton.href += `?id=${courseId}`;

        getCourseContent(courseId);
        getCourseUsers(courseId);
    }
});