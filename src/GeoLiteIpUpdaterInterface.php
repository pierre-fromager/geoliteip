<?php

namespace PierInfor;

interface GeoLiteIpUpdaterInterface extends GeoLiteIpCommonInterface
{

    const UPDATER_URL = 'https://geolite.maxmind.com/download/geoip/database/GeoLite2-';
    const UPDATER_EXT = '.tar.gz';
    const UPDATERS = [
        self::ADAPTER_CITY => self::UPDATER_URL . 'City' . self::UPDATER_EXT,
        self::ADAPTER_COUNTRY => self::UPDATER_URL .  'Country' . self::UPDATER_EXT,
        self::ADAPTER_ASN => self::UPDATER_URL . 'ASN' . self::UPDATER_EXT
    ];
    const UPDATERS_DB_FILENAME = [
        self::ADAPTER_CITY => self::DB_CITY_FILENAME,
        self::ADAPTER_COUNTRY => self::DB_COUNTRY_FILENAME,
        self::ADAPTER_ASN => self::DB_ASN_FILENAME
    ];
    const CLEAN_DB_EXTS = ['*.tar.gz', '*.tar'];

    public function __construct();

    public function setAdapter(string $adapter = self::ADAPTER_COUNTRY): GeoLiteIpUpdater;

    public function update(): GeoLiteIpUpdater;
}
