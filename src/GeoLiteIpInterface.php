<?php

namespace PierInfor;

use GeoIp2\Database\Reader;

/**
 * @codeCoverageIgnore
 */
interface GeoLiteIpInterface extends GeoLiteIpCommonInterface
{
    const HEADERS_COMMON = ['ip', 'country', 'city', 'lon', 'lat', 'radius'];
    const HEADERS_ASN = ['ip', 'as', 'organization'];
    
    const BUFFER = 4096;

    public function __construct($locales = ['fr']);

    public function __destruct();

    public function reset();

    public function getReader(): Reader;

    public function getUpdater(): GeoLiteIpUpdater;

    public function update(bool $force = false): GeoLiteIpUpdater;

    public function setAdapter(string $adapter = self::ADAPTER_COUNTRY): GeoLiteIp;

    public function addIp(string $ip): GeoLiteIp;

    public function fromFile(string $filename): GeoLiteIp;

    public function getIpList(): array;

    public function process(): GeoLiteIp;

    public function sort(int $col = 1): GeoLiteIp;

    public function toArray(): array;

    public function toCsv(string $sep = ';'): string;

    public function toJson(): string;
}
