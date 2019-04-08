<?php

/*
 * hvvclient
 * implements checkName and departureList calls to the the GEOFOX Thin Interface (GTI)
 * a Passenger Information System for the Hamburger Verkehrsverbund (HVV)
 * for details see sections 2.2 and 2.4 of GTI Handbuch V35.1 
 * https://api-test.geofox.de/gti/doc/html/GTIHandbuch_p.html
 * 
 * @author axaneco
 * 
 */

$test_flag = TRUE;

// stations array
// first station: departures
// second station: via filter for direction control
$stations = array(  "ms" => array( "Bf. Harburg", "XXX"), 
                    "bf" => array( "Bf. Harburg", "XXX") 
);

// date
$refday = date("d.m.Y");
$reftime = date("H:i");

// credentials for geofox api
// to be requested via https://www.hvv.de/de/fahrplaene/abruf-fahrplaninfos/datenabruf/
$username = 'your_username';
$password = 'your_password';

//display paramater
$maxlist = 30; // max list items for query results
$maxtimeoffset = 720; // results for max 12 hours in future

?>