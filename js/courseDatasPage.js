// let menu = $("hamburger-menu")

const deadlineModal = $("deadlineModal");
const openModalLink = $("openModal");

const closeButton = document.querySelector(".close-button");

let courseId = getUrlEndpoint();

async function fillDeadlineList() {
    try {
        let [response, result] = await API.getCourseContent(courseId);

        let div = document.querySelector('.deadlineExercises');

        result = result.filter(x => x.deadline != null);

        if (result.length > 0) {
            div.innerHTML = '';
            result.sort((a, b) => b.deadline - a.deadline);
            result.forEach(feladat => {
                div.innerHTML += `<p>${feladat.deadline} - <a href="../feladat.html?id=${feladat.content_id}">${feladat.title}</a></p>`;
            })
        }
        else {
            div.innerHTML = '<p style="color: gray; font-style: italic; font-weight: bold;">Nincsenek határidős feladatai!</p>';
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

closeButton.addEventListener("click", () => {
    deadlineModal.style.display = "none";
    document.body.classList.remove("modal-open");
});

let designData

async function getDesignJson() {
    let response = await fetch("../js/design.json")
    designData = await response.json()
    for (const design of designData) {
        if (design.designId == cardData.design_id) {
            header.style.backgroundImage = `url('${design.courseImage}')`
            header.style.filter = "brightness(50%);"
        }
    }
}

let cardData;
async function getCardsData() {
    try {
        let [response, result] = await API.getCourseData(courseId);

        if (response.ok) {
            cardData = result
            ModifyActualData(cardData)
            viewByRole()
            getDesignJson()
            getCourseUsers(courseId);
        }
        else if (response.status == 401) {
            location.href = '../login.html';
        }
        else if (response.status == 403) {
            location.href = '../kurzusok.html';
        }
        else {
            throw response.status
        }
    } catch (error) {
        console.log(error)
    }
}

function ModifyActualData(cardData) {
    let addButton = document.querySelector(".addIcon")
    let settingsButton = document.querySelector(".settingIcon")
    let deleteUserButton = $("deleteButton")
    let deleteCourseButton = document.querySelector(".deleteCourseIcon")

    let kurzusNev = $("kurzusNev")
    let oktatok = $("oktatok")
    let title = document.querySelector("title")
    let header = $("header")
    let kurzusLeiras = $('kurzusLeiras');
    kurzusNev.innerHTML = cardData.name
    oktatok.innerHTML = cardData.lastname + " " + cardData.firstname
    title.innerHTML = cardData.name
    kurzusLeiras.innerHTML = cardData.description;
    if (cardData.archived == 1) {
        addButton.removeAttribute("href")
        addButton.style.display = "none"
        settingsButton.removeAttribute("href")
        settingsButton.style.display = "none"
        deleteUserButton.classList.add("disabled")
        deleteCourseButton.style.display = "flex"

        const menu = $("menu"); // A hamburger menü div-je
        const warningBanner = create('div', 'archived-warning');
        warningBanner.innerHTML = "Ez a kurzus archivált! A tartalmak elérhetők, de nem módosíthatók.";

        // A menu div UTÁN illesztjük be
        menu.parentNode.insertBefore(warningBanner, menu.nextSibling);
    }
}

async function getCourseContent(courseId) {
    try {
        let [response, contentList] = await API.getCourseContent(courseId);

        let contentNavbar = document.getElementById("contentNavbar")

        if (contentList.length > 0) {
            contentNavbar.style.display = 'block';
            showCourseContent(contentList);
        } else {
            contentNavbar.style.display = 'none';
        }
        
    }
    catch (e) {
        console.error(e);
    }
}

async function getCourseUsers(courseId) {
    try {
        let [response, userList] = await API.getCourseMembers(courseId);

        if (response.ok) {
            showCourseUsers(userList);
        } else {
            throw response.status;
        }
    } catch (e) {
        console.error(e);
    }
}

function showCourseUsers(userList){
    console.log(userList)
}

function viewByRole() {
    let addContentButton = $("addContentButton");
    let settingsButton = $("settingsButton");
    let navbar = $("contentNavbar");
    if (cardData.role == 2 || cardData.role == 3) {
        addContentButton.style.display = "flex";
        settingsButton.style.display = "flex";
        navbar.style.display = "block"
    }
    if(cardData.role == 1 || cardData.role == 2){
        $('leaveButton').style.display = "flex"
    }
}

async function deleteUserFromCourse() {
    let course_id = parseInt(getUrlEndpoint())
    let user_id = document.querySelector('input:checked')?.value
    if (user_id == null) {
        alert("Nincsen kiválasztva személy!");
        return
    }
    try {
        let [response, result] = await API.kickMember(user_id, course_id);
        if (response.ok) {
            if (result.sikeres == true) {
                location.reload()
            }
        } else {
            alert(result.uzenet)
        }
    } catch (error) {
        console.log(error)
    }
}

function showCourseContent(content) {
    let contentList = $('contentList');
    let notPublishedDiv = $("not_published")
    let button = $("publishButton")

    content.forEach(c => {
        let div = create('div', 'ContentTypeDiv', c.task ? 'feladatDiv' : 'tananyagDiv',);
        div.id = c.content_id

        let container = create('a', 'containerdiv')

        let iconDiv = create('div', 'Icon');

        let a = create('a');
        container.href = c.task ? `../feladat.html?id=${c.content_id}` : `../tananyag.html?id=${c.content_id}`;

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
        if (c.published == null) {
            notPublishedDiv.appendChild(div)
            button.style.display = "block"
        } else {
            contentList.appendChild(div)
        }
    });

    $("publishButton").addEventListener("click", function () { PublishContent(document.querySelectorAll(".radioButton")) })
}

async function PublishContent(radios) {
    let selectedValue;
    radios.forEach(radio => {
        if (radio.checked) {
            selectedValue = parseInt(radio.value);
        }
    });
    try {
        let [response, result] = await API.publishContent(selectedValue);
        if (result.sikeres) {
            location.reload()
        }
    } catch (error) {
        console.log(error)
    }
}

async function DeleteCourse() {
    let course_id = parseInt(getUrlEndpoint());
    console.log(course_id)
    try {
        let [response, result] = await API.deleteCourse(course_id)
        if(result.sikeres){
            location.href = '../kurzusok.html';
        }
    } catch (error) {
        console.log(error)
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const link1 = $('link1');
    const link2 = $('link2');
    const content1 = $('contentList');
    const content2 = $('not_published');

    content1.classList.add("active");
    link1.classList.add("active-link");

    link1.addEventListener('click', function (event) {
        event.preventDefault();
        content1.classList.add('active');
        content2.classList.remove('active');

        link1.classList.add('active-link');
        link2.classList.remove('active-link');
    });

    link2.addEventListener('click', function (event) {
        event.preventDefault();
        content2.classList.add('active');
        content1.classList.remove('active');

        link2.classList.add('active-link');
        link1.classList.remove('active-link');
    });
});


$('deleteButton').addEventListener('click', deleteUserFromCourse)

// Kurzus elhagyása
async function leaveCourse() {
    let courseId = parseInt(getUrlEndpoint());
    let [response, result] = await API.leaveCourse(courseId);

    if (response.ok) {
        location.href = '../kurzusok.html';
    }
    else {
        showAlert(result.uzenet);
    }
}

function confirmationModal(messageText,fuggveny) {
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "flex"

    let modal_content = create("div", 'confirmation-modal-content')
    alertDiv.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = messageText

    let yes_button = create("button")
    modal_content.appendChild(yes_button)
    yes_button.innerHTML = "Igen"
    yes_button.addEventListener("click", ()=>{
        fuggveny()
    })

    let no_button = create("button")
    modal_content.appendChild(no_button)
    no_button.innerHTML = "Nem"

    no_button.addEventListener("click", () => {
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
}

function showAlert(uzenet) {
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

    ok_button.addEventListener("click", () => {
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })

    ok_button.focus()
}

$('leaveButton').addEventListener('click',()=>{
    confirmationModal("Biztosan ki szeretne lépni a kurzusból?",leaveCourse)
});

window.addEventListener("load", getCardsData)
window.addEventListener("load", () => {
    let courseId = parseInt(getUrlEndpoint());
    if (isNaN(courseId)) {
        location.href = '../kurzusok.html';
    }
    else {
        let addContentButton = $('addContentButton');
        let settingsButton = $('settingsButton');
        let usersButton = $('openModalUsers')
        addContentButton.href += `?id=${courseId}`;
        settingsButton.href += `?id=${courseId}`;
        usersButton.href += `?id=${courseId}`;

        getCourseContent(courseId);
    }
});

document.getElementById("deleteCourseButton").addEventListener("click", ()=>{
    confirmationModal("Biztosan törölné a kurzust?", DeleteCourse)
})