function resultModal(result) {
    let modal = create("div", 'modal')
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "block"
    alertDiv.appendChild(modal)

    let modal_content = create("div", 'modal-content')
    modal.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = result

    let ok_button = create("button")
    modal_content.appendChild(ok_button)
    ok_button.innerHTML = "OK"

    ok_button.addEventListener("click", () => {
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })

    ok_button.focus()
}

async function login() {
    let email = $('email').value;
    let password = $('password').value;
    let keep_login = $('stayLoggedIn').checked;

    let loginData = {
        email,
        password,
        keep_login
    };

    if (isValid(loginData)) {
        let [response, result] = await API.login(email, password, keep_login);

        if (response.ok) {
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


document.getElementById('toggle-password').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.innerHTML = `
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
      `;
    } else {
        passwordField.type = 'password';
        eyeIcon.innerHTML = `
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
      `;
    }
});

async function forgottenPasswd(email) {
    try {
        let request = await fetch("./api/user/forgotten-password", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({"email": email})
        });
        if (request.ok || request.status == 400 || request.status == 404) {
            let response = await request.json();
            resultModal(response.uzenet)
        } else {
            throw request.status;
        }
    } catch (error) {
        console.log(error);
    }
}

$("forgottenPasswd").addEventListener("click", function () {
    let emailModal = create("div", 'modal')
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "block"
    alertDiv.appendChild(emailModal)

    let modal_content = create("div", 'modal-content')
    emailModal.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = "Kérjük, adja meg e-mail címét:<br><input type='text' id='resetEmail'>";

    let ok_button = create("button")
    modal_content.appendChild(ok_button)
    ok_button.innerHTML = "Új jelszó igénylése"

    ok_button.addEventListener("click", () => {
        forgottenPasswd($("resetEmail").value);
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })

    ok_button.focus()
});