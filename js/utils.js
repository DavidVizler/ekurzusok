// Megadott id-jű elem lekérése
function $(id) {
    return document.getElementById(id);
}

// Új 'elem' típusú HTML elem létrehozása
function create(elem, ...classes) {
    let e = document.createElement(elem);
    e.classList.add(...classes);
    return e;
}

// Az URL-ben átadott paraméterek lekérése
function getUrlParams() {
    return new URL(location.href).searchParams;
}

// Az URL részeinek lekérése ('/' jeleknél elválasztva)
function getUrlParts() {
    return location.href.split('/');
}

// Az URL utolsó részének lekérése
function getUrlEndpoint() {
    let parts = getUrlParts();
    return parts[parts.length - 1];
}

// A dátumot yyyy-mm-dd HH:MM:SS formátumú szöveggé alakítja. Üres érték esetén null-t ad vissza.
function convertDate(datetime) {
    if (datetime == '' || datetime == null) return null;
    let [date, time] = datetime.split('T');
    // Másodperc hiányzik-e
    if ((time.match(/:/g) || []).length == 1) {
        time += ':00';
    }
    return date + ' ' + time;
}

// Jelenlegi idő lekérése yyyy-mm-dd HH:MM:SS formátumban
function getCurrentTime() {
    let now = new Date();
    let year = now.getFullYear();
    let month = String(now.getMonth() + 1).padStart(2, '0');
    let day = String(now.getDate()).padStart(2, '0');
    let hours = String(now.getHours()).padStart(2, '0');
    let minutes = String(now.getMinutes()).padStart(2, '0');
    let seconds = String(now.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}


if (typeof module !== 'undefined') {
    module.exports = {
        $,
        create,
        getUrlParams,
        getUrlParts,
        getUrlEndpoint,
        convertDate
    };
}