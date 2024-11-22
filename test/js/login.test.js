const isValid = require('../../js/loginCheck.js');

test('Üres minden adat', () => {
    let loginData = {
        email: '',
        password: ''
    };
    expect(isValid(loginData)).toBe(false);
});

test('Minden megfelelő', () => {
    let loginData = {
        email: 'teszt@email.com',
        password: 'Teszt1234',
    };
    expect(isValid(loginData)).toBe(true);
});

describe('Email', () => {
    test('Nincs @ jel', () => {
        let loginData = {
            email: 'teszt',
            password: 'Teszt1234',
        };
        expect(isValid(loginData)).toBe(false);
    });

    test('Nincs TLD', () => {
        let loginData = {
            email: 'teszt@email',
            password: 'Teszt1234',
        };
        expect(isValid(loginData)).toBe(false);
    });

    test('Nem engedélyezett karakterek', () => {
        let loginData = {
            email: 'teszt<>\\$ßˇˇ°¤@email.com',
            password: 'Teszt1234',
        };
        expect(isValid(loginData)).toBe(false);
    });
});
