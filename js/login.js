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

    let response = await fetch("./php/user_manager.php/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(loginData)
    });

    let result = await response.json();
    // if (result.siker) {
    //     alert("Sikeres bejelentkezés!");
    //     window.location.href = '/kurzusok.html';
    // }
    // else {
    //     alert("A bejelentkezés sikertelen!");
    // }
}

window.addEventListener('load', () => {
    $('login_form').addEventListener('submit', (e) => {
        login();
        e.preventDefault();
    });
});