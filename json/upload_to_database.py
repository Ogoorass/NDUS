#!/bin/env python3
import sys
import json
from unittest import result
import mysql.connector

try:
    file_name = sys.argv[1]
    name = file_name.split(".")[0]
except IndexError:
    print("File not specified!")
    exit(1)

try:
    with open(file_name, "r") as f:
        data = json.load(f)

except FileNotFoundError:
    print("File not found!")
    exit(1)

except json.JSONDecodeError:
    print("JSON decode error!")
    exit(1)

mydb = mysql.connector.connect(
    host="localhost", user="NDUS", password="DP#>#Ea9,Z3;-4d", database="NDUS"
)

mycursor = mydb.cursor()


# insert data
sql = "INSERT INTO lista (id, nazwa, typ) VALUES (NULL, %s, %s)"
val = (name, 0)
mycursor.execute(sql, val)

mydb.commit()

print(mycursor.rowcount, "record inserted.")


# check id
sql = "SELECT id FROM lista WHERE nazwa = %s"
val = (name,)
mycursor.execute(sql, val)

result = mycursor.fetchall()

id_listy = result[0][0]


for row in data:
    sql = "INSERT INTO pytania (id, id_listy, pytanie, odpowiedź) VALUES (NULL, %s, %s, %s)"
    val = (id_listy, row["p"], row["n"])
    mycursor.execute(sql, val)
    mydb.commit()

print("done")
