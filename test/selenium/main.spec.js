const { Builder } = require('selenium-webdriver');
const { URL, BROWSERS, TIMEOUT } = require('../config');

BROWSERS.map(browser => {
    describe(browser, () => {
        let driver;
        beforeAll(async () => {
            driver = new Builder().forBrowser(browser).build();
            jest.setTimeout(60000);
        }, TIMEOUT);
        
        test("Kezdőlap", async () => {
            await driver.get(URL);
            let title = await driver.getTitle();
            await expect(title).toBe('Kezdőlap');
        }, TIMEOUT);
        
        afterAll(async () => {
            await driver.quit();
        })
    })
})