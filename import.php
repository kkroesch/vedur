#!/usr/bin/env php
<?php namespace ch\kroesch\meteo;

require('VedurParser.php');

$parser = new VedurParser();
$obs = $parser->get_observations(1);
$parser->write_csv($obs);
