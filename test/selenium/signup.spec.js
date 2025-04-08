const { Builder, Browser, By } = require('selenium-webdriver');
const URL = require('../url');

const SIGNUP_URL = URL + '/signup.html';

const browsers = [Browser.CHROME, Browser.FIREFOX];

browsers.map(browser => {
    describe(browser, () => {
        let driver;
        
        beforeAll(async () => {
            driver = new Builder().forBrowser(browser).build();
        });
        
        beforeEach(async () => {
            await driver.get(SIGNUP_URL);
            await driver.manage().setTimeouts({implicit: 500});
        })

        test("Regisztráció oldal", async () => {
            let title = await driver.getTitle();
            await expect(title).toBe('Regisztráció');

            let lastnameInputExists = (await driver.findElements(By.id('lastname'))).length > 0;
            await expect(lastnameInputExists).toBe(true);

            let firstnameInputExists = (await driver.findElements(By.id('firstname'))).length > 0;
            await expect(firstnameInputExists).toBe(true);

            let emailInputExists = (await driver.findElements(By.id('email'))).length > 0;
            await expect(emailInputExists).toBe(true);

            let passwordInputExists = (await driver.findElements(By.id('password'))).length > 0;
            await expect(passwordInputExists).toBe(true);

            let pwdConfirmInputExists = (await driver.findElements(By.id('confirm-password'))).length > 0;
            await expect(pwdConfirmInputExists).toBe(true);
        });
        
        afterAll(async () => {
            await driver.quit();
        })
    })
})