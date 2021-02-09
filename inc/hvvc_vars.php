<?php

/*
 * hvvclient
 * implements checkName and departureList calls to the the GEOFOX Thin Interface (GTI)
 * a Passenger Information System for the Hamburger Verkehrsverbund (HVV)
 * for details see sections 2.2 and 2.4 of GTI Handbuch V35.1 
 * https://gti.geofox.de/html/GTIHandbuch_p.html
 * 
 * @author axaneco
 * 
 */

// Version string
$hvvc_version = 'C2102.1A';
// debugging mode
$hvvc_debug = FALSE;
// geofox API url
$gfurl = 'https://gti.geofox.de/gti/public/';

// stations array
// first station: departures
// second station: via filter for direction control
// XXX filled by get_station_keys() with HVV station codes
$stations = array(  "dep" => array( "Hamburg Hbf", "XXX"), 
                    "via" => array( "", "XXX") 
);

// date
$refday = date("d.m.Y");
$reftime = date("H:i");

//display paramater
$maxlist = 30; // max list items for query results
$maxtimeoffset = 720; // results for max 12 hours in future

// replace this by your data or...
$username = 'username';
$password = 'password';

// ...include the data from an extra file
include 'credentials.php';

?>