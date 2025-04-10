async function signup() {
    let lastname = $('lastname').value;
    let firstname = $('firstname').value;
    let email = $('email').value;
    let password = $('password').value;
    let passwordConfirm = $('confirm-password').value;

    if (isValid({ lastname, firstname, email, password, passwordConfirm })) {
        let [response, result] = await API.signup(email, firstname, lastname, password);

        if (response.ok) {
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
