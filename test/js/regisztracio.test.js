const isValid = require('../../js/signupCheck.js');

test('Üres minden adat', () => {
    let signupData = {
        lastname: '',
        firstname: '',
        email: '',
        password: '',
        passwordConfirm: ''
    }
    expect(isValid(signupData)).toBe(false);
});

test('Minden megfelelő', () => {
    let signupData = {
        lastname: 'Teszt',
        firstname: 'Teszt',
        email: 'teszt@email.com',
        password: 'Teszt1234',
        passwordConfirm: 'Teszt1234'
    }
    expect(isValid(signupData)).toBe(true);
});

describe('Név', () => {
    test('Hiányzó vezetéknév', () => {
        let signupData = {
            lastname: '',
            firstname: 'Teszt',
            email: 'teszt@email.com',
            password: 'Teszt1234',
            passwordConfirm: 'Teszt1234'
        }
        expect(isValid(signupData)).toBe(false);
    });

    test('Hiányzó keresztnév', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: '',
            email: 'teszt@email.com',
            password: 'Teszt1234',
            passwordConfirm: 'Teszt1234'
        }
        expect(isValid(signupData)).toBe(false);
    });
});

describe('Email', () => {
    test('Nincs @ jel', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'rossz',
            password: 'Teszt1234',
            passwordConfirm: 'Teszt1234'
        }
        expect(isValid(signupData)).toBe(false);
    });

    test('Nincs TLD megadva', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'rossz@asd',
            password: 'Teszt1234',
            passwordConfirm: 'Teszt1234'
        }
        expect(isValid(signupData)).toBe(false);
    });

    test('Tiltott karakterek', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'rossz*Đ;\\@asd.com',
            password: 'Teszt1234',
            passwordConfirm: 'Teszt1234'
        }
        expect(isValid(signupData)).toBe(false);
    });
});

describe('Jelszó', () => {
    test('Hossz kisebb mint 8', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'teszt@email.com',
            password: 'Rovid1',
            passwordConfirm: 'Rovid1'
        }
        expect(isValid(signupData)).toBe(false);
    });

    test('Hossz nagyobb mint 50', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'teszt@email.com',
            password: 'TulHosszu123456789012345678901234567890123456789012345678901234567890',
            passwordConfirm: 'TulHosszu123456789012345678901234567890123456789012345678901234567890'
        }
        expect(isValid(signupData)).toBe(false);
    });

    test('Nincs számjegy', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'teszt@email.com',
            password: 'NincsSzam',
            passwordConfirm: 'NincsSzam'
        }
        expect(isValid(signupData)).toBe(false);
    });

    test('Nincs nagybetű', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'teszt@email.com',
            password: 'csakkicsi1234',
            passwordConfirm: 'csakkicsi1234'
        }
        expect(isValid(signupData)).toBe(false);
    });

    test('Nincs kisbetű', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'teszt@email.com',
            password: 'csaknagy1234',
            passwordConfirm: 'csaknagy1234'
        }
        expect(isValid(signupData)).toBe(false);
    });

    test('Megerősítés nem egyezik', () => {
        let signupData = {
            lastname: 'Teszt',
            firstname: 'Teszt',
            email: 'teszt@email.com',
            password: 'Teszt1234',
            passwordConfirm: 'Teszt4321'
        }
        expect(isValid(signupData)).toBe(false);
    });
});