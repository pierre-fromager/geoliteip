# geoliteip

Geoliteip is a php tool to use and manage maxmind GeoLite2 free databases in mmdb format.

## Features

* use 3 free dbs as asn, country, city.
* change db on the fly without re-instanciate.
* input ip list from plain text file.
* output as array, json, csv.
* update dbs on the fly.

## Pro

* flexibility with adapter pattern.
* documented and tested with full coverage.
* overriding adapters methods can be done to change behaviour.

## Cons

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

## Composer facilities

* run test (pass tests and generate coverage in html format)
* run checklint (linter checker).
* run lint (lint the code).

## Composer integration

To play with GeoLiteIp from your projects, adjust your composer.json as below.

* Add as below the required package
``` json
"require": {
    ...
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

## Todo

* Implement input arguments to read stdin.
* Remove the db files from assets and run update with Composer PostInstall scripts.

