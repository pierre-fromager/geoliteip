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
* run lint (lint the code).

### Integration

To play with GeoLiteIp from your own projects, adjust your composer.json as below.

For information '...' is just a continuity marker, pls do not copy as it.

Add as below :

* the required packages
``` json
"require": {
    ...
    "PierInfor/GeoLiteIp": "dev-master"
}	
```

* the package definition
``` json
"repositories": [
    ...
    {
        "type": "git",
        "url": "https://github.com/pierre-fromager/geoliteip.git"
    }
],
```

* the script to run update/download on post install (WIP...untested not ready yet)
``` json
"scripts": {
    ...
    "post-install-cmd": [
        "PierInfor\\GeoLite\\Installer::postInstall"
    ]
}
```

#### Dummy app

From the root of the project, create a src folder then a file app.php inside.

Copy paste the code below in app.php

``` php
<?php

namespace Company\MyApp;

use PierInfor\GeoLite\Ip;

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

require_once 'vendor/autoload.php';

$geoInst = new Ip();
$forceUpdate = false;
echo 'Begin update @' . microtime(true) . "\n";
$geoInst->setAdapter(Ip::ADAPTER_ASN)->update($forceUpdate);
$geoInst->setAdapter(Ip::ADAPTER_COUNTRY)->update($forceUpdate);
$geoInst->setAdapter(Ip::ADAPTER_CITY)->update($forceUpdate);
echo 'End   update @' . microtime(true) . "\n";
$ipv6ToCheck = '2a01:e35:2422:4d60:2ad2:44ff:fe06:2983';
$ipv4ToCheck = '82.66.36.214';
echo 'Scanning ipv6 ' . $ipv6ToCheck . "\n";
echo 'Scanning ipv4 ' . $ipv4ToCheck . "\n";
$geoInst->addIp($ipv6ToCheck);
$geoInst->addIp($ipv4ToCheck);
echo $geoInst->process()->toJson();
unset($geoInst);
```

From the root of the project running
``` bash
php ./src/app.php
```
Should be immediate with no errors and display messages as below
``` bash
Begin update @1568318808.9869
End   update @1568318808.9871
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

You can figure out the accuracy changing from ipv4 to ipv6.

## Quickfix issues

Sometimes from an existing clone when pulling you have to update autoload.

```
composer dump-autoload
```

## Todo

* Implement input arguments to read stdin.
* (WIP) Remove the db files from assets and run update with Composer PostInstall scripts.
* Find a good php documentation generator...

