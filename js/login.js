function resultModal(result){
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
        let [response, result] = await API.login(email, password);

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

$('showPassword').addEventListener('click', ()=>{
    let password = $('password')
    if(password.type == "password"){
        password.type = "text"
    }else{
        password.type = "password"
    }
})