<?php
require __DIR__ . '/vendor/autoload.php';

$sainsburys_cmd = new Commando\Command();
$sainsburys_cmd
  // Define a boolean flag "-p" a.k.a. "--plainjson"
  ->option('p')
    ->aka('plainjson')
    ->describedAs('Use plain json instead of human readable.')
    ->boolean()
  // Define a boolean flag "-t" a.k.a. "--texterrors"
  ->option('t')
    ->aka('texterrors')
    ->describedAs('Display errors as plain text instead of json.')
    ->boolean()
  // Define a boolean flag "-d" a.k.a. "--descriptiontext"
  ->option('d')
    ->aka('descriptiontext')
    ->describedAs('Get the product description from the page text instead of the meta tags.')
    ->boolean()
  // Define a boolean flag "-f" a.k.a. "--fileget"
  ->option('f')
    ->aka('fileget')
    ->describedAs('Use PHP file_get_contents instead of CURL.')
    ->boolean();
//Create scraper and display results
$scraper = new Sainsburys\Scraper($sainsburys_cmd['fileget'],$sainsburys_cmd['texterrors'],$sainsburys_cmd['plainjson'],$sainsburys_cmd['descriptiontext']);
echo $scraper->scrape(), PHP_EOL;