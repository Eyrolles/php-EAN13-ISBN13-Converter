<?php

require('ISBN13.class.php');

$isbn = new ISBN13(array(978, 9940, 26, 123));

echo $isbn.PHP_EOL;
echo $isbn->getEAN13Value().PHP_EOL;
echo $isbn->isValid() ? 'true' : 'false';
echo PHP_EOL;

echo ISBN13::EAN13ToISBN13('9789940261238');


echo PHP_EOL.PHP_EOL;


$isbn2 = new ISBN13('978-92-9364-684-3');

echo $isbn2.PHP_EOL;
echo $isbn2->getEAN13Value().PHP_EOL;
echo $isbn2->isValid() ? 'true' : 'false';
echo PHP_EOL;
echo ISBN13::EAN13ToISBN13('9789940261238'); // Would throw Exception if the EAN has a wrong length.


echo PHP_EOL.PHP_EOL;


$isbn3 = new ISBN13('978-92-9364-68'); // Will throw Exception (length is 12 and not 13).
