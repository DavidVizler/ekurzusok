function $(id) {
    return document.getElementById(id);
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

async function listUsers(page, rows, orderby) {
    try {
        let request = await fetch("./api/get-users", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows,
                orderby
            })
        });

        if (request.ok) {
            let users = await request.json();
            if (users.length > 0) {
                let content = "";        
                for (let i = 0; i < users.length; i++) {
                    content += `<tr>
                        <td>${users[i]["user_id"]}</td>
                        <td>${users[i]["email"]}</td>
                        <td>${users[i]["lastname"]}</td>
                        <td>${users[i]["firstname"]}</td>
                        <td>
                            ${users[i]["courses"]} (${users[i]["own_courses"]} saját) <a href="user-info?id=${users[i]["user_id"]}">Több infó</a>
                        </td>
                    </tr>`
                }
                
                $("table-content").innerHTML = content;
            } 

            $("rows").value = rows;
            if (orderby == "default") {
                $("orderby").value = "user_id";
            } else {
                $("orderby").value = orderby;
            }
        } else {
            throw request.status;
        }
    } catch(error) {
        console.log(error);
    }
}

async function listCourses(page, rows, orderby) {
    try {
        let request = await fetch("./api/get-courses", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows,
                orderby
            })
        });

        if (request.ok) {
            let courses = await request.json();
            if (courses.length > 0) {             
                let content = "";
                for (let i = 0; i < courses.length; i++) {
                    content += `<tr>
                        <td>${courses[i]["course_id"]}</td>
                        <td>${courses[i]["name"]}</td>
                        <td>${courses[i]["code"]}</td>
                        <td>${courses[i]["archived"] == 1 ? "Igen" : "Nem"}</td>
                        <td>
                            <a href="user-info?id=${courses[i]["user_id"]}">${courses[i]["lastname"]} ${courses[i]["firstname"]} (#${courses[i]["user_id"]})</a>
                        </td>
                        <td>
                            ${courses[i]["members"]} (${courses[i]["teachers"]} tanár) <a href="course-info?id=${courses[i]["course_id"]}">Több infó</a>
                        </td>
                    </tr>`
                }
                
                $("table-content").innerHTML = content;
            } 

            $("rows").value = rows; 
            if (orderby == "default") {
                $("orderby").value = "course_id";
            } else {
                $("orderby").value = orderby;
            }
        } else {
            throw request.status;
        }
    } catch(error) {
        console.log(error);
    }
}

async function listCourseInfo(page, rows, id, orderby) {
    try {
        let request = await fetch("./api/get-course-info", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows,
                id,
                orderby
            })
        });

        if (request.ok) {
            let courseInfo = await request.json();
            let data = courseInfo.course_data;
            if (courseInfo.course_members.length > 0) {
                let members = courseInfo.course_members;
                let table_content = "";
                let info_content = `<div class="info-modal-container">
                    <div class="info-modal">Kurzus adatai</div>
                    <div class="info-modal">ID: ${data["course_id"]}</div>
                    <div class="info-modal">Név: ${data["name"]}</div>
                    <div class="info-modal">Kód: ${data["code"]}</div>
                    <div class="info-modal">Design ID: ${data["design_id"]}</div>
                    <div class="info-modal">Archivált: ${data["archived"] == 1 ? "Igen" : "Nem"}</div>
                    <div class="info-modal">Leírás: ${data["description"]}</div>
                </div>`;

                $("info").innerHTML = info_content;
                
                members.forEach(member => {     
                    let role;               
                    switch (member["role"]) {
                        case 1:
                            role = "Tanuló";
                            break;
                        case 2:
                            role = "Tanár";
                            break;
                        case 3:
                            role = "Tulajdonos";
                            break;
                    }

                    table_content += `<tr>
                        <td>${member["user_id"]}</td>
                        <td class="monospace">${member["membership_id"]}</td>
                        <td>${member["lastname"]}</td>
                        <td>${member["firstname"]}</td>
                        <td>${member["email"]}</td>
                        <td>${role}</td>
                        <td>
                            <a href="user-info?id=${member["user_id"]}">Több infó</a>
                        </td>
                        <td class='torles'>
                            <form method='POST' action='javascript:;' onsubmit='deleteMember(${member["membership_id"]})'>
                                <input type='submit' value='Eltávolítás' name='delete_button'>
                            </form>
                        </td>
                    </tr>`
                });
                
                $("table-content").innerHTML = table_content;
            } 

            $("rows").value = rows; 
            if (orderby == "default") {
                $("orderby").value = "lastname";
            } else {
                $("orderby").value = orderby;
            }
        } else {
            throw request.status;
        }
    } catch(error) {
        console.log(error);
    }
}

async function listUserInfo(page, rows, id, orderby) {
    try {
        let request = await fetch("./api/get-user-info", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows,
                id,
                orderby
            })
        });

        if (request.ok) {
            let userInfo = await request.json();
            let data = userInfo.user_data;
            if (userInfo.user_courses.length > 0) {
                let courses = userInfo.user_courses;
                let table_content = "";
                let info_content = `<div class="info-modal-container">
                    <div class="info-modal">Felhasználó adatai</div>
                    <div class="info-modal">ID: ${data["user_id"]}</div>
                    <div class="info-modal">Email: ${data["email"]}</div>
                    <div class="info-modal">Vezetéknév: ${data["lastname"]}</div>
                    <div class="info-modal">Keresztnév: ${data["firstname"]}</div>
                </div>`;

                $("info").innerHTML = info_content;
                
                courses.forEach(course => {     
                    let role;               
                    switch (course["role"]) {
                        case 1:
                            role = "Tanuló";
                            break;
                        case 2:
                            role = "Tanár";
                            break;
                        case 3:
                            role = "Tulajdonos";
                            break;
                    }

                    table_content += `<tr>
                        <td>${course["course_id"]}</td>
                        <td class="monospace">${course["membership_id"]}</td>
                        <td>${course["name"]}</td>
                        <td>${course["code"]}</td>
                        <td>${course["archived"] ? "Igen" : "Nem"}</td>
                        <td>${role}</td>
                        <td>
                            <a href="user-info?id=${course["course_id"]}">Több infó</a>
                        </td>
                        <td class='torles'>
                            <form method='POST' action='javascript:;' onsubmit='deleteMember(${course["membership_id"]})'>
                                <input type='submit' value='Eltávolítás' name='delete_button'>
                            </form>
                        </td>
                    </tr>`
                });
                
                $("table-content").innerHTML = table_content;
            } 

            $("rows").value = rows; 
            if (orderby == "default") {
                $("orderby").value = "name";
            } else {
                $("orderby").value = orderby;
            }
        } else {
            throw request.status;
        }
    } catch(error) {
        console.log(error);
    }
}

async function deleteMember(id) {
    try {
        let request = await fetch("./api/remove-member", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                "membership_id": id
            })
        });

        if (request.ok) {
            let result = await request.json();
            alert(result.uzenet);
            if (result.sikeres) {
                location.reload();
            }
        } else {
            throw(request.status);
        }
    } catch (error) {
        console.log(error);
    }
}

function prevPage() {
    $("page").value -= 1;
    $("page-form").submit();
}

function nextPage() {
    let next = parseInt($("page").value) + 1;
    $("page").value = next;
    $("page-form").submit();
}

function manualPageTurn(event) {
    if (event.key == "Enter") {
        $("page-form").submit();
    }
}

async function loginAdmin() {
    let username = $("username").value;
    let password = $("password").value;
    try {
        let response = await fetch("./api/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                username,
                password
            })
        });

        if (response.ok) {
            let result = await response.json();
            if (result.sikeres) {
                window.location.href = './';
            }
            else {
                alert(result.uzenet);
            }
        } else {
            throw(response.status);
        }
    } catch (error) {
        console.log(error);
    }
}

window.addEventListener("load", () => {
    if ($("admin-login-form") != null) {
        $("admin-login-form").addEventListener("submit", (e) => {
            loginAdmin();
            e.preventDefault();
        });
    }
})