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
    let ul = $('addedCourses');
    let ul2 = $("archivedCourses")
    let archivedDiv = $("archivedDiv")
    if (ul == null) return;
    ul.innerHTML = '';
    ul2.innerHTML = ""

    courses.forEach(course => {
        let li = create('li');
        li.setAttribute('data-initial', course.name.charAt(0).toUpperCase());
        li.innerText = course.name;
        
        let a = create('a');
        if (window.location.href.includes('/kurzus/')) {
            a.href = `./${course.course_id}`;
        }
        else {
            a.href = `./kurzus/${course.course_id}`;
        }
        
        if(course.archived == 1){
            archivedDiv.style.display = "flex"
            a.appendChild(li)
            ul2.appendChild(a)
        }else{
            a.appendChild(li);
            ul.appendChild(a);
        }
    });
}

const menu = document.getElementById("menu");

const toggleScroll = () => {
    if (menu.classList.contains("active")) {
        document.documentElement.classList.add("no-scroll");
    } else {
        document.documentElement.classList.remove("no-scroll");
    }
};

function toggleSideBar() {
    $('menu').classList.toggle('active');
    toggleScroll()
}


window.addEventListener('load', async () => {
    let courses = await getCoursesAsync();
    fillSideBar(courses);
    $('icon').addEventListener('click', toggleSideBar);
});