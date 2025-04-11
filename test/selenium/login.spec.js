const { Builder, Browser, By, until } = require('selenium-webdriver');
const { LOGIN_URL } = require('../url');

const browsers = [Browser.CHROME, Browser.FIREFOX];

browsers.map(browser => {
    describe(browser, () => {
        let driver;

        beforeAll(async () => {
            driver = new Builder().forBrowser(browser).build();
        })

        beforeEach(async () => {
            await driver.get(LOGIN_URL);
            await driver.manage().setTimeouts({implicit: 2000});
        })

        test("Bejelentkezés oldal", async () => {
            let title = await driver.getTitle();
            await expect(title).toBe('Bejelentkezés');

            let emailInputExists = (await driver.findElements(By.id('email'))).length > 0;
            await expect(emailInputExists).toBe(true);

            let passwordInputExists = (await driver.findElements(By.id('password'))).length > 0;
            await expect(passwordInputExists).toBe(true);

            let stayLoggedInExists = (await driver.findElements(By.id('stayLoggedIn'))).length > 0;
            await expect(stayLoggedInExists).toBe(true);

            let loginBtnExists = (await driver.findElements(By.id('button_login'))).length > 0;
            await expect(loginBtnExists).toBe(true);
        })

        test("Bejelentkezés - hibás adatok", async () => {
            let email = 'teszt@teszt.com';
            let password = 'Hibás1234';

            let emailInput = await driver.findElement(By.id('email'));
            emailInput.sendKeys(email);

            let passwordInput = await driver.findElement(By.id('password'));
            passwordInput.sendKeys(password);

            let loginBtn = await driver.findElement(By.id('button_login'));
            await loginBtn.click();

            let isAlertDisplayed = await driver.findElement(By.className('modal-content')).isDisplayed();
            await expect(isAlertDisplayed).toBe(true);
        })

        test("Bejelentkezés - sikeres bejelentkezés", async () => {
            let email = 'teszt@teszt.com';
            let password = 'Teszt1234';

            let emailInput = await driver.findElement(By.id('email'));
            emailInput.sendKeys(email);

            let passwordInput = await driver.findElement(By.id('password'));
            passwordInput.sendKeys(password);

            let loginBtn = await driver.findElement(By.id('button_login'));
            await loginBtn.click();

            await driver.wait(until.urlContains('kurzusok.html'));

            let title = await driver.getTitle();
            await expect(title).toBe('Kurzusok');
        })

        afterAll(async () => {
            await driver.quit();
        })
    })
})