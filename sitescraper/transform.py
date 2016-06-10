# -*- coding: utf-8 -*-

import re
import csv

coord_regex = re.compile('\((?P<lat>[0-9,]+), (?P<lng>[0-9,]+)\)')

f = open('stations.txt', 'r')

stations = []
station = {}

for line in f:
    if line.startswith('Stöðvanúmer'):
        station['origin'] = int(line.split(':')[1])
    if line.startswith('WMO'):
        station['wmo'] = int(line.split(':')[1])
    if line.startswith('Skammstöfun'):
        station['short'] = line.split(':')[1].strip()
    if line.startswith('Nafn'):
        station['name'] = line.split(':')[1].strip()
    if line.startswith('Staðsetning'):
        m = coord_regex.findall(line)
        station['lat'] = m[0][0]
        station['lng'] = m[0][1]

    if line.strip() == '':
        stations.append(station)
        station = {}

f.close()

id = 4000

with open('../stations.csv', 'w') as csvfile:
    fieldnames = ['id', 'origin', 'wmo', 'short', 'name', 'lat', 'lng']
    writer = csv.DictWriter(csvfile, fieldnames=fieldnames, delimiter=';')

    writer.writeheader()
    for station in stations:
        id += 1
        if id % 10 == 0:
            id += 1
        station['id'] = str(id).zfill(6)
        writer.writerow(station)
