# ekurzusok
Technikusi vizsga 2024-2025 \
A projekt résztvevői: Ferenczy Bálint, Iványi Anna, Vizler Dávid

## TODO
**Frontend**
- Feladatleadás (ne tudja mégegyszer leadni a felhasználó)
- Kurzus törlése
- Felhasználó kilépése a kurzusból (önmagától)
- Frontend javítások (görgethetőség, reszponzivtás)
- Saját, csatlakozott és archivált kurzusok egyértelmű elkülönítése
- Beadott munkák megtekintése
- Beadandóhoz feltöltött fájlok lekérdezése
- Felhasználó tanári státuszának módosítása a kurzusban

**Backend**
- Felhasználó fiók törlése

**Mindkettő**
- Feladaton belül a felhasználó-e a feladat készítője (fel tud-e tölteni beadandókat vagy meg tudja tekinteni a beküldött beadandókat)
- Több fájl feltöltése a meglévők mellé
- Feltöltött fájlok törlése
- Leadott munkák megtekintése
- Tartalomhoz feltöltött fájlok lekérdezése
- Kurzuson belül a felhasználó tanár-e (jelenleg csak azt vizsgálja, hogy tulajdonos-e)
- Műveletek letiltása az archivált kurzusokban

**Egyéb**
- Dokumentáció befejezése
- Projekt beadható és tesztelhető formában

## Fájlfeltöltéshez szükséges teendők
**php.ini**-ben pár paramétert át kell állítani.\
A fájl itt található Windows-on: `C:\xampp\php\php.ini`
```
file_uploads = On
upload_max_filesize = "30M"
max_file_uploads = 10
post_max_size = "300M"
```

## Dokumentáció
A részletes dokumentáció elérhető [ezen a linken](https://docs.google.com/document/d/1uhBqkqfKAe0qxYCk307rlWE4jrNmFYU45DSQCpYt-Fk/edit?usp=sharing). 

## PHP struktúra és API
Minden adatbázisshoz köthető, adatváltoztató és lekérdező művelet az api-n keresztül érhető el, POST metódussal. A műveletek megadása végpontok segítségével történnek (pl. `api/user/login`).

A műveletek és lekérdezések megadások és a hozzájuk tartozó várt/visszatérő adatok megtalálhatóak [ebben a segédletben](https://docs.google.com/spreadsheets/d/1QqVU3NuwNTp1Xk_SZ8jrgYIF6DXR1OvF8vQTprfVUaY/edit?usp=sharing), ahol a végpontok állapota is nyilván van tartva.

## Fájlok letöltése
A letöltendő fájlok esetén az alábbi linket kell megnyitni a megfelelő értékkel behelyettesítve:

`downloader?file_id=x&attached_to=y&id=z`

A `file_id` a fájl ID-ja, az `attached_to` azt jelenti, hogy mihez van csatolva a fájl: ha tartalomhoz, akkor ennek az értéke `content`, ha beadandóhoz, akkor `submission`. Az `id` a tartalom vagy beadandó ID-ját jelöli.

## Adatbázis
(Az `admins` tábla nélkül)

![Adatbázis relációs modell](./db/db.png)

## Tesztek
A tesztek futtatása előtt a [test](test/) mappába kell belépni és futtatni kell az alábbi parancsot: `npm install` \
A tesztek futtatásához az alábbi parancsot kell futtatni: `npm test`
