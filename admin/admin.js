function $(id) {
    return document.getElementById(id);
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
                users.forEach(user => {
                    content += `<tr>
                        <td>${user["user_id"]}</td>
                        <td>${user["email"]}</td>
                        <td>${user["lastname"]}</td>
                        <td>${user["firstname"]}</td>
                        <td>
                            ${user["courses"]} (${user["own_courses"]} saját) <a href="user-info?id=${user["user_id"]}&rows=${rows}">Több infó</a>
                        </td>
                        <td>
                            <div class='actions'>
                                <a href='modify-user-data?id=${user["user_id"]}'><button class='modify'>Adatmódosítás</button></a>
                                <button class='delete' onclick='deleteUser(${user["user_id"]})'>Eltávolítás</button>
                            </div>
                        </td>
                    </tr>`
                });
                
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
                courses.forEach(course => {
                    content += `<tr>
                        <td>${course["course_id"]}</td>
                        <td>${course["name"]}</td>
                        <td>${course["code"]}</td>
                        <td>${course["archived"] == 1 ? "Igen" : "Nem"}</td>
                        <td>
                            <a href="user-info?id=${course["user_id"]}&rows=${rows}">${course["lastname"]} ${course["firstname"]} (#${course["user_id"]})</a>
                        </td>
                        <td>
                            ${course["members"]} (${course["teachers"]} tanár) <a href="course-info?id=${course["course_id"]}&rows=${rows}">Több infó</a>
                        </td>
                        <td class='action'>
                            <button class='delete' onclick='deleteCourse(${course["course_id"]})'>Eltávolítás</button>
                        </td>
                    </tr>`
                });
                
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
                            <a href="user-info?id=${member["user_id"]}&rows=${rows}">Több infó</a>
                        </td>
                        <td class='action'>
                            <button class='delete' onclick='deleteMember(${member["membership_id"]})' ${role == "Tulajdonos" ? "hidden" : ""}>Kirúgás</button>
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
                    <button id='modify-modal' onclick='window.location.href = "./modify-user-data?id=${id}"'>Adatmódosítás</button>
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
                            <a href="course-info?id=${course["course_id"]}&rows=${rows}">Több infó</a>
                        </td>
                        <td class='action'>
                            <button class='delete' onclick='deleteMember(${course["membership_id"]})' ${role == "Tulajdonos" ? "hidden" : ""}>Kirúgás</button>
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

async function deleteUser(id) {
    if (confirm("Biztosan evltávolítja a felhasználót és az összes általa létrehozott kurzust, tartalmat és leadott feladatot?")) {
        try {
            let request = await fetch("./api/delete-user", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    "user_id": id
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
}

async function deleteCourse(id) {
    if (confirm("Biztosan evltávolítja a kurzust és az összes ide feltöltött tartalmat és leadott feladatot?")) {
        try {
            let request = await fetch("./api/delete-course", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    "course_id": id
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
}

async function deleteMember(id) {
    if (confirm("Biztosan evltávolítja a felhasználót a kurzusból?")) {
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

async function modifyUserData() {
    let user_id = $("user_id").value;
    let email = $("email").value;
    let lastname = $("lastname").value;
    let firstname = $("firstname").value;

    try {
        let request = await fetch("./api/modify-user-data", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                user_id,
                email,
                lastname,
                firstname
            })
        });
        if (request.ok) {
            let response = await request.json();
            window.location.href = "./user-info?id=" + user_id;
        } else {
            throw request.status;
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

    if ($("modify-form") != null) {
        $("modify-form").addEventListener("submit", (e) => {
            modifyUserData();
            e.preventDefault();
        })
    }
})