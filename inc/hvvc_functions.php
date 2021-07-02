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
// get all the station keys
function get_station_keys($gfurl, $stations, $username, $password) { // stations, username and password come from the vars.php
    for ($x = 0, $keys = array_keys($stations), $c = count($keys); $x < $c; $x++) {
        if ($test_flag) {
            echo($keys[$x] . ": " . $stations[$keys[$x]][0] . $stations[$keys[$x]][1] . "<br>");
        }
        // create the gti:checkName request
        $cn_xml = create_gti_CNRequest($stations[$keys[$x]][0]);
        // call the gti:checkName API function
        $res = call_gti_api($gfurl, 'checkName', $cn_xml, $username, $password);
        // set station key in stations array
        $resultxml = simplexml_load_string($res);
        $stations[$keys[$x]][1] = $resultxml->results->id;
    }
    return $stations;
}

// create xml gti:CNRequest (checkName xml body)
function create_gti_CNRequest($stname) {
    // xml header
    $dom = new DOMDocument('1.0', 'utf-8');
    $dom->xmlStandalone = TRUE;
    $dom->formatOutput = TRUE;

    //set request type with attributes
    $root = $dom->createElement("gti:CNRequest");
    $root->setAttribute("xmlns:gti", "http://www.geofox.de/schema/geofoxThinInterface");

    // define structure and set parameters
    $dom->appendChild($root);
    $root->appendChild($n_theName = $dom->createElement("theName"));
    $n_theName->appendChild($n_name = $dom->createElement("name", $stname));
    $n_theName->appendChild($n_type = $dom->createElement("type", "STATION"));
    $root->appendChild($n_maxList = $dom->createElement("maxList", "5"));
    $root->appendChild($n_coordinateType = $dom->createElement("coordinateType", "EPSG_4326"));

    return $dom->saveXML();
}

// create xml gti:DLRequest (departureList xml body)
function create_gti_DLRequest($stname, $stid, $refday, $reftime, $maxlist, $maxtimeoffset, $filterid = FALSE) {
    // xml header
    $dom = new DOMDocument('1.0', 'utf-8');
    $dom->xmlStandalone = TRUE;
    $dom->formatOutput = TRUE;

    //set request type with attributes
    $root = $dom->createElement("gti:DLRequest");
    $root->setAttribute("xmlns:gti", "http://www.geofox.de/schema/geofoxThinInterface");

    // define structure and set parameters
    $dom->appendChild($root);
    //$root->appendChild($n_version = $dom->createElement("version", "35"));
    $root->appendChild($n_version = $dom->createElement("version", "38"));
    $root->appendChild($n_station = $dom->createElement("station"));
    $n_station->appendChild($n_name = $dom->createElement("name", $stname));
    $n_station->appendChild($n_city = $dom->createElement("city", "Hamburg"));
    $n_station->appendChild($n_id = $dom->createElement("id", $stid));
    $n_station->appendChild($n_type = $dom->createElement("type", "STATION"));
    $root->appendChild($n_time = $dom->createElement("time"));
    $n_time->appendChild($n_date = $dom->createElement("date", $refday));
    $n_time->appendChild($n_stime = $dom->createElement("time", $reftime));
    $root->appendChild($n_maxlist = $dom->createElement("maxList", $maxlist));
    if ($filterid) {
        $root->appendChild($n_filter = $dom->createElement("filter"));
        $n_filter->appendChild($n_stationids = $dom->createElement("stationIDs", $filterid));
    }
    $root->appendChild($n_maxtimeoffset = $dom->createElement("maxTimeOffset", $maxtimeoffset));
    $root->appendChild($n_userealtime = $dom->createElement("useRealtime", "true"));

    return $dom->saveXML();
}

// Call GTI API via cURL
function call_gti_api($gfurl, $gfunc, $http_body, $username, $password) { // gfunc here either checkName or departureList
    global $hvvc_debug;
    global $hvvc_version;
    // sign the api request
    $bin_signature = hash_hmac("sha1", $http_body, $password, true);
    $signature = base64_encode($bin_signature);
    // make UUID for X-TraceId
    $traceid = v4();
    // create API call URI
    $ch = curl_init($gfurl . $gfunc);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $http_body);
    // Set HTTP Header for POST request 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/xml',
        'Content-Type: application/xml;charset=UTF-8',
        'geofox-auth-type: HmacSHA1',
        'geofox-auth-user: ' . $username,
        'geofox-auth-signature: ' . $signature,
        'User-Agent: ax-hvvclient ' . $hvvc_version,
        'X-Platform: web',
        'X-TraceId: ' . $traceid,
        'Content-Length: ' . strlen($http_body))
    );
    // Submit the POST request
    $resultxml = curl_exec($ch);
    if ($hvvc_debug) {
        echo "<p style=\"font-family:'Courier New'\">cURL start<br>";
        $cURL = curl_getinfo ($ch,  CURLINFO_HEADER_OUT); 
        //print_r($cURL);
        echo nl2br($cURL);
        echo "<br>cURL end</p>";
    }
    curl_close($ch);
    return $resultxml;
}

// tableswitch
function tab($table, $alignment = FALSE) {
    if ($table) {
        echo "</td>";
        if ($alignment) {
            echo "<td align=$alignment>";
        } else {
            echo "<td>";
        }
    }
}

// check disturbances
function check_disturbances($resultxml, $i, $tdelay, $table) {
    //$rt = $resultxml->departures[$i]->attributes->types[0]; // is either REALTIME or missing
    $rt = $resultxml->departures[$i]->delay; // is either 0, >0 or missing
    $tj = $resultxml->departures[$i]->attributes->types[1]; // is either ACCURATE or TRAFFIC_JAM
    $dis = FALSE; // no disturbance initially

    if ($table) {
        echo "<td>";
    }
    // no live info -> blank symbol
    if ($rt[0] === NULL) {
        echo "<img src='assets/images/empty.png' height='14' border='0'/>";
        $dis = TRUE;
    }
    // traffic jam -> black symbol
    if ($tj == 'TRAFFIC_JAM') {
        echo "<img src='assets/images/black.png' height='14' border='0'/>";
        $dis = TRUE;
    }
    // delay (without traffic jam) -> yellow symbol
    if ($tdelay > 0 && $dis == FALSE) {
        echo "<img src='assets/images/yellow.png' height='14' border='0'/>";
        $dis = TRUE;
    }
    $res = array("rt" => $rt, "dis" => $dis);
    return $res;
}

// sofort switch
function now($tdep, $table) {
    if ($tdep < 0) {
        echo ' ist abgefahren'; // bypass time garbage with departures in the past
    }
    if ($tdep == 0) {
        echo ' sofort';
    } else {
        if ($table) {
            echo ' &nbsp;&nbsp ';
        } else {
            echo ' in ';
        }
        echo $tdep . ' Minute';
        if ($tdep > 1) {
            echo 'n';
        } // minuten for 0, 2 - inf
    }
}

// print out the departure list
function print_departures($resultxml, $maxlist, $table = FALSE, $ddelay = FALSE) { // resultxml delivered by the GeoFox API, here: call_gti_api($gfunc, $http_body, $username, $password)
    if ($table) {
        echo "<table>\n";
    }
    if ($resultxml->returnCode == 'OK') {
        for ($i = 0; $i < $maxlist; $i++) {
            $id = $resultxml->departures[$i]->line->id;         // get bus id
            if ($id) { // go on only if there's a result in the xml 
                if ($table) {
                    echo "<tr>";
                }
                $toffset = $resultxml->departures[$i]->timeOffset;  // departure time offset in minutes from query 
                $tdelay = round(($resultxml->departures[$i]->delay) / 60, 0, PHP_ROUND_HALF_UP);  // planned/known delay, if any, converted to minutes
                $tdep = $toffset + $tdelay; // estimated departure time including known delay
                $ex = $resultxml->departures[$i]->extra; // extra trip
                $no = $resultxml->departures[$i]->cancelled; // trip cancelled
                // check (and display as icon) disturbances
                $dst = check_disturbances($resultxml, $i, $tdelay, $table);
                // live info and everything ok -> green icon
                //if ($dst["rt"] == 'REALTIME' && $dst["dis"] == FALSE) {
                if ($dst["rt"] == '0' && $dst["dis"] == FALSE) {
                    echo "<img src='assets/images/green.png' height='14' border='0'/>";
                }
                tab($table, 'center');
                echo "<img src='https://cloud.geofox.de/icon/line?height=14&amp;lineKey=" . $id . "'> ";   // line icon
                tab($table);
                if ($no) {
                    echo '<s>';
                } // strike if no journey
                echo $resultxml->departures[$i]->line->direction . ' '; // line direction 
                if ($no) {
                    echo '</s>';
                }
                tab($table, 'right');
                if ($no) {
                    echo '<s>';
                } // strike if no journey
                now($tdep, $table); // "sofort" switch
                if ($tdelay > 0 && $ddelay) { // print delay in minutes if present and display switch is on
                    echo ' (+' . $tdelay . ')';
                }
                if ($no) {
                    echo '</s>';
                }
                tab($table);
                if ($no) {
                    echo '<font color="red"> FÄLLT AUS</font>';
                }
                if ($ex) {
                    echo ' (Verstärkerfahrt)';
                }
                if ($table) {
                    echo "</td></tr>\n";
                } else {
                    echo "<br>\n";
                }
            }
        }
        echo "</table>\n";
    } else {
        echo 'Fehler: GeoFox returned an error';
    }
}

/**
 * 
 * Generate v4 UUID
 * 
 * Version 4 UUIDs are pseudo-random.
 * 
 * @author Andrew Moore
 * @link http://www.php.net/manual/en/function.uniqid.php#94959
 * 
 * source https://github.com/macx/rfc-4122-uuid/blob/master/src/uuid.php
 * 
 */
function v4() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
