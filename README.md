# geoliteip

Geoliteip is a php tool to use and manage maxmind GeoLite2 free databases in mmdb format.

## Features

* use 3 free dbs as asn, country, city.
* change db on the fly without re-instanciate.
* input ip list from plain text file.
* output as array, json, csv.
* update dbs on the fly.

### Pro

* flexibility with adapter pattern.
* documented and tested with full coverage.
* overriding adapters methods can be done to change behaviour.

### Cons

* do not use non free GeoLite dbs (but can be extended to do the job).

## Dependencies

* geoip2/geoip2
* guzzlehttp/guzzle

## Testing & Coverage

* require xdebug to enable coverage.
* tested with phpunit.
* tests all passed with php version >= 7.0.
* /!\\ running tests makes real db update from maxmind, do not abuse because of 503.
* TEST_ENABLE constant let you enable or disable one or more test case.

## Composer 

### Facilities

* run test (pass tests and generate coverage in html format)
* run checklint (linter checker).
* run lint (lint the code).

### Integration

To play with GeoLiteIp from your own projects, adjust your composer.json as below.

For information '...' is just a continuity symbol, pls do not copy as it.

* Add as below the required packages
``` json
"require": {
    ...
    "geoip2/geoip2": "~2.0",
    "guzzlehttp/guzzle": "^6.3",
    "PierInfor/GeoLiteIp": "dev-master"
}	
```

* Add as below the package definition
``` json
"repositories": [
    {
        "type": "package",
        "package": {
            "name": "PierInfor/GeoLiteIp",
            "version": "dev-master",
            "source": {
                "url": "https://github.com/pierre-fromager/geoliteip.git",
                "type": "git",
                "reference": "origin/master"
            },
            "autoload": {
                "psr-0": {
                    "PierInfor\\GeoLiteIp": "src"
                }
            }
        }
    }
]
```

* Append a new entry in autoload as below
``` json
"autoload": {
    "psr-4": {
        ...
        "PierInfor\\": "vendor/PierInfor/GeoLiteIp/src/"
    }
}
```

I know this is not fair, but this is the way to deploy a package without packagist.

#### Check

Running 
``` bash
composer show 
```

Should return (maybe versions change)
``` bash
composer/ca-bundle         1.2.4                    Lets you find a path to the system CA bundle, and includes a fallback...
geoip2/geoip2              v2.9.0                   MaxMind GeoIP2 PHP API
guzzlehttp/guzzle          6.3.3                    Guzzle is a PHP HTTP client library
guzzlehttp/promises        v1.3.1                   Guzzle promises library
guzzlehttp/psr7            1.6.1                    PSR-7 message implementation that also provides common utility methods
maxmind-db/reader          v1.4.1                   MaxMind DB Reader API
maxmind/web-service-common v0.5.0                   Internal MaxMind Web Service API
PierInfor/GeoLiteIp        dev-master origin/master
psr/http-message           1.0.1                    Common interface for HTTP messages
ralouphie/getallheaders    3.0.3                    A polyfill for getallheaders.

```

#### Dummy app

From the root of the project, create a src folder then a file app.php inside.

Copy paste the code below in app.php

``` php
<?php

namespace Company\MyApp;

use PierInfor\GeoLiteIp;

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('date.timezone', 'Europe/Paris');
ini_set('register_globals', 0);
ini_set('opcache.enable', 0);

if (function_exists('opcache_get_configuration')) {
    ini_set('opcache.memory_consumption', 128);
    ini_set('opcache.load_comments', true);
}

$there = __DIR__;
$loader = require 'vendor/autoload.php';
$geoInst = new GeoLiteIp();
$forceUpdate = false;
echo 'Begin update' . "\n";
$geoInst->setAdapter(GeoLiteIp::ADAPTER_ASN)->update($forceUpdate);
$geoInst->setAdapter(GeoLiteIp::ADAPTER_COUNTRY)->update($forceUpdate);
$geoInst->setAdapter(GeoLiteIp::ADAPTER_CITY)->update($forceUpdate);
echo 'End update' . "\n";
$ipv6ToCheck = '2a01:e35:2422:4d60:2ad2:44ff:fe06:2983';
$ipv4ToCheck = '82.66.36.214';
echo 'Scanning ipv6 ' . $ipv6ToCheck . "\n";
echo 'Scanning ipv4 ' . $ipv4ToCheck . "\n";
$geoInst->addIp($ipv6ToCheck);
$geoInst->addIp($ipv4ToCheck);
echo $geoInst->process()->toJson();
```

From the root of the project running
``` bash
php ./src/app.php
```
Should be immediate with no errors and display messages as below
``` bash
Begin update 
End update 
Scanning ipv6 2a01:e35:2422:4d60:2ad2:44ff:fe06:2983
Scanning ipv4 82.66.36.214
[
    {
        "ip": "2a01:e35:2422:4d60:2ad2:44ff:fe06:2983",
        "country": "FR",
        "city": "?",
        "lon": 48.85,
        "lat": 2.5,
        "radius": 10
    },
    {
        "ip": "82.66.36.214",
        "country": "FR",
        "city": "Aubervilliers",
        "lon": 48.9163,
        "lat": 2.3869,
        "radius": 5
    }
]
```
Changing forceUpdate to true will force update with the same display but with an elapsed time bit longer.

## Todo

* Implement input arguments to read stdin.
* Remove the db files from assets and run update with Composer PostInstall scripts.
* Find a good php documentation generator...

