# ekurzusok
Technikusi vizsga 2024-2025 \
A projekt résztvevői: Ferenczy Bálint, Iványi Anna, Vizler Dávid

## Új PHP struktúra
Minden adatbázisshoz köthető, adatváltoztató és lekérdező művelet az api-n keresztül érhető el, POST metódussal. A műveletek megadása most már végpontok segítségével történnek (pl. `api/user/login`).

**Fetch útmutató:**\
A műveletek és lekérdezések megadások és a hozzájuk tartozó várt/visszatérő adatok megtalálhatóak [ebben a segédletben](https://docs.google.com/spreadsheets/d/1QqVU3NuwNTp1Xk_SZ8jrgYIF6DXR1OvF8vQTprfVUaY/edit?usp=sharing), ahol a végpontok állapota is nyilván van tartva.

## Tesztek
A tesztek futtatása előtt a [test](test/) mappába kell belépni és futtatni kell az alábbi parancsot: `npm install` \
A tesztek futtatásához az alábbi parancsot kell futtatni: `npm test`
