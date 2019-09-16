<?php

namespace PierInfor\GeoLite\Interfaces;

interface CommonInterface
{
    const DB_PATH = '/../assets/db/';
    const ADAPTER_CITY = 'cityAdapter';
    const ADAPTER_COUNTRY = 'countryAdapter';
    const ADAPTER_ASN = 'asnAdapter';
    const DB_CITY_FILENAME = 'GeoLite2-City.mmdb';
    const DB_COUNTRY_FILENAME = 'GeoLite2-Country.mmdb';
    const DB_ASN_FILENAME = 'GeoLite2-ASN.mmdb';
    const ADAPTERS = [self::ADAPTER_CITY, self::ADAPTER_COUNTRY, self::ADAPTER_ASN];
}
