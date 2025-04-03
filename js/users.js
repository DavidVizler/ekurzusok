let pageLink = document.getElementById("backtoPage").addEventListener("click", () => {
    history.back()
})

let userData;
async function getCardsData() {
    let params = new URLSearchParams(document.location.search);
    let courseId = params.get("id")
    try {
        let eredmeny = await fetch("../api/query/course-data", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }, body: JSON.stringify({ course_id: parseInt(courseId) })
        });
        if (eredmeny.ok) {
            userData = await eredmeny.json()
            console.log(userData)
            viewByRole()
        }
    } catch (error) {
        console.log(error)
    }
}

async function getCourseUsers() {
    let params = new URLSearchParams(document.location.search);
    let courseid = params.get("id")
    try {
        let data = {
            course_id: parseInt(courseid)
        }
        let valasz = await fetch('../api/query/course-members', {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        if (valasz.ok) {
            let userslist = await valasz.json();
            console.log(userslist)
            getCardsData()
            showCourseUsers(userslist)
        } else {
            throw valasz.status
        }

    } catch (e) {
        console.error(e);
    }
}

function viewByRole() {
    let navbar = document.getElementById("contentNavbar");
    if (userData.role == 3) {
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

async function showCourseUsers(userslist) {
    const usersDiv = document.querySelector('.courseUsers');
    const ownerp = document.createElement('p');
    const tags = document.createElement('h1');
    const scrollDiv = document.createElement('div');
    const ul = document.createElement('ul');
    const tanarUl = document.createElement('ul');

    tags.innerHTML = "Tagok:";
    scrollDiv.id = "scrollDiv";

    ownerp.innerHTML = "<h1>Tanárok: </h1>";
    ownerp.id = "ownerp"

    usersDiv.append(ownerp, tags, scrollDiv);

    //<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
  

    userslist.forEach(user => {
        const li = document.createElement('li');
        li.innerHTML = `${user.lastname} ${user.firstname}`;

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

        path.setAttribute('d','M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z');
        svg.appendChild(path)

        li.appendChild(svg)

        if (user.role == 2 || user.role == 3) {
            tanarUl.appendChild(li);
        } else {
            ul.appendChild(li);
        }
    });

    if (tanarUl.children.length > 0) {
        ownerp.appendChild(tanarUl);
    }

    if (ul.children.length > 0) {
        scrollDiv.appendChild(ul);
    }
}

window.addEventListener('load', getCourseUsers)