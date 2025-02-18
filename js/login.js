function $(id) {
    return document.getElementById(id);
}

function resultModal(result){
    let modal = document.createElement("div")
    modal.classList.add("modal")
    let alertDiv = document.getElementById('alertDiv')
    alertDiv.style.display = "block"
    alertDiv.appendChild(modal)

    let modal_content = document.createElement("div")
    modal_content.classList.add("modal-content")
    modal.appendChild(modal_content)

    let message = document.createElement("p")
    modal_content.appendChild(message)
    message.innerHTML = result

    let ok_button = document.createElement("button")
    modal_content.appendChild(ok_button)
    ok_button.innerHTML = "OK"

    ok_button.addEventListener("click",()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
}

async function login() {
    let email = $('email').value;
    let password = $('password').value;

    let loginData = {
        email,
        password
    };

    if (isValid(loginData)) {
        let response = await fetch("./api/user/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(loginData)
        });

        if (response.ok) {
            let result = await response.json();
            if (result.sikeres) {
                window.location.href = './kurzusok.html';
            }
            else {
                resultModal(result.uzenet);
            }
        }
    }
}

window.addEventListener('load', () => {
    $('login_form').addEventListener('submit', (e) => {
        login();
        e.preventDefault();
    });
});

$('showPassword').addEventListener('click', ()=>{
    let password = $('password')
    if(password.type == "password"){
        password.type = "text"
    }else{
        password.type = "password"
    }
})