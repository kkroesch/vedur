# -*- coding: utf-8 -*-

import re
import csv

coord_regex = re.compile('\((?P<lat>[0-9,]+), (?P<lng>[0-9,]+)\)')

""" Character substitution for Icelandic letters """
convert = {
    'Á'.decode('utf8'): 'A',
    'Å'.decode('utf8'): 'A',
    'Æ'.decode('utf8'): 'AE',
    'Ó'.decode('utf8'): 'O',
    'Ö'.decode('utf8'): 'O',
    'Ø'.decode('utf8'): 'O',
    'Ý'.decode('utf8'): 'Y',
    'Þ'.decode('utf8'): 'p',
    'á'.decode('utf8'): 'a',
    'ã'.decode('utf8'): 'a',
    'ä'.decode('utf8'): 'a',
    'å'.decode('utf8'): 'a',
    'æ'.decode('utf8'): 'ae',
    'è'.decode('utf8'): 'e',
    'é'.decode('utf8'): 'e',
    'ë'.decode('utf8'): 'e',
    'í'.decode('utf8'): 'i',
    'ð'.decode('utf8'): 'd',
    'ó'.decode('utf8'): 'o',
    'ö'.decode('utf8'): 'o',
    'ø'.decode('utf8'): 'o',
    'ú'.decode('utf8'): 'u',
    'ü'.decode('utf8'): 'u'
}


def to_latin_char(text):
    return ''.join(map(lambda c: convert[c] if c in convert.keys() else c, text))


f = open('stations.txt', 'r')

stations = []
station = {}

for line in f:
    if line.startswith('Stöðvanúmer'):
        station['origin'] = int(line.split(':')[1])
    if line.startswith('WMO'):
        station['wmo'] = int(line.split(':')[1])
    if line.startswith('Skammstöfun'):
        station['latin'] = to_latin_char(line.split(':')[1].strip())
    if line.startswith('Nafn'):
        station['name'] = line.split(':')[1].strip()
    if line.startswith('Hæð'):
        station['elevation'] = int(line.split(':')[1])
    if line.startswith('Staðsetning'):
        m = coord_regex.findall(line)
        station['lat'] = m[0][0].replace(',', '.')
        station['lng'] = m[0][1].replace(',', '.')
    if line.strip() == '':
        stations.append(station)
        station = {}

f.close()

id = 4000

with open('../stations.csv', 'w') as csvfile:
    fieldnames = ['wmo', 'origin', 'name', 'latin', 'elevation', 'lat', 'lng']
    writer = csv.DictWriter(csvfile, fieldnames=fieldnames, delimiter=';')

    writer.writeheader()
    for station in stations:
        id += 1
        if id % 10 == 0:
            id += 1
        station['id'] = id
        writer.writerow(station)
