<?php

namespace PierInfor\GeoLite\Interfaces;

use PierInfor\GeoLite\Ip;
use PierInfor\GeoLite\Updater;
use GeoIp2\Database\Reader;

/**
 * @codeCoverageIgnore
 */
interface IpInterface extends CommonInterface
{
    const HEADERS_COMMON = ['ip', 'country', 'city', 'lon', 'lat', 'radius'];
    const HEADERS_ASN = ['ip', 'as', 'organization'];
    const BUFFER = 4096;

    public function __construct($locales = ['fr']);

    public function __destruct();

    public function reset();

    public function getReader(): Reader;

    public function getUpdater(): Updater;

    public function update(bool $force = false): Updater;

    public function setAdapter(string $adapter = self::ADAPTER_COUNTRY): Ip;

    public function addIp(string $ip): Ip;

    public function fromFile(string $filename): Ip;

    public function getIpList(): array;

    public function process(): Ip;

    public function sort(int $col = 1): Ip;

    public function toArray(): array;

    public function toCsv(string $sep = ';'): string;

    public function toJson(): string;
}
