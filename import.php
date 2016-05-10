#!/usr/bin/env php
<?php namespace ch\kroesch\meteo;

require('VedurParser.php');

$parser = new VedurParser($base_url='test/fixture.xml');

$fp = fopen('stations.csv', 'r');
while (($row = fgetcsv($fp, 1000, ';')) !== FALSE) {
    $vedur_id = $row[4];
    $intern_id = $row[0];
    $obs = $parser->get_observations($vedur_id);
    $parser->write_csv($intern_id, $obs);
}
fclose($fp);
