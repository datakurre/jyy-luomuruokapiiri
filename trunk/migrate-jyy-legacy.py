# -*- coding: utf-8 -*-
# """ Migrate legacy (2005) ruokapiiri database to new (2009-2010) ruokapiiri database """

# 1) Migrate asetukset.txt to settings.txt
output = open("settings.txt", "w")

output.write("""\
id#name#value
inc#str#str
##
""")

settings = {
  "toimituskulut": "charge",
  "tilauskopiot":  "email",
  "lomake_viesti": "form.instructions",
  "lomake_tila":   "form.enabled",
}

counter = 1
for line in [l for l in open("asetukset.txt").readlines()[3:] if l.strip()]:
    line = unicode(line, "latin-1").encode("utf-8")
    columns = line.strip().split("#")
    if columns[1] in settings.keys():
        migrated = [
            str(counter),           # id
            settings[columns[1]],   # name
            columns[2],             # value
        ]
        output.write("#".join(migrated) + "\n")
        counter += 1

output.close()


# 2) Migrate tuotteet.txt to catalog.txt
output = open("catalog.txt", "w")

output.write("""\
id#description#notes#ingredients#price#unit#producer#orderable#position
inc#str#str#str#str#str#str#int#int
########
""")

schema = "id#kuvaus#hinta#tuottaja#tilattavissa#sija".split("#")

for line in [l for l in open("tuotteet.txt").readlines()[3:] if l.strip()]:
    line = unicode(line, "latin-1").encode("utf-8")
    columns = line.strip().split("#")
    migrated = [
        columns[schema.index("id")],      # id
        columns[schema.index("kuvaus")],  # description
        "",                               # notes
        "",                               # ingredients
        columns[schema.index("hinta")].replace(",", "."), # price
        "pcs",                            # unit
        columns[schema.index("tuottaja")],# producer
        columns[schema.index("tilattavissa")], # orderable
        columns[schema.index("sija")],    # position
    ]
    output.write("#".join(migrated) + "\n")

output.close()


# 3) Migrate tilaukset.txt to orders.txt
output = open("orders.txt", "w")

output.write("""\
id#date#charge#name#phone#email#pickup#participate#notes#state
inc#str#str#str#str#str#str#int#str#int
#########
""")

schema = "id#pvm#toimituskulut#nimi#puhelin#sposti#nouto#jako#muuta#tila".split("#")

for line in [l for l in open("tilaukset.txt").readlines()[3:] if l.strip()]:
    line = unicode(line, "latin-1").encode("utf-8")
    columns = line.strip().split("#")
    parts = columns[schema.index("nimi")].split(" ")
    name = parts[-1] + " " + " ".join(parts[:-1])
    migrated = [
        columns[schema.index("id")],     # id
        columns[schema.index("pvm")],    # date
        columns[schema.index("toimituskulut")].replace(",", "."), # charge
        name,                            # name
        columns[schema.index("puhelin")],# phone
        columns[schema.index("sposti")], # email
        columns[schema.index("nouto")],  # pickup
        columns[schema.index("jako")],   # participate
        columns[schema.index("muuta")],  # notes
        "1",                             # state
    ]
    output.write("#".join(migrated) + "\n")

output.close()


# 4) Migrate erittelyt.txt to breakdowns.txt
output = open("breakdowns.txt", "w")

output.write("""\
id#order_id#description#producer#price#unit#quantity
inc#int#str#str#str#str#int
######
""")

schema = "id#tilaus#kuvaus#hinta#tuottaja#maara".split("#")

for line in [l for l in open("erittelyt.txt").readlines()[3:] if l.strip()]:
    line = unicode(line, "latin-1").encode("utf-8")
    columns = line.strip().split("#")
    migrated = [
        columns[schema.index("id")],        # id
        columns[schema.index("tilaus")],    # order_id
        columns[schema.index("kuvaus")],    # description
        columns[schema.index("tuottaja")],  # producer
        columns[schema.index("hinta")].replace(",", "."), # price
        "pcs",                              # unit
        columns[schema.index("maara")],     # quantity
    ]
    output.write("#".join(migrated) + "\n")

output.close()


# 5) Migrate tilastot_tilaukset.txt to stats_orders.txt
output = open("stats_orders.txt", "w")

output.write("""\
id#date#quantity#sum#charges
inc#str#int#str#str
####
""")

schema = "id#pvm#toimituskulut#summa#tilaukset".split("#")

for line in [l for l in open("tilastot_tilaukset.txt").readlines()[3:] if l.strip()]:
    line = unicode(line, "latin-1").encode("utf-8")
    columns = line.strip().split("#")
    migrated = [
        columns[schema.index("id")],        # id
        columns[schema.index("pvm")],       # date
        columns[schema.index("tilaukset")], # quantity
        columns[schema.index("summa")].replace(",", "."),         # sum
        columns[schema.index("toimituskulut")].replace(",", "."), # charges
    ]
    if "#".join(migrated).endswith("#0#0#0#0"):
        continue
    output.write("#".join(migrated) + "\n")

output.close()


# 6) Migrate tilastot_tuottajat.txt to stats_producers.txt
output = open("stats_producers.txt", "w")

output.write("""\
id#order_id#producer#sum
inc#int#str#str
###
""")

schema = "id#tilaus#tuottaja#summa".split("#")

for line in [l for l in open("tilastot_tuottajat.txt").readlines()[3:] if l.strip()]:
    line = unicode(line, "latin-1").encode("utf-8")
    columns = line.strip().split("#")
    migrated = [
        columns[schema.index("id")],       # id
        columns[schema.index("tilaus")],   # order_id
        columns[schema.index("tuottaja")], # producer
        columns[schema.index("summa")].replace(",", "."), # sum
    ]
    output.write("#".join(migrated) + "\n")

output.close()


# 7) Migrate tilastot_tuotteet.txt to stats_products.txt
output = open("stats_products.txt", "w")

output.write("""\
id#order_id#producer#description#quantity#unit#sum
inc#int#str#str#int#str#str
######
""")

schema = "id#tilaus#kuvaus#summa#maara".split("#")

for line in [l for l in open("tilastot_tuotteet.txt").readlines()[3:] if l.strip()]:
    line = unicode(line, "latin-1").encode("utf-8")
    columns = line.strip().split("#")
    migrated = [
        columns[schema.index("id")],     # id
        columns[schema.index("tilaus")], # order_id
        "",                              # producer
        columns[schema.index("kuvaus")], # description
        columns[schema.index("maara")],  # quantity
        "pcs",                           # unit
        columns[schema.index("summa")].replace(",", "."), # sum
    ]
    output.write("#".join(migrated) + "\n")

output.close()
