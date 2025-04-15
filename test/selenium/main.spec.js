const { Builder, Browser } = require('selenium-webdriver');
const { URL } = require('../config');

const browsers = [Browser.CHROME, Browser.FIREFOX];
const timeout = 20000;

browsers.map(browser => {
    describe(browser, () => {
        let driver;
        beforeAll(async () => {
            driver = new Builder().forBrowser(browser).build();
            jest.setTimeout(60000);
        });
        
        test("Kezdőlap", async () => {
            await driver.get(URL);
            let title = await driver.getTitle();
            await expect(title).toBe('Kezdőlap');
        }, timeout);
        
        afterAll(async () => {
            await driver.quit();
        })
    })
})