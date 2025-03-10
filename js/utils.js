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

// A yyyy-mm-dd HH:MM:SS (UTC) formátumú dátumokat átkonvertálja JavaScript Date objektumokká
function convertDate(date) {
    // ISO 8601 formátumra konvertálás
    let iso8601 = `${date.substring(0, 10)}T${date.substring(11, 19)}.000Z`;
    return new Date(iso8601);
}