# hvvclient
A live departure board for HVV buses in Hamburg, Germany, implemented via REST calls to the GeoFox API GTI

## Getting Started

### Prerequisites

You'll need a webserver with PHP support, plus curl and the php-curl-module, for Ubuntu:
```
sudo apt-install php-curl
```
Since the project uses the Geofox Thin Interface (GTI), which requires registration, you'll have to apply for API access, see [HVV page](https://www.hvv.de/de/fahrplaene/abruf-fahrplaninfos/datenabruf).

### Installing

Install the ```assets``` and ```inc``` folders plus ```index.php``` to some directory on your machine where your web server can find it. Fill in the access credentials for the Geofox API in ```inc/hvvc_vars.php``` with your data, start up the web server (if it's not runing already), and point your browser to the index.php. 
If all went OK, you'll see the live departures of buses running from bus station Mensingstrasse in Hamburg, Germany. You can - and should - change this later on, of course.

## Testing

The ```devdata``` directory contains sample XML files that demonstrate the communication of the REST interface.
The "request"-data is produced by the hvvclient, whereas the "response" data comes from the Geofox server.
The ```DLResponse.xml``` provided contains realtime data for buses that are on time, delayed, or in traffic jam, as well as plan data where no realtime data is available.

## Authors

* **axaneco** - *Initial work*

## License

This project is licensed under the GNU GPL V3 License - see the [LICENSE.md](LICENSE.md) file for details.
