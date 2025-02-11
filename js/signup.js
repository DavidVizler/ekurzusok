function $(id) {
    return document.getElementById(id);
}

async function signup() {
    let lastname = $('lastname').value;
    let firstname = $('firstname').value;
    let email = $('email').value;
    let password = $('password').value;
    let passwordConfirm = $('confirm-password').value;

    if (isValid({ lastname, firstname, email, password, passwordConfirm })) {
        let signupData = {
            "manage" : "user",
            "action" : "signup",
            lastname,
            firstname,
            email,
            password
        };

        let response = await fetch("./api/user/signup", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(signupData)
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
    $('signup_form').addEventListener('submit', (e) => {
        signup();
        e.preventDefault();
    });
});
