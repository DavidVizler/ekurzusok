const { URL } = require('../url');

async function signup(signupData) {
    let response = await fetch(URL + '/api/user/signup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(signupData)
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

let i = 0;
let timestamp = new Date().getTime();
function generateEmail() {
    i = i + 1;
    return 'teszt' + timestamp + '.' + i + '@teszt.com';
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
                email: generateEmail(),
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
                email: generateEmail(),
                password: 'jelszó'
            };
    
            let result = await signup(signupData);
            await expect(result).toBe(false);
        });
    });
});