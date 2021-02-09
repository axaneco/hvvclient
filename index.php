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

include ('inc/hvvc_vars.php'); // vars + station query xml
include ('inc/hvvc_functions.php'); //functions
// parameter "from" given in URL?
if ($_GET["from"]) {
    $both_dirs = TRUE;
    $stations["dep"][0] = $_GET["from"];
}
// parameter "to" given in URL?
if ($_GET["to"]) {
    $both_dirs = FALSE;
    $stations["via"][0] = $_GET["to"];
}

// parameter "dd" given in URL?
if ($_GET["dd"]) {
    $ddelay = $_GET["dd"];
}

// get actual station IDs from GeoFox
$stat = get_station_keys($gfurl, $stations, $username, $password);

// create gti:departureList request
if ($both_dirs) { // both directions
    $dl_xml = create_gti_DLRequest($stat["dep"][0], $stat["dep"][1], $refday, $reftime, $maxlist, $maxtimeoffset);
} else {
    $dl_xml = create_gti_DLRequest($stat["dep"][0], $stat["dep"][1], $refday, $reftime, $maxlist, $maxtimeoffset, $stat["via"][1]);
}

// get departure list for dep
$res = call_gti_api($gfurl, 'departureList', $dl_xml, $username, $password);

// read result as xml
$requestxml = simplexml_load_string($dl_xml);
$resultxml = simplexml_load_string($res);

// Debugging XML
if ($hvvc_debug) {
    echo "<p style=\"font-family:'Courier New'\">";
    echo "XML start<br>";
    echo "Request XML:<br>";
    echo "<pre>";
    echo htmlentities($requestxml->asXML());
    echo "</pre></p>";
    echo "<p style=\"font-family:'Courier New'\">";
    echo "Response XML:<br>";
    echo "<pre>";
    $dom = new DOMDocument('1.0', 'utf-8');
    $dom->formatOutput = true;
    $xml = $resultxml->asXML();
    $dom->loadXML($xml);
    $xml_pretty = $dom->saveXML();
    echo htmlentities($xml_pretty);
    echo "</pre>";
    echo "<p style=\"font-family:'Courier New'\">";
    echo "XML end</p>";
}

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
print_departures($resultxml, $maxlist, TRUE, $ddelay); // boole parameter: table display yes/no

echo "</span></span>\n";
?>