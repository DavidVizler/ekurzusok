async function listUsers(page, rows, orderby, field = null, keyword = null) {
    if (field == "user_id") keyword = parseInt(keyword);
    try {
        let request = await fetch("./api/get-users", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows,
                orderby,
                field,
                keyword
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
                                <button class='delete' onclick='confirmModal("user", ${user["user_id"]}, ["${user["lastname"]}", "${user["firstname"]}", "${user["own_courses"]}"])'>Eltávolítás</button>
                            </div>
                        </td>
                    </tr>`
                });
                
                $("table-content").innerHTML = content;
            } 

            $("rows").value = rows;
            $("field").value = field == "" ? "user_id" : field;
            $("keyword").value = keyword == "" ? "" : (field == "user_id" && isNaN(keyword) ? "" : keyword)
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

async function listCourses(page, rows, orderby, field = null, keyword = null) {
    if (field == "user_id") keyword = parseInt(keyword);
    try {
        let request = await fetch("./api/get-courses", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows,
                orderby,
                field,
                keyword
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
                            <button class='delete' onclick='confirmModal("course", ${course["course_id"]}, ["${course["name"]}", "${course["members"]}"])'>Eltávolítás</button>
                        </td>
                    </tr>`
                });
                
                $("table-content").innerHTML = content;
            } 

            $("rows").value = rows; 
            $("field").value = field == "" ? "course_id" : field;
            $("keyword").value = keyword == "" ? "" : (field == "course_id" && isNaN(keyword) ? "" : keyword)
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
                            <button class='delete' onclick='confirmModal("member", ${member["membership_id"]}, ["${member["lastname"]}", "${member["firstname"]}", "${data["name"]}"])' ${role == "Tulajdonos" ? "hidden" : ""}>Eltávolítás a kurzusból</button>
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
                            <button class='delete' onclick='confirmModal("member", ${course["membership_id"]}, ["${data["lastname"]}", "${data["firstname"]}", "${course["name"]}"])' ${role == "Tulajdonos" ? "hidden" : ""}>Eltávolítás a kurzusból</button>
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

function resultModal(result, refresh = false) {
    let modal = create("div", "modal");
    $("modal-container").appendChild(modal);

    let modal_content = create("div", "modal-content");
    modal.appendChild(modal_content);

    let message = create("p");
    modal_content.appendChild(message);
    message.innerHTML = result;

    let ok_button = create("button");
    modal_content.appendChild(ok_button);
    ok_button.innerHTML = "OK";
    ok_button.focus();

    ok_button.addEventListener("click", () => {
        $("modal-container").innerHTML = "";
        if (refresh) location.reload();
    });

    modal.addEventListener("click", (e) => {
        if (e.target.classList[0] == "modal") {
            $("modal-container").innerHTML = "";
        }
    })
}

function confirmModal(target, id, info) {
    let modal = create("div", "modal");
    $("modal-container").appendChild(modal);

    let modal_content = create("div", "modal-content");
    modal.appendChild(modal_content);

    let message = create("p");
    modal_content.appendChild(message);

    let yes_button = create("button");
    modal_content.appendChild(yes_button);
    yes_button.innerHTML = "Igen";
    yes_button.focus();

    let no_button = create("button");
    modal_content.appendChild(no_button);
    no_button.innerHTML = "Nem";
    
    switch (target) {
        case "user":
            message.innerHTML = `Biztosan törli ${info[0]} ${info[1]} nevű felhasználót és ${info[2]} db saját kurzusát?`;
            yes_button.addEventListener("click", () => {
                deleteUser(id);
            });
            break;
        case "course":
            message.innerHTML = `Biztosan törli a(z) ${info[0]} nevű kurzust, melynek ${info[1]} felhasználó tagja?`;
            yes_button.addEventListener("click", () => {
                deleteCourse(id);
            });
            break;
        case "member":
            message.innerHTML = `Biztosan kirúgja ${info[0]} ${info[1]} nevű felhasználót a(z) ${info[2]} nevű kurzusból?`;
            yes_button.addEventListener("click", () => {
                deleteMember(id);
            });
            break;
        case "password":
            message.innerHTML = `Biztosan visszaállítja ${info[0]} ${info[1]} nevű felhasználó jelszavát?`;
            yes_button.addEventListener("click", (e) => {
                resetUserPassword(id);
                e.preventDefault();
            });
            break;
    }

    no_button.addEventListener("click", () => {
        $("modal-container").innerHTML = "";
    });

    modal.addEventListener("click", (e) => {
        if (e.target.classList[0] == "modal") {
            $("modal-container").innerHTML = "";
        }
    })
}

async function deleteUser(id) {
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
        
        $("modal-container").innerHTML = "";

        if (request.ok) {
            let result = await request.json();
            resultModal(result.uzenet, true);
        } else {
            resultModal("Művelet sikertelen! Hiba történt!");
            throw(request.status);
        }
    } catch (error) {
        console.log(error);
    }
}

async function deleteCourse(id) {
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
        
        $("modal-container").innerHTML = "";

        if (request.ok) {
            let result = await request.json();
            resultModal(result.uzenet, true);
        } else {
            resultModal("Művelet sikertelen! Hiba történt!");
            throw(request.status);
        }
    } catch (error) {
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

        $("modal-container").innerHTML = "";

        if (request.ok) {
            let result = await request.json();
            resultModal(result.uzenet, true);
        } else {
            resultModal("Művelet sikertelen! Hiba történt!");
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

        if (response.ok || response.status == 400) {
            let result = await response.json();
            if (result.sikeres) {
                window.location.href = './';
            }
            else {
                resultModal(result.uzenet);
            }
        } else {
            throw(response.status);
        }
    } catch (error) {
        console.log(error);
    }
}

async function modifyUserData() {
    let user_id = parseInt($("user_id").value);
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
            window.location.href = "./user-info?id=" + user_id;
        } else if (request.status == 400) {
            let response = await request.json();
            resultModal(response.uzenet);
        } else {
            throw request.status;
        }
    } catch (error) {
        console.log(error);
    }
}

async function resetUserPassword() {
    let user_id = parseInt($("user_id").value);

    try {
        let request = await fetch("./api/reset-user-password", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({user_id})
        });
        $("modal-container").innerHTML = "";
        if (request.ok || request.status == 400) {
            let response = await request.json();
            resultModal(response.uzenet);
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

    if ($("field") != null) {
        $("field").addEventListener("input", (e) => {
            if (e.target.value == "user_id" || e.target.value == "course_id") {
                $("keyword").value = "";
            }
        });
    }
})