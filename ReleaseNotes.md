# hvvclient Release Notes

## C2102.1

* reflect the changes in XML delivered by geofox (see 2102*xml data in devdata dir) regarding live info: live info criteria is now if a <delay> tag is delivered. If yes, we have live data, if no, it's timetable data.
* added POST/XML debugging features (and a debug switch) as network sniffing makes no longer sense due to https
* added version string, updated user agent string

## C2009.1

* switch to new Geofox GTI server

## C1911.1

* handle departures in the past
* added "dd" URL parameter to display delay in minutes

## C1904.4

* GeoFox URL production switch
* added "from" and "to" URL parameters

## C1904.2

* generalized departure and via stations 
* output as table option
* GeoFox URL now variable

## Initial upload

* initial version, API calls are working
