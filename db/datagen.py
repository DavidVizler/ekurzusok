import random
import unicodedata

def remove_accents(input_str):
    nfkd_form = unicodedata.normalize('NFKD', input_str)
    return ''.join([c for c in nfkd_form if not unicodedata.combining(c)])

rows = int(input("New users : "))

new_users = ""

lastnames = ["Kovács", "Kis", "Nagy", "Tóth", "Szabó", "Horváth", "Varga", "Kiss", "Molnár", "Németh", 
             "Balogh", "Farkas", "Lakatos", "Papp", "Takács", "Juhász", "Oláh", "Mészáros", "Simon",
             "Rácz", "Fekete", "Szilágyi", "Török", "Fehér", "Balázs", "Gál", "Szűcs", "Orsós", "Kocsis",
             "Fodor", "Pintér", "Szalai", "Sipos", "Magyar"]
firstnames = ["Pista", "Jóska", "István", "Gábor", "Gyula", "László", "Zoltán", "Péter", "Tamás",
              "Ferenc", "Zsolt", "Attila", "Sándor", "Tibor", "András", "Csaba", "Imre", "Balázs",
              "Lajos", "György", "Dávid", "Dániel", "Ádám", "Róbert", "Bence", "Gyula", "Mária",
              "Erzsébet", "Katalin", "Éva", "Ilona", "Anna", "Zsuzsanna", "Andrea", "Judit", "Ágnes",
              "Ildikó", "Margit", "Erika", "Krisztina", "Julianna", "Mónika", "Eszter", "Szilvia",
              "Gabriella", "Irén", "Edit", "Viktória", "Anita", "Magdolna", "Anikó"]
emails = ["gmail.com", "protonmail.com", "citromail.hu"]

password = "$2y$10$mPo.I8wSPUi.W5QaydbKIeGuR1SyKMPrIXm71XFoJZ7sHQbh/bGjO"
new_users = "INSERT INTO `felhasznalo` (`Email`, `VezetekNev`, `KeresztNev`, `Jelszo`) VALUES "

for i in range(0, rows):
    lastname = random.choice(lastnames)
    firstname = random.choice(firstnames)
    email = str.lower(remove_accents(lastname + firstname) + str(random.randint(1970, 2010)) + "@" + random.choice(emails))
    new_users += f"\n('{email}', '{lastname}', '{firstname}', '{password}')"
    if i+1 != rows:
        new_users += ", "

course_subjects = [
    "Matematika",
    "Földrajz",
    "Biológia",
    "Kémia",
    "Fizika",
    "Környezetismeret",
    "Frontend",
    "Backend",
    "Adatbázis Kelezés",
    "Digitális Kultúra",
    "Asztali Alk. Fejl.",
    "Mobil Alk. Fejl."
]

course_designs = [3, 1, 4, 4, 4, 5, 5, 5, 5, 5, 5, 5]

new_courses = "INSERT INTO `kurzus` (`FelhasznaloID`, `KurzusNev`, `Kod`, `Leiras`, `Design`, `Archivalt`) VALUES "
new_owners = "INSERT INTO `kurzustag` (`FelhasznaloID`, `KurzusID`, `Tanar`) VALUES "

course_amount = random.randint(rows, rows*2)
for i in range(1, course_amount+1):
    course_theme = random.randint(0, 11)
    course_name = course_subjects[course_theme] + " " + str(random.randint(9, 13)) + random.choice([".", "/", ""]) + "AaBbCcDdEeFfGgHh"[random.randint(0, 15)]
    course_owner = random.randint(1, rows)
    code = ""
    chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"
    desc = course_name + " online kurzus"
    for j in range(0, 10):
        code += chars[random.randint(0, 61)]

    new_courses += f"\n({course_owner}, '{course_name}', '{code}', '{desc}', {course_designs[course_theme]}, 0)"
    new_owners += f"({course_owner}, {i}, 1)"

    if i != course_amount:
        new_courses += ", "
        new_owners += ", "

f = open("./sql_command.txt", "w", encoding="utf-8")
f.write(new_users + ";\n" + new_courses + ";\n" + new_owners + ";")
f.close()

""" ALTER TABLE kurzus AUTO_INCREMENT = 1;
ALTER TABLE felhasznalo AUTO_INCREMENT = 1; """