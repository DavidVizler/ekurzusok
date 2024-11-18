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
            lastname,
            firstname,
            email,
            password
        };

        let response = await fetch("/php/user_manager.php/signup", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(signupData)
        });

        let result = await response.json();
        // if (result.success) {
        //     alert("Sikeres regisztráció!");
        //     window.location.href = '/kurzusok.html';
        // }
        // else {
        //     alert("A regisztráció sikertelen!");
        // }
    }
}


function isValid(signupData) {
    let valid = true;

    const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!emailRegex.test(signupData.email)) {
        valid = false;
    }

    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,50}$/;
    if (!passwordRegex.test(signupData.password)) {
        valid = false;
    }
    
    if (signupData.password !== signupData.passwordConfirm) {
        valid = false;
    }

    return valid;
}

window.addEventListener('load', () => {
    $('signup_form').addEventListener('submit', (e) => {
        signup();
        e.preventDefault();
    });
});