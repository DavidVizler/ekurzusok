# ekurzusok
Technikusi vizsga 2024-2025 \
A projekt résztvevői: Ferenczy Bálint, Iványi Anna, Vizler Dávid

## PHP struktúra
Most már minden adatváltozással járó művelet a data_manager.php-n keresztül érhető el.

**Fetch útmutató:**\
URL: php/data_manager.php\
metódus: POST\
body:
```
{
    "manage" : ... ,
    "action" : ... ,
    ... adatok ...
}
```
Az elérhető manage és action műveletmegadások és a hozzájuk tartozó várt adatok megtalálhatóak
[ebben a segédletben](https://docs.google.com/spreadsheets/d/1HAkm7S7Lovg0MLZaVrUpa_84gKn3DRy2awOW5ynNdwM/edit?usp=sharing).

## Tesztek
A tesztek futtatása előtt a [test](test/) mappába kell belépni és futtatni kell az alábbi parancsot: `npm install` \
A tesztek futtatásához az alábbi parancsot kell futtatni: `npm test`
