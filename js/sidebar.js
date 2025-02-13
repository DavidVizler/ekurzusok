async function logout() {
    let url = window.location.href.includes('/kurzus/') ? '../api/user/logout' : './api/user/logout';
    let request = await fetch(url);
    if (request.ok) {
       window.location.replace("login.html");
    }
 }

async function getCoursesAsync() {
    try {
        let url = window.location.href.includes('/kurzus/') ? '../api/query/user-courses' : './api/query/user-courses';
        let response = await fetch(url);
        if(response.ok){
            return await response.json();
        }
        else{
            throw response.status;
        }
    }
    catch (e) {
        console.error(e);
    }
}

function fillSideBar(courses) {
    let ul = document.getElementById('addedCourses');
    if (ul == null) return;
    ul.innerHTML = '';

    courses.forEach(course => {
        let li = document.createElement('li');
        li.setAttribute('data-initial', course.name.charAt(0).toUpperCase());
        li.innerText = course.name;

        let a = document.createElement('a');
        if (window.location.href.includes('/kurzus/')) {
            a.href = `./kurzus/${course.course_id}`;
        }
        else {
            a.href = `./kurzus/${course.course_id}`;
        }

        a.appendChild(li);
        ul.appendChild(a);
    });
}

function toggleSideBar() {
    document.getElementById('menu').classList.toggle('active');
}


window.addEventListener('load', async () => {
    let courses = await getCoursesAsync();
    fillSideBar(courses);
    document.getElementById('logoutButton').addEventListener('click', async () => await logout());
    document.getElementById('icon').addEventListener('click', toggleSideBar);
});