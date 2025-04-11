const { URL } = require('../url');

async function login(loginData) {
    let response = await fetch(URL + '/api/user/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(loginData)
    });

    if (response.ok) {
        let result = await response.json();
        if (result.sikeres) {
            return true;
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

const email = 'teszt@teszt.com';
const password = 'Teszt1234';
const keep_login = false;

describe('Bejelentkezés', () => {
    test('Nem létező email cím', async () => {
        let loginData = {
            email: 'nemletezo@teszt.com',
            password,
            keep_login
        };

        let result = await login(loginData);
        await expect(result).toBe(false);
    });

    test('Hibás jelszó', async () => {
        let loginData = {
            email,
            password: 'Hibás1234',
            keep_login
        };

        let result = await login(loginData);
        await expect(result).toBe(false);
    });

    test('Sikeres', async () => {
        let loginData = { email, password, keep_login };

        let result = await login(loginData);
        await expect(result).toBe(true);
    });
});