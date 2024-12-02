const URL = require('./url');

async function login(loginData) {
    let response = await fetch(URL + '/php/user_manager.php/login', {
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

describe('Bejelentkezés', () => {
    test('Nem létező email cím', async () => {
        let loginData = {
            email: 'nemletezo@teszt.com',
            password
        };

        let result = await login(loginData);
        await expect(result).toBe(false);
    });

    test('Hibás jelszó', async () => {
        let loginData = {
            email,
            password: 'Hibás1234'
        };

        let result = await login(loginData);
        await expect(result).toBe(false);
    });

    test('Sikeres', async () => {
        let loginData = { email, password };

        let result = await login(loginData);
        await expect(result).toBe(true);
    });
});