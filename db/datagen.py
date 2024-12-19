import random
import unicodedata

def remove_accents(input_str):
  nfkd_form = unicodedata.normalize('NFKD', input_str)
  return ''.join([c for c in nfkd_form if not unicodedata.combining(c)])

rows = int(input("New users : "))

new_users = ""

lastnames = ["Kovács", "Kis", "Nagy"]
firstnames = ["Pista", "Jóska", "István", "Gábor", "Gyula"]
emails = ["gmail.com", "protonmail.com", "citromail.hu"]

for i in range(0, rows):
    lastname = random.choice(lastnames)
    firstname = random.choice(firstnames)
    email = str.lower(remove_accents(lastname) + "@" + random.choice(emails))
    new_user = f"INSERT INTO `felhasznalo` (`VezetekNev`, `KeresztNev`, `Email`, `Jelszo`) VALUES ('{lastname}', '{firstname}', '{email}', '$2y$10$mPo.I8wSPUi.W5QaydbKIeGuR1SyKMPrIXm71XFoJZ7sHQbh/bGjO')"
    print(new_user)
