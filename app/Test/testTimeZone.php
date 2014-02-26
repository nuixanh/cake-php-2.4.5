<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/26/14
 * Time: 6:38 PM
 * To change this template use File | Settings | File Templates.
 */
//$nowUtc = new DateTime('2014-02-26 18:33:21',  new DateTimeZone('Asia/Saigon'));
//echo '$nowUtc'.PHP_EOL;
//echo date_format($nowUtc, "Y-m-d H:i:s").PHP_EOL;
//var_dump($nowUtc);
//
//$nowUtc->setTimezone( new DateTimeZone('Australia/Sydney') );
//echo '$nowUtc->setTimezone( new DateTimeZone( \'Australia/Sydney\' ) )'.PHP_EOL;
//var_dump($nowUtc);
//echo date_format($nowUtc, "Y-m-d H:i:s").PHP_EOL;

for ($i = -11; $i <= 13; $i++) {
    echo $i . "\n";
    echo timezone_name_from_abbr(null, $i * 3600, true) . "------\n";
    echo timezone_name_from_abbr(null, $i * 3600, false) . "+++\n";
}

//$offset = -10;
//$tz = timezone_name_from_abbr(null, $offset * 3600, true);
//if($tz === false) $tz = timezone_name_from_abbr(null, $offset * 3600, false);
//echo $tz;
