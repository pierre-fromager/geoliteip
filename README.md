# :elephant: geoliteip

[![TravsisBadgeBuild](https://travis-ci.org/pierre-fromager/geoliteip.svg?branch=master)](https://travis-ci.org/pierre-fromager/geoliteip)
[![Coverage](https://scrutinizer-ci.com/g/pierre-fromager/geoliteip/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/pierre-fromager/geoliteip/)
[![ScrutinizerScore](https://scrutinizer-ci.com/g/pierre-fromager/geoliteip/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/pierre-fromager/geoliteip/)
[![Latest Stable Version](https://poser.pugx.org/pier-infor/geoliteip/v/stable)](https://packagist.org/packages/pier-infor/geoliteip)
[![Total Downloads](https://poser.pugx.org/pier-infor/geoliteip/downloads)](https://packagist.org/packages/pier-infor/geoliteip)
[![Latest Unstable Version](https://poser.pugx.org/pier-infor/geoliteip/v/unstable)](https://packagist.org/packages/pier-infor/geoliteip)

Geoliteip is a php tool to use and manage maxmind GeoLite2 free databases in mmdb format.

## :ocean: Features

* Use 3 free dbs as asn, country, city.
* Change db on the fly without re-instanciate.
* Input ip list from plain text file.
* Output as array, json, csv.
* Update dbs on the fly or from composer.

### :thumbsup: Pro

* Flexibility and scalability with factory/adapter pattern.
* Annotated and tested with full coverage.
* Changing behaviours can be simply done by overloading/adding adapters.
* All constants class centralized in src/Interfaces can be overloaded;

### :thumbsdown: Cons

* do not use non free GeoLite dbs (but can be extended to do the job).

## :construction_worker: Dependencies

* geoip2/geoip2

## :innocent: Testing & Coverage

* before running tests install db doing
```
composer run db
```
* Require xdebug to enable coverage.
* Tests all passed with php version >= 7.0.
* /!\\ updater tests makes real db update from maxmind, abusing leads to a 503.

## Composer 

### Facilities run

* db (download and install db in assets db).
* test (pass all tests).
* coverage (pass all tests with coverage).
* testIp (run IpTest only).
* testDownloader (run DownloaderTest only).
* testFileManager (run FileManagerTest only).
* testUpdater (run UpdaterTest only).
* lint (check and fix source errors).

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

* the script to run update/download on post install

``` json
"scripts": {
    ...
    "post-install-cmd": [
        ...
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
$geoInst
    ->setAdapter(Ip::ADAPTER_ASN)->update($forceUpdate)
    ->setAdapter(Ip::ADAPTER_COUNTRY)->update($forceUpdate)
    ->setAdapter(Ip::ADAPTER_CITY)->update($forceUpdate);
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

Changing forceUpdate to true will force update in silent mode.

You can figure out the accuracy changing from ipv4 to ipv6 for the same location.

## :hamster: Todo

* Implement input arguments to read stdin.
* Find a good php documentation generator...
