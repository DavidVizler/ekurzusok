function isValid(signupData) {
    let valid = true;

    if (signupData.lastname == '') {
        valid = false;
    }

    if (signupData.firstname == '') {
        valid = false;
    }

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

if (typeof module !== 'undefined') {
    module.exports = isValid;
}