# ekurzusok
Technikusi vizsga 2024-2025 \
A projekt résztvevői: Ferenczy Bálint, Iványi Anna, Vizler Dávid

## TODO
- Fájl feltöltés tananyaghoz
- Fájl feltöltés tartalomhoz
- Feltöltött fájlok törlése
- Több fájl feltöltése
- Feladatleadás
- Leadott munkák megtekintése
- Tartalomhoz feltöltött fájlok lekérdezése
- Fájl letöltés
- Kurzuson belül a felhasználó tanár-e (jelenleg csak azt vizsgálja, hogy tulajdonos-e)
- Feladaton belül a felhasználó-e a feladat készítője (fel tud-e tölteni beadandókat vagy meg tudja tekinteni a beküldött beadandókat)
- Kurzus törlése
- Felhasználó fiók törlése
- Felhasználó kilépése a kurzusból (önmagától)
- Felhasználó tanári státuszának módosítása a kurzusban
- Kurzus tartalom törlése
- Frontend javítások (görgethetőség, reszponzivtás)
- Műveletek letiltása az archivált kurzusokban
- Saját, csatlakozott és archivált kurzusok egyértelmű elkülönítése
- Dokumentáció befejezése
- Projekt beadható és tesztelhető formában

## Dokumentáció
A részletes dokumentáció elérhető [ezen a linken](https://docs.google.com/document/d/1uhBqkqfKAe0qxYCk307rlWE4jrNmFYU45DSQCpYt-Fk/edit?usp=sharing). 

## Új PHP struktúra
Minden adatbázisshoz köthető, adatváltoztató és lekérdező művelet az api-n keresztül érhető el, POST metódussal. A műveletek megadása most már végpontok segítségével történnek (pl. `api/user/login`).

**Fetch útmutató:**\
A műveletek és lekérdezések megadások és a hozzájuk tartozó várt/visszatérő adatok megtalálhatóak [ebben a segédletben](https://docs.google.com/spreadsheets/d/1QqVU3NuwNTp1Xk_SZ8jrgYIF6DXR1OvF8vQTprfVUaY/edit?usp=sharing), ahol a végpontok állapota is nyilván van tartva.

## Új adatbázis
![Adatbázis relációs modell](./db/db.png)

## Tesztek
A tesztek futtatása előtt a [test](test/) mappába kell belépni és futtatni kell az alábbi parancsot: `npm install` \
A tesztek futtatásához az alábbi parancsot kell futtatni: `npm test`
