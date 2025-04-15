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

async function signup() {
    let lastname = $('lastname').value;
    let firstname = $('firstname').value;
    let email = $('email').value;
    let password = $('password').value;
    let passwordConfirm = $('confirm-password').value;

    let checkResult = isValid({ lastname, firstname, email, password, passwordConfirm });

    if (checkResult.valid) {
        let [response, result] = await API.signup(email, firstname, lastname, password);

        if (response.ok) {
            if (result.sikeres) {
                window.location.href = './kurzusok.html';
            }
            else {
                resultModal(result.uzenet);
            }
        }
    } else {
        resultModal(checkResult.message);
    }
}

window.addEventListener('load', () => {
    $('signup_form').addEventListener('submit', (e) => {
        signup();
        e.preventDefault();
    });
});
