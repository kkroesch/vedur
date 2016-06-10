# -*- coding: utf-8 -*-

import json
import re

coord_regex = re.compile('\((?P<lat>[0-9,]+), (?P<lng>[0-9,]+)\)')

f = open('stations.txt', 'r')

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
        station['coords'] = ';'.join(m[0])

	print json.dumps(station)
f.close()
