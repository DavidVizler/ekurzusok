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

async function listUsers(page, rows) {
    try {
        let request = await fetch("./api/get-users", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows
            })
        });

        if (request.ok) {
            let userList = await request.json();
            if (userList.length == 2) {
                let users = userList[0];
                let own_course_count = userList[1];
                let content = "";
                
                for (let i = 0; i < users.length; i++) {
                    content += `<tr>
                        <td>${users[i]["id"]}</td>
                        <td>${users[i]["email"]}</td>
                        <td>${users[i]["lastname"]}</td>
                        <td>${users[i]["firstname"]}</td>
                        <td>
                            ${users[i]["courses"]} (${own_course_count[i]["courses"]} saját) <a href="user-info?id=${users[i]["id"]}">Több infó</a>
                        </td>
                    </tr>`
                }
                
                $("table-content").innerHTML = content;
            } 

            $("rows").value = rows; 
        } else {
            throw request.status;
        }
    } catch(error) {
        console.log(error);
    }
}

async function listCourses(page, rows) {
    try {
        let request = await fetch("./api/get-courses", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows
            })
        });

        if (request.ok) {
            let courseList = await request.json();
            if (courseList.length == 2) {
                let courses = courseList[0];
                let teacher_count = courseList[1];
                let content = "";
                
                for (let i = 0; i < courses.length; i++) {
                    content += `<tr>
                        <td>${courses[i]["id"]}</td>
                        <td>${courses[i]["name"]}</td>
                        <td>${courses[i]["desc"]}</td>
                        <td>${courses[i]["code"]}</td>
                        <td>${courses[i]["archived"] == 1 ? "Igen" : "Nem"}</td>
                        <td>
                            <a href="user-info?id=${courses[i]["owner_id"]}">${courses[i]["owner_lastname"]} ${courses[i]["owner_firstname"]} (${courses[i]["owner_email"]})</a>
                        </td>
                        <td>
                            ${courses[i]["members_count"]} (${teacher_count[i]["teachers_count"]} tanár) <a href="course-info?id=${courses[i]["id"]}">Több infó</a>
                        </td>
                    </tr>`
                }
                
                $("table-content").innerHTML = content;
            } 

            $("rows").value = rows; 
        } else {
            throw request.status;
        }
    } catch(error) {
        console.log(error);
    }
}

async function listCourseInfo(page, rows, id) {
    try {
        let request = await fetch("./api/get-course-info", {
            method: "POST",
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                page,
                rows,
                id
            })
        });

        if (request.ok) {
            let courseInfo = await request.json();
            if (courseInfo.course_members.length > 0) {
                let data = courseInfo.course_data;
                let members = courseInfo.course_members;
                let content = "";
                
                for (let i = 0; i < members.length; i++) {
                    content += `<tr>
                        <td>${members[i]["user_id"]}</td>
                        <td>${members[i]["membership_id"]}</td>
                        <td>${members[i]["lastname"]}</td>
                        <td>${members[i]["firstname"]}</td>
                        <td>${members[i]["email"]}</td>
                        <td>${members[i]["teacher"] == 1 ? "Igen" : "Nem"}</td>
                        <td>
                            <a href="user-info?id=${members[i]["id"]}">Több infó</a>
                        </td>
                        <td class='torles'>
                            <form method='POST' action='javascript:;' onsubmit='deleteMember(${members[i]['membership_id']})'>
                                <input type='submit' value='Eltávolítás' name='delete_button'>
                            </form>
                        </td>
                    </tr>`
                }
                
                $("table-content").innerHTML = content;
            } 

            $("rows").value = rows; 
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
                alert(response.uzenet);
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