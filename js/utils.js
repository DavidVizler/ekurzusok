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