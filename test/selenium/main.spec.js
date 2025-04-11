const { Builder, Browser } = require('selenium-webdriver');
const { URL } = require('../config');

const browsers = [Browser.CHROME, Browser.FIREFOX];

browsers.map(browser => {
    describe(browser, () => {
        let driver;
        beforeAll(async () => {
            driver = new Builder().forBrowser(browser).build();
        });
        
        test("Kezdőlap", async () => {
            await driver.get(URL);
            let title = await driver.getTitle();
            await expect(title).toBe('Kezdőlap');
        });
        
        afterAll(async () => {
            await driver.quit();
        })
    })
})