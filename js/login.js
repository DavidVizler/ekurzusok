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
        let response = await fetch("./php/user_manager.php/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(loginData)
        });

        if (response.ok) {
            let result = await response.json();
            if (result.bejelentkezes == "sikeres") {
                window.location.href = './kurzusok.html';
            }
            else {
                alert(result.bejelentkezes);
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