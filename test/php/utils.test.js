/**
 * @jest-environment jsdom
 * @jest-environment-options { "url": "https://example.com/part1/part2/index.html?testParam=5&testParam2=9" }
 */

const Utils = require('../../js/utils.js');

test('$(id) - HTML elem lekérése id alapján', () => {
    document.body.innerHTML = '<div id="testDiv">TESZT</div>';
    let testDiv = Utils.$('testDiv');
    expect(testDiv.innerHTML).toBe('TESZT');
});

test('create - Új elem létrehozása', () => {
    let uj = Utils.create('div', 'test1', 'test2');
    expect(uj.classList.contains('test1')).toBe(true);
    expect(uj.classList.contains('test2')).toBe(true);
});

test('getUrlParams - URL paraméterek lekérése', () => {
    let params = Utils.getUrlParams();
    expect(params.get('testParam')).toBe('5');
    expect(params.get('testParam2')).toBe('9');
});

test('getUrlParts - URL részeinek lekérése', () => {
    let parts = Utils.getUrlParts();
    expect(parts.length).toBe(6);
    expect(parts[3]).toBe('part1');
    expect(parts[4]).toBe('part2');
});

test('getUrlEndpoint - URL utolsó részének lekérése', () => {
    let endpoint = Utils.getUrlEndpoint();
    expect(endpoint).toBe('index.html?testParam=5&testParam2=9');
})

test('convertDate - Dátum átkonvertálása', () => {
    let now = new Date();
    now.setMilliseconds(0);

    // ISO 8601
    let iso8601 = now.toISOString();

    // yyyy-mm-dd HH:MM:SS
    now.setMinutes(now.getMinutes() + now.getTimezoneOffset());
    // Hibás
    let hms = `${now.getFullYear()}-${now.getMonth()}-${now.getDay()} ${now.getHours()}:${now.getMinutes()}:${now.getSeconds()}`;

    let testDate = Utils.convertDate(hms);

    console.log(now.toISOString())
    console.log(iso8601);
    console.log(testDate);
    expect(testDate.toISOString()).toBe(iso8601);
})