const URL = require('./url');

async function signup(signupData) {
    let response = await fetch(URL + '/php/user_manager.php/signup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(signupData)
    });

    if (response.ok) {
        let result = await response.json();
        console.log(`Response from server: ${result}`);
        if (result.sikeres) {
            return true;
        }
        else {
            console.error(`Test failed: ${result.uzenet}`);
            return false;
        }
    }
    else {
        console.error(`Test failed: ${response.status} ${response.statusText}`);
        return false;
    }
}

function generateEmail(n = 0) {
    return 'teszt' + new Date().toJSON().replaceAll(':', '').toLowerCase() + n + '@teszt.com';
}

const lastname = 'Teszt';
const firstname = 'Teszt';
const email = generateEmail();
const password = 'Teszt1234';

describe('Regisztráció', () => {
    test('Sikeres regisztráció', async () => {
        let signupData = {
            lastname,
            firstname,
            email,
            password
        };

        let result = await signup(signupData);
        await expect(result).toBe(true);
    });

    describe('Sikertelen regisztráció', () => {
        test('Felhasználó már létezik', async () => {
            let signupData = {
                lastname,
                firstname,
                email,
                password
            };
    
            let result = await signup(signupData);
            await expect(result).toBe(false);
        });

        test('Üres név', async () => {
            let signupData = {
                lastname: '',
                firstname: '',
                email: generateEmail(2),
                password
            };
    
            let result = await signup(signupData);
            await expect(result).toBe(false);
        });

        test('Hibás email cím', async () => {
            let signupData = {
                lastname,
                firstname,
                email: 'emailcím.',
                password
            };
    
            let result = await signup(signupData);
            await expect(result).toBe(false);
        });

        test('Nem megfelelő jelszó', async () => {
            let signupData = {
                lastname: '',
                firstname: '',
                email: generateEmail(3),
                password: 'jelszó'
            };
    
            let result = await signup(signupData);
            await expect(result).toBe(false);
        });
    });
});