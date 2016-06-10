<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadFromUrl('http://www.vedur.is/vedur/stodvar');
$html = $dom->outerHtml;

$links = $dom->find('a');

$count = 0;
foreach ($links as $link) {
    if ($link->innerHtml == 'Uppl.') {
        print $link->getAttribute('href') . "\n";
        $count ++;
    }
}

echo "\n\nTotal: " . $count;

