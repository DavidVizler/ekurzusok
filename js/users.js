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
            await getCardsData()
            showCourseUsers(userslist)
            viewByRole()
        } else {
            throw valasz.status
        }

    } catch (e) {
        console.error(e);
    }
}

function viewByRole() {
    let deleteBtns = document.querySelectorAll('.deleteBtn');
    let modRoleBtns = document.querySelectorAll('.modeRoleBtn');

    if (deleteBtns.length > 0) {
        deleteBtns.forEach(btn => {
            btn.style.display = "none";
        });

        if (userData.role == 3) {
            deleteBtns.forEach(btn => {
                btn.style.display = "flex";
            });
        }
    }

    if (modRoleBtns.length > 0) {
        modRoleBtns.forEach(btn => {
            btn.style.display = "none"
        })
        modRoleBtns.forEach(btn => {
            if (userData.role == 3) {
                btn.style.display = "flex";
            }
        });
    }
}

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

    userslist.forEach(user => {
        const li = document.createElement('li');
        li.innerHTML = `${user.lastname} ${user.firstname}`;
        li.value = user.user_id

        const svgContainer = document.createElement('div');
        svgContainer.classList.add('svg-container');

        let deleteBtn = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        deleteBtn.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        deleteBtn.setAttribute('fill', 'none');
        deleteBtn.setAttribute('viewBox', '0 0 24 24');
        deleteBtn.setAttribute('stroke-width', '1');
        deleteBtn.setAttribute('stroke', 'currentColor');
        deleteBtn.classList.add('size-6');
        deleteBtn.classList.add("deleteBtn")

        let path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');

        path.setAttribute('d','M6 18 18 6M6 6l12 12');
        deleteBtn.appendChild(path)

        let modRoleBtn = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        modRoleBtn.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        modRoleBtn.setAttribute('fill', 'none');
        modRoleBtn.setAttribute('viewBox', '0 0 24 24');
        modRoleBtn.setAttribute('stroke-width', '1');
        modRoleBtn.setAttribute('stroke', 'currentColor');
        modRoleBtn.classList.add('size-6');
        modRoleBtn.classList.add("modeRoleBtn")

        let path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path2.setAttribute('stroke-linecap', 'round');
        path2.setAttribute('stroke-linejoin', 'round');
        path2.setAttribute('d','m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10');
        modRoleBtn.appendChild(path2)

        if (user.role == 3) {
            tanarUl.prepend(li);
        }

        if(user.role == 2){
            modRoleBtn.style.marginRight = "10px"
            svgContainer.appendChild(modRoleBtn)
            li.appendChild(svgContainer)
            if (tanarUl.lastChild) {
                tanarUl.lastChild.insertAdjacentElement("afterend", li);
            } else {
                tanarUl.appendChild(li);
            }
        }
        
        if(user.role == 1) {
            svgContainer.appendChild(modRoleBtn)
            svgContainer.appendChild(deleteBtn)
            li.appendChild(svgContainer)
            ul.appendChild(li);
        }

        if(userData.role == 3){
            modRoleBtn.addEventListener("click", function(){modifyRole(li.value)})
            deleteBtn.addEventListener("click",function(){deleteUserFromCourse(li.value)})
        }
    });

    if (tanarUl.children.length > 0) {
        ownerp.appendChild(tanarUl);
    }

    if (ul.children.length > 0) {
        scrollDiv.appendChild(ul);
    }
}

async function deleteUserFromCourse(user_id) {
    let params = new URLSearchParams(document.location.search);
    let course_id = params.get("id")
    
    if (user_id == null) {
        alert("Nincsen kiválasztva személy!");
        return
    }
    try {
        let data = {
            "user_id": parseInt(user_id),
            "course_id": parseInt(course_id)
        }
        let keres = await fetch('../api/member/remove', {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            }, body: JSON.stringify(data)
        })
        if (keres.ok) {
            let response = await keres.json()
            if (response.sikeres == true) {
                setTimeout(()=>{
                    location.reload()
                },500)
            }
        } else {
            let response = await keres.json()
            console.log(response)
        }
    } catch (error) {
        console.log(error)
    }
}

async function modifyRole(user_id) {
    let params = new URLSearchParams(document.location.search);
    let course_id = params.get("id")
    
    if (user_id == null) {
        alert("Nincsen kiválasztva személy!");
        return
    }

    try {
        let data = {
            "user_id": parseInt(user_id),
            "course_id": parseInt(course_id)
        }
        let keres = await fetch('../api/member/teacher', {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            }, body: JSON.stringify(data)
        })
        if (keres.ok) {
            let response = await keres.json()
            if (response.sikeres == true) {
                setTimeout(()=>{
                    location.reload()
                },500)
            }
        } else {
            let response = await keres.json()
            console.log(response)
        }
    } catch (error) {
        console.log(error)
    }
}

window.addEventListener('load', getCourseUsers)