function $(id) {
    return document.getElementById(id);
}

// Ezek a függvények kijelölik az aktív linket a navbaron
function changeActiveTab(id) {
    document.getElementsByClassName("active")[0].classList.remove("active");
    $(id).classList.add("active");
}

function listStatistics() {
    changeActiveTab("nav-home");
}

function listUsers() {
    changeActiveTab("nav-users");
}

function listCourses() {
    changeActiveTab("nav-courses");
}

// Felhasználó törlése
async function deleteUser(id, lastname, firstname, email, password) {
    if(confirm(`Biztos benne, hogy ki szeretné törölni ${lastname} ${firstname} nevű, ${email} e-mail című felhasználót?`)) {
        let deleteData = {
            "manage" : "user",
            "action" : "delete-as-admin",
            "id" : id,
            "password" : password
        };
        let deleteRequest = await fetch("../php/data_manager.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(deleteData)
        });
        let result = await deleteRequest.json();
        console.log(result);
        location.reload();
    }
}

// Kurzus törlése
async function deleteCourse(id, coursename, userid, lastname, firstname, password) {
    if(confirm(`Biztos benne, hogy ki szeretné törölni ${lastname} ${firstname} felhasználó "${coursename}" nevű kurzusát?`)) {
        let deleteData = {
            "manage" : "course",
            "action" : "delete-as-admin",
            "id" : id,
            "user_id" : userid,
            "password" : password
        };
        let deleteRequest = await fetch("../php/data_manager.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(deleteData)
        });
        let result = await deleteRequest.json();
        console.log(result);
        location.reload();
    }
}