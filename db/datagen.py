import random
import unicodedata

owner_chance = 5 # Hány % hogy lesz kurzusa
student_chance = 99 # Hány % hogy bent lesz kurzusban
teacher_chance = 0.1 # Hány % hogy tanár lesz a kurzusban
min_courses = 3 # Minimum hány kurzusa lesz, ha lesz (min 1)
max_courses = 10 # Maximum hány kurzusa lesz, ha lesz
min_memberships = 1 # Minimum hány kurzusnak tagja (min 1)
max_memberships = 20 # Maximum hány kurzusnak tagja

# Ékezeteltávolító függvény
def RemoveAccents(input_str):
    nfkd_form = unicodedata.normalize('NFKD', input_str)
    return ''.join([c for c in nfkd_form if not unicodedata.combining(c)])

# Kurzus kód generátor
chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"
def CodeGen():
    code = ""
    for i in range(0, 10):
        code += chars[random.randint(0, 61)]
    return code

user_courses = []

# Vezetéknevvek
lastnames = ["Kovács", "Kis", "Nagy", "Tóth", "Szabó", "Horváth", "Varga", "Kiss", "Molnár", "Németh", 
             "Balogh", "Farkas", "Lakatos", "Papp", "Takács", "Juhász", "Oláh", "Mészáros", "Simon",
             "Rácz", "Fekete", "Szilágyi", "Török", "Fehér", "Balázs", "Gál", "Szűcs", "Orsós", "Kocsis",
             "Fodor", "Pintér", "Szalai", "Sipos", "Magyar", "Blank", "Sas", "Tekes", "Dudás"]

# Keresztnevek
firstnames = ["István", "Gábor", "Gyula", "László", "Zoltán", "Péter", "Tamás",
              "Ferenc", "Zsolt", "Attila", "Sándor", "Tibor", "András", "Csaba", "Imre", "Balázs",
              "Lajos", "György", "Dávid", "Dániel", "Ádám", "Róbert", "Bence", "Gyula", "Mária",
              "Erzsébet", "Katalin", "Éva", "Ilona", "Anna", "Zsuzsanna", "Andrea", "Judit", "Ágnes",
              "Ildikó", "Margit", "Erika", "Krisztina", "Julianna", "Mónika", "Eszter", "Szilvia",
              "Gabriella", "Irén", "Edit", "Viktória", "Anita", "Magdolna", "Anikó", "Máté"]

# E-mail szolgáltatók
email_providers = ["gmail.com", "protonmail.com", "tuta.io", "outlook.com", "citromail.hu"]

# Jelszó = Almafa123
password = "$2y$10$mPo.I8wSPUi.W5QaydbKIeGuR1SyKMPrIXm71XFoJZ7sHQbh/bGjO"

users = int(input("Uj felhasznalok szama : "))

new_users = "INSERT INTO users (email, firstname, lastname, password) VALUES "

# Felhasználók generálása
for user_id in range(1, users+1):
    lastname = random.choice(lastnames)
    firstname = random.choice(firstnames)
    email = str.lower(RemoveAccents(lastname + firstname) + str(random.randint(1970, 2010)) + "@" + random.choice(email_providers))

    new_users += f"\n('{email}', '{firstname}', '{lastname}', '{password}')"

    if user_id != users:
        new_users += ", "
    
# Kurzus tananyagok
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

# Kurzus tananyagokhoz tartozó dizájnok
course_designs = [3, 1, 4, 4, 4, 5, 5, 5, 5, 5, 5, 5]

# Generáláshoz szükséges adatok
course_id = 1
course_codes = []
new_courses = "INSERT INTO courses (name, description, code, design_id, archived) VALUES "
new_owners = "INSERT INTO memberships (user_id, course_id, role) VALUES "

# Felhasználók kurzusainak generálása
for user_id in range(1, users+1):
    # Lesznek kurzusai a felhasználónak?
    if random.randint(1, round(100/owner_chance)) == 1:
        # Egy felhasználóhoz tartozó kurzusok számának meghatározása
        owned_courses = random.randint(min_courses, max_courses)
        # Egy felhasználóhoz tartozó kurzusok generálása
        for c in range(0, owned_courses):
            if course_id != 1:
                new_courses += ", "
                new_owners += ", "
            # Tantárgy kiválasztása
            course_theme = random.randint(0, 11)
            # Név generálása
            # Pl. Matematika 13/C vagy Földrajz 12.h
            course_name = course_subjects[course_theme] + " " + str(random.randint(9, 13)) + random.choice([".", "/", ""]) + "AaBbCcDdEeFfGgHh"[random.randint(0, 15)]
            design = course_designs[course_theme]
            desc = course_name + " online kurzus"
            # Kód generálása
            code = CodeGen()
            while code in course_codes:
                code = CodeGen()
            course_codes.append(code)

            new_courses += f"\n('{course_name}', '{desc}', '{code}', {design}, 0)"
            new_owners += f"({user_id}, {course_id}, 3)"

            user_courses.append([user_id, course_id])                
            
            course_id += 1

new_members = "INSERT INTO memberships (user_id, course_id, role) VALUES "

memberships = 0

# Tagok felvétele
for user_id in range(1, users+1):
    # Benne lesz-e valamelyik kurzusban
    if random.randint(1, round(100/student_chance)) == 1:
        # Egy felhasználó által felvett kurzusok számának meghatározása
        if (max_memberships > course_id):
            max_memberships = course_id-1
        courses_memberships = random.randint(min_memberships, max_memberships)
        # Egy felhasználóhoz tartozó tagságok generálása
        for c in range(0, courses_memberships):
            # Kurzus választása
            course = random.randint(1, course_id-1)
            # Benne van-e a kurzusban a felhasználó
            if [user_id, course] not in user_courses:
                if memberships != 0:
                    new_members += ", "
                memberships += 1
                # Tanár lesz-e
                if random.randint(1, round(100/teacher_chance)) == 1:
                    teacher = 2
                else:
                    teacher = 1

                user_courses.append([user_id, course])
                new_members += f"({user_id}, {course}, {teacher})"

db_reset = "DELETE FROM users WHERE 1; DELETE FROM courses WHERE 1; ALTER TABLE users AUTO_INCREMENT = 1; ALTER TABLE courses AUTO_INCREMENT = 1; ALTER TABLE memberships AUTO_INCREMENT = 1;\n"

f = open("./sql_command.txt", "w", encoding="utf-8")
f.write(db_reset + new_users + ";\n" + new_courses + ";\n" + new_owners + ";\n" + new_members + ";")
f.close()

print(f"{users} új felhasználó és {course_id-1} kurzus létrehozva!")