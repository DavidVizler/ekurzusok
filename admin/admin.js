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

async function getPageCount(field, rows) {
    let countRequest = await fetch(`./api/get-${field}-count`);
    let count = await countRequest.json();
    let pageCount = Math.ceil(count[0]["count"] / rows);
    $("count").innerHTML = count + " találat";
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


function prevPage() {
    $("page").value -= 1;
    $("page-form").submit();
}

function nextPage() {
    let next = parseInt($("page").value) + 1;
    $("page").value = next;
    $("page-form").submit();
}

function setRowNumber(rows) {
    $("rows").value = rows;
}

function manualPageTurn(event) {
    if (event.key == "Enter") {
        $("page-form").submit();
    }
}