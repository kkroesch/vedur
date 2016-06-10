<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;

// Collect station URLs
$dom = new Dom;
$dom->loadFromUrl('http://www.vedur.is/vedur/stodvar');
$html = $dom->outerHtml;

$links = $dom->find('a');

$station_urls = array();
foreach ($links as $link) {
    if ($link->innerHtml == 'Uppl.') {
        $station_urls[] = $link->getAttribute('href');
    }
}

foreach ($station_urls as $url) {
    $dom = new Dom;
    $dom->loadFromUrl('http://www.vedur.is' . $url);
    $html = $dom->outerHtml;

    $tds = $dom->find("td");

    $odd = true;
    foreach ($tds as $td) {
        $value = $td->innerHtml;
        if ($value == 'Nafn')
            print "\n";
        if ($odd)
            print $value . ': ';
        else
            print $value . "\n";
        $odd = ! $odd;
    }
}
