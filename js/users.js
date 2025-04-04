let pageLink = document.getElementById("backtoPage").addEventListener("click", ()=>{
    history.back()
})

let cardData;
async function getCardsData() {
    let params = new URLSearchParams(document.location.search);
    let courseId = params.get("id")
    try {
        let [response, result] = await API.getCourseData(courseId);
        if (response.ok) {
            cardData = result
            console.log(cardData)
        }
    } catch (error) {
        console.log(error)
    }
}

async function getCourseUsers() {
    let params = new URLSearchParams(document.location.search);
    let courseid = params.get("id")
    try {
        let [response, userList] = await API.getCourseMembers(courseid);
        if (response.ok) {
            console.log(userList)
            viewByRole()
        } else {
            throw response.status
        }

    } catch (e) {
        console.error(e);
    }
}

function viewByRole() {
    let navbar = document.getElementById("contentNavbar");
    if (cardData.role != 1) {
        navbar.style.display = "block"
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const link1 = document.getElementById('link1');
    const link2 = document.getElementById('link2');
    const content1 = document.getElementById('usersList');
    const content2 = document.getElementById('modifyRole');

    content1.classList.add("active");
    link1.classList.add("active");

    link1.addEventListener('click', function (event) {
        event.preventDefault();

        link1.classList.add('active');
        link2.classList.remove('active');

        content1.classList.add('active');
        content2.classList.remove('active');
    });

    link2.addEventListener('click', function (event) {
        event.preventDefault();

        link2.classList.add('active');
        link1.classList.remove('active');

        content2.classList.add('active');
        content1.classList.remove('active');
    });
});


window.addEventListener('load', getCourseUsers)
window.addEventListener("load", getCardsData)