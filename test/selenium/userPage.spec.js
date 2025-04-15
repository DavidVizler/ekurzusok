const { Builder, Browser, By, until } = require('selenium-webdriver');
const { USER_URL, LOGIN_URL, EMAIL, PASSWORD } = require('../config');

const browsers = [Browser.CHROME, Browser.FIREFOX];
const timeout = 30000;

async function login(driver) {
    let emailInput = await driver.findElement(By.id('email'));
    emailInput.sendKeys(EMAIL);

    let passwordInput = await driver.findElement(By.id('password'));
    passwordInput.sendKeys(PASSWORD);

    let loginBtn = await driver.findElement(By.id('button_login'));
    await loginBtn.click();

    await driver.wait(until.urlContains('kurzusok.html'));
}

browsers.map(browser => {
    describe(browser, () => {
        let driver;

        beforeAll(async () => {
            driver = new Builder().forBrowser(browser).build();
            jest.setTimeout(60000)
        }, timeout);
        
        beforeEach(async () => {
            await driver.get(LOGIN_URL);
            await driver.manage().setTimeouts({implicit: 5000});

            await login(driver);
            await driver.get(USER_URL);
        }, timeout)

        test("Felhasználó oldala", async () => {
            let title = await driver.getTitle();
            await expect(title).toBe('Felhasználói fiók');

            let emailInputExists = (await driver.findElements(By.id('emailInput'))).length > 0;
            await expect(emailInputExists).toBe(true);

            let lastnameInputExists = (await driver.findElements(By.id('lastname'))).length > 0;
            await expect(lastnameInputExists).toBe(true);

            let firstnameInputExists = (await driver.findElements(By.id('firstname'))).length > 0;
            await expect(firstnameInputExists).toBe(true);

            let passwordInputExists = (await driver.findElements(By.id('password'))).length > 0;
            await expect(passwordInputExists).toBe(true);

            let buttonExists = (await driver.findElements(By.id('modifyUserDataButton'))).length > 0;
            await expect(buttonExists).toBe(true);
        }, timeout)

        test("Adatmódosítás", async () => {
            let firstnameInput = await driver.findElement(By.id('firstname'));
            await firstnameInput.click();
            await firstnameInput.sendKeys('2');

            let passwordInput = await driver.findElement(By.id('password'));
            await passwordInput.sendKeys(PASSWORD);

            let btn = await driver.findElement(By.id('modifyUserDataButton'));
            await btn.click();

            let modalContent = await driver.findElement(By.className('modal-content'));
            let modalText = await modalContent.findElement(By.tagName('p')).getText();
            await expect(modalText).toBe('Sikeres adatmódosítás!');
        }, timeout)

        afterAll(async () => {
            await driver.quit();
        })
    })
})