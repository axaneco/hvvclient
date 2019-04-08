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

include ('inc/hvvc_vars.php'); // vars + station query xml
include ('inc/hvvc_functions.php'); //functions

// get actual station IDs from GeoFox
$stat = get_station_keys($stations, $username, $password);

// create gti:departureList request
if ($test_flag) { // test is unfiltered, both directions
    $dl_xml = create_gti_DLRequest ($stat["ms"][0], $stat["ms"][1], $refday, $reftime, $maxlist, $maxtimeoffset);
} else {
    $dl_xml = create_gti_DLRequest ($stat["ms"][0], $stat["ms"][1], $refday, $reftime, $maxlist, $maxtimeoffset, $stat["bf"][1]);
}

// get departure list for Mensingstraße
$res = call_gti_api('departureList', $dl_xml, $username, $password);

// read result as xml
$resultxml = simplexml_load_string($res);  

// write results
echo "<br><a href='https://www.hvv.de/linking-service/show/1b0df0dc1be949e081b37ac02c92c0cf' target='_blank'>";
echo "<img src='https://www.hvv.de/images/logo_hvv_110x25.png' alt='Mit dem HVV zu uns' height='25' border='0'/>";
if ($test_flag) { // test is unfiltered, both directions
    echo "</a><br><br>Nächste Busse (ungefiltert):<br><br>";
} else {
    echo "</a><br><br>Nächste Busse in die Stadt:<br><br>";
}

echo "</a><br><br>Nächste Busse:<br><br>";

// print departure list
print_departures($resultxml, $maxlist);

?>