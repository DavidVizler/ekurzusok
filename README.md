# ekurzusok
Technikusi vizsga 2024-2025 \
A projekt résztvevői: Ferenczy Bálint, Iványi Anna, Vizler Dávid

## TODO
**Frontend**
- [x] Feladatleadás (ne tudja mégegyszer leadni a felhasználó)
- [x] Kurzus törlése
- [x] Felhasználó kilépése a kurzusból (önmagától)
- [x] Frontend javítások (görgethetőség, reszponzivtás)
- [ ] Saját, csatlakozott és archivált kurzusok egyértelmű elkülönítése
- [x] Beadott munkák megtekintése
- [x] Beadandóhoz feltöltött fájlok lekérdezése
- [ ] Felhasználó tanári státuszának módosítása a kurzusban
- [x] Feltöltött fájlok törlése
- [x] Tartalomhoz feltöltött fájlok lekérdezése
- [ ] Több fájl feltöltése a meglévők mellé
- [x] Leadott munkák megtekintése
- [x] Kilépés gomb ne jelenjen meg a felhasználó saját kurzusában
- [x] Leadás értékelése, A beadandóhoz tartozó adatok lekérdezésénél az értékelés pontja és a max pont is legyen ott

**Backend**
- [x] Felhasználó fiók törlése
- [x] Műveletek letiltása az archivált kurzusokban

**Mindkettő**
- [x] Feladaton belül a felhasználó-e a feladat készítője (fel tud-e tölteni beadandókat vagy meg tudja tekinteni a beküldött beadandókat)
- [x] Kurzuson belül a felhasználó tanár-e (jelenleg csak azt vizsgálja, hogy tulajdonos-e)
- [x] Kurzus csatlakozási kód lekérése és megjelenítése
- [x] Annak lekérése, hogy a felhasználó már leadta-e az adott feladatot

**Egyéb**
- [x] Adatgenerátor ne generálja le ugyanazt az e-mail címet többször + adatbázis frissítése
- [ ] Dokumentáció befejezése
- [x] Projekt beadható és tesztelhető formában

## Fájlfeltöltéshez szükséges teendők
**php.ini**-ben pár paramétert át kell állítani.\
A fájl itt található Windows-on: `C:\xampp\php\php.ini`
```
file_uploads = On
upload_max_filesize = 30M
max_file_uploads = 10
post_max_size = 300M
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

A db mappában megtalálható az `ekurzusok_ures.sql`, ami az üres adatbázis és az `ekurzusok_tesztadatokkal.sql`, ami tartalmaz 12.754 felhasználót, 4632 kurzust, 138.984 tagságot és egy admin fiókot.

Az adatgenerátor a `DataGenerator.zip` fájlban található. Kicsomagolás után a mappán belül a `dotnet run` paranccsal lehet futtatni. A program 3 CSV fájlt generál: `user.csv`, `courses.csv` és `memberships.csv`, amelyeket be lehet importálni az **üres** SQL adatbázisba. Windows-on a `DataGenerator\bin\Debug\net8.0` mappába teszi a fájlokat, Linuxon közvetlenül a `DataGenerator` gyökérkönyvtárba. **Importáláskor be kell állítani phpmyAdmin-ban, hogy az első sort (fejlécet) hagyja ki és hogy az elválsztó karakter pontosvessző legyen!**

## Tesztek
A tesztek futtatása előtt a [test](test/) mappába kell belépni és futtatni kell az alábbi parancsot: `npm install` \
A tesztek futtatásához az alábbi parancsot kell futtatni: `npm test`
