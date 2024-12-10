function $(id) {
    return document.getElementById(id);
}

async function login() {
    let email = $('email').value;
    let password = $('password').value;

    let loginData = {
        "manage" : "user",
        "action" : "login",
        email,
        password
    };

    if (isValid(loginData)) {
        let response = await fetch("./php/data_manager.php", {
            method: "POST",
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
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