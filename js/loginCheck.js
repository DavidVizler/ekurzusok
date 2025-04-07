function isValid(loginData) {
    const { email, password, keep_login } = loginData;

    let valid = true;
    
    if (email == '' || password == '' || keep_login == null) {
        valid = false;
    }

    const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!emailRegex.test(email)) {
        valid = false;
    }

    return valid;
}

if (typeof module !== 'undefined') {
    module.exports = isValid;
}