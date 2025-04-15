function isValid(signupData) {
    if (signupData.lastname == '' || signupData.firstname == '') {
        return { valid: false, message: "A név nem lehet üres!" };
    }

    const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!emailRegex.test(signupData.email)) {
        return { valid: false, message: "Az email cím formátuma nem megfelelő!" };
    }

    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,50}$/;
    if (!passwordRegex.test(signupData.password)) {
        return { valid: false, message: "A jelszó nem felel meg az elvárásoknak! A jelszónak legalább 8 karakterből kell állnia és tartalmaznia kell kis- és nagybetűt, illetve számjegyet." };
    }
    
    if (signupData.password !== signupData.passwordConfirm) {
        return { valid: false, message: "A jelszavak nem egyeznek meg!" };
    }

    return { valid: true, message: '' };
}

if (typeof module !== 'undefined') {
    module.exports = isValid;
}