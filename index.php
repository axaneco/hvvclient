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
$stat = get_station_keys($gfurl, $stations, $username, $password);

// create gti:departureList request
if ($both_dirs) { // both directions
    $dl_xml = create_gti_DLRequest ($stat["dep"][0], $stat["dep"][1], $refday, $reftime, $maxlist, $maxtimeoffset);
} else {
    $dl_xml = create_gti_DLRequest ($stat["dep"][0], $stat["dep"][1], $refday, $reftime, $maxlist, $maxtimeoffset, $stat["via"][1]);
}

// get departure list for dep
$res = call_gti_api($gfurl, 'departureList', $dl_xml, $username, $password);

// read result as xml
$resultxml = simplexml_load_string($res);  

echo "<span style='font-family:sans-serif;'>";
echo "<span style='font-size:16px;'>\n";

// write results
echo "<a href='https://geofox.hvv.de/jsf/home.seam' target='_blank'>";
echo "<img src='https://www.hvv.de/images/logo_hvv_110x25.png' alt='Mit dem HVV zu uns' height='25' border='0'/>";
echo "</a>\n";

// print departure list
if ($both_dirs) { // both directions
    echo "<br><br>Nächste Abfahrten ab " . $stat["dep"][0] . " (ungefiltert):<br><br>\n";
} else {
    echo "<br><br>Nächste Abfahrten ab " . $stat["dep"][0] . " Richtung " . $stat["via"][0] . ":<br><br>\n";
}
print_departures($resultxml, $maxlist, TRUE); // boole parameter: table display yes/no

echo "</span></span>\n";

?>