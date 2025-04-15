const { Builder, By, until } = require('selenium-webdriver');
const { SIGNUP_URL, PASSWORD, BROWSERS, TIMEOUT } = require('../config');

BROWSERS.map(browser => {
    describe(browser, () => {
        let driver;
        
        beforeAll(async () => {
            driver = new Builder().forBrowser(browser).build();
            jest.setTimeout(60000)
        }, TIMEOUT);
        
        beforeEach(async () => {
            await driver.get(SIGNUP_URL);
            await driver.manage().setTimeouts({implicit: 5000});
        }, TIMEOUT)

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

            let signupButtonExists = (await driver.findElements(By.id('signup_button'))).length > 0;
            await expect(signupButtonExists).toBe(true);
        }, TIMEOUT);

        test("Új felhasználó létrehozása", async () => {
            let lastname = 'Selenium';
            let firstname = 'Test';
            let email = 'selenium' + new Date().getTime() + '@teszt.com';
            
            let lastnameInput = await driver.findElement(By.id('lastname'));
            await lastnameInput.sendKeys(lastname);

            let firstnameInput = await driver.findElement(By.id('firstname'));
            await firstnameInput.sendKeys(firstname);

            let emailInput = await driver.findElement(By.id('email'));
            await emailInput.sendKeys(email);

            let passwordInput = await driver.findElement(By.id('password'));
            await passwordInput.sendKeys(PASSWORD);

            let passwordConfirmInput = await driver.findElement(By.id('confirm-password'));
            await passwordConfirmInput.sendKeys(PASSWORD);

            let signupBtn = await driver.findElement(By.id('signup_button'));
            await signupBtn.click();

            await driver.wait(until.urlContains('kurzusok.html'));

            let title = await driver.getTitle();
            await expect(title).toBe('Kurzusok');
        }, TIMEOUT)
        
        afterAll(async () => {
            await driver.quit();
        })
    })
})