const { Builder, Browser, By } = require('selenium-webdriver');
const URL = require('../url');

let driver;

beforeAll(async () => {
    driver = await new Builder().forBrowser(Browser.CHROME).build();
});

test("Kezdőlap", async () => {
    await driver.get(URL);
    let title = await driver.getTitle();
    await expect(title).toBe('Kezdőlap');
});

afterAll(async () => {
    await driver.quit();
})