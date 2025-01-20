function $(id) {
    return document.getElementById(id);
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
                alert(result.uzenet);
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