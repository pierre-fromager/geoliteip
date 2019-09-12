<?php

namespace PierInfor;

use PierInfor\GeoLiteIpInterface;
use PierInfor\GeoLiteIpUpdater;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Database\Reader;

/**
 * @use GeoLiteIp
 * GeoLiteIp class to use free maxmind dbs in mmdb format
 */
class GeoLiteIp implements GeoLiteIpInterface
{

    /**
     * locales in use
     *
     * @var Array
     */
    private $readerLocales;

    /**
     * Reader instance for cities
     *
     * @var Reader
     */
    private $readerCity;

    /**
     * Reader instance for countries
     *
     * @var Reader
     */
    private $readerCountry;

    /**
     * Reader instance for Asn
     *
     * @var Reader
     */
    private $readerAsn;

    /**
     * updater instance
     *
     * @var GeoLiteIpUpdater
     */
    private $updater;

    /**
     * Results
     *
     * @var Array
     */
    private $results;

    /**
     * Ip collection
     *
     * @var Array
     */
    private $ipList;

    /**
     * Current adapter
     *
     * @var String
     */
    private $adapter;

    /**
     * Instanciate with given locales
     *
     * @param array $locales
     */
    public function __construct($locales = ['fr'])
    {
        $this->readerLocales = $locales;
        $this->reset();
        $this->setReaders();
        $this->setAdapter();
        $this->updater = new GeoLiteIpUpdater();
    }

    /**
     * free when unset instance
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->adapter = null;
        $this->readerCity->close();
        $this->readerCountry->close();
        $this->readerAsn->close();
        $this->reset();
    }

    /**
     * reset ip list and result
     *
     * @return void
     */
    public function reset()
    {
        $this->ipList = [];
        $this->results = [];
    }

    /**
     * set adapter
     *
     * @param string $adapter
     * @return GeoLiteIp
     * @throws \Exception
     */
    public function setAdapter(string $adapter = self::ADAPTER_COUNTRY): GeoLiteIp
    {
        if (!in_array($adapter, self::ADAPTERS)) {
            throw new \Exception('Error: Unkown adapter ' . $adapter);
        }
        $this->adapter = $adapter;
        return $this;
    }

     /**
      * update current db if required or forced
      *
      * @param boolean $force
      * @return GeoLiteIpUpdater
      */
    public function update(bool $force = false): GeoLiteIpUpdater
    {
        return ($this->getUpdater()->updateRequired() || $force)
            ? $this->updater->update()
            : $this->updater;
    }

    /**
     * addIp
     *
     * @param string $ip
     * @return GeoLiteIp
     */
    public function addIp(string $ip): GeoLiteIp
    {
        $this->ipList[] = trim($ip);
        $this->ipList = array_unique($this->ipList);
        return $this;
    }

    /**
     * load ip list from a file
     *
     * @param string $filename
     * @return GeoLiteIp
     * @throws \Exception
     */
    public function fromFile(string $filename): GeoLiteIp
    {
        $this->reset();
        $handle = @fopen($filename, 'r');
        if (!is_resource($handle)) {
            throw new \Exception('Error: File open issue');
        }
        if ($handle) {
            while (($ip = fgets($handle, self::BUFFER)) !== false) {
                $this->addIp($ip);
            }
            fclose($handle);
        }
        return $this;
    }

    /**
     * returns ip list
     *
     * @return array
     */
    public function getIpList(): array
    {
        return $this->ipList;
    }

    /**
     * geo process ip list
     *
     * @return GeoLiteIp
     */
    public function process(): GeoLiteIp
    {
        $this->results = [];
        $reader = $this->getReader();
        $li = count($this->ipList);
        for ($c = 0; $c < $li; $c++) {
            $this->results[] = call_user_func_array(
                [$this, $this->adapter],
                [$reader, $this->ipList[$c]]
            );
        }
        return $this;
    }

    /**
     * return reader belongs to current adapter
     *
     * @return Reader
     */
    public function getReader(): Reader
    {
        switch ($this->adapter) {
            case self::ADAPTER_CITY:
                $reader = $this->readerCity;
                break;
            case self::ADAPTER_COUNTRY:
                $reader = $this->readerCountry;
                break;
            case self::ADAPTER_ASN:
                $reader = $this->readerAsn;
                break;
        }
        return $reader;
    }

    /**
     * returns GeoLiteIpUpdater instance
     *
     * @return GeoLiteIpUpdater
     */
    public function getUpdater(): GeoLiteIpUpdater
    {
        return $this->updater->setAdapter($this->adapter);
    }

    /**
     * sort result by column number
     *
     * @param integer $col
     * @return GeoLiteIp
     */
    public function sort(int $col = 1): GeoLiteIp
    {
        usort($this->results, function ($a, $b) use ($col) {
            return strcmp($a[$col], $b[$col]);
        });
        return $this;
    }

    /**
     * returns result as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->results;
    }

    /**
     * returns result as csv
     *
     * @param string $sep
     * @return void
     */
    public function toCsv(string $sep = ';'): string
    {
        $lc = count($this->results);
        $csv = '';
        for ($c = 0; $c < $lc; $c++) {
            $csv .= implode($sep, $this->results[$c]) . "\n";
        }
        return $csv;
    }

    /**
     * returns result as json
     *
     * @return string
     */
    public function toJson(): string
    {
        $lc = count($this->results);
        $json = [];
        $headers = $this->getHeaders();
        for ($c = 0; $c < $lc; $c++) {
            $json[] = array_combine($headers, $this->results[$c]);
        }
        return json_encode($json, JSON_PRETTY_PRINT);
    }

    /**
     * set readers, one per db file
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function setReaders()
    {
        $this->readerCity = new Reader(
            __DIR__ . self::DB_PATH . self::DB_CITY_FILENAME,
            $this->readerLocales
        );
        $this->readerCountry = new Reader(
            __DIR__ . self::DB_PATH . self::DB_COUNTRY_FILENAME,
            $this->readerLocales
        );
        $this->readerAsn = new Reader(
            __DIR__ . self::DB_PATH . self::DB_ASN_FILENAME,
            $this->readerLocales
        );
    }

    /**
     * returns headers
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function getHeaders(): array
    {
        $headers = [];
        switch ($this->adapter) {
            case self::ADAPTER_CITY:
                $headers = self::HEADERS_COMMON;
                break;
            case self::ADAPTER_COUNTRY:
                $headers = array_slice(self::HEADERS_COMMON, 0, 2);
                break;
            case self::ADAPTER_ASN:
                $headers = self::HEADERS_ASN;
                break;
        }
        return $headers;
    }

    /**
     * adapter to use city reader, returns record chosen fields
     *
     * @param Reader $reader
     * @param string $ip
     * @return array
     */
    protected function cityAdapter(Reader $reader, string $ip): array
    {
        $ip = trim($ip);
        try {
            $record = $reader->city($ip);
            return [
                $ip,
                $record->country->isoCode,
                $record->city->name ?: '?',
                $record->location->latitude,
                $record->location->longitude,
                $record->location->accuracyRadius,
            ];
        } catch (AddressNotFoundException $e) {
            return array_fill(0, 6, '?');
        } catch (\InvalidArgumentException $e) {
            return array_fill(0, 6, '?');
        }
    }

    /**
     * adapter to use country reader, returns record chosen fields
     *
     * @param Reader $reader
     * @param string $ip
     * @return array
     */
    protected function countryAdapter(Reader $reader, string $ip): array
    {
        $ip = trim($ip);
        try {
            $record = $reader->country($ip);
            return [$ip, $record->country->isoCode];
        } catch (AddressNotFoundException $e) {
            return array_fill(0, 2, '?');
        } catch (\InvalidArgumentException $e) {
            return array_fill(0, 2, '?');
        }
    }

    /**
     * adapter to use asn reader, returns record chosen fields
     *
     * @param Reader $reader
     * @param string $ip
     * @return array
     */
    protected function asnAdapter(Reader $reader, string $ip): array
    {
        $ip = trim($ip);
        $badRes = array_fill(0, 3, '?');
        try {
            $record = $reader->asn($ip);
            return [
                $ip,
                $record->autonomousSystemNumber,
                $record->autonomousSystemOrganization
            ];
        } catch (AddressNotFoundException $e) {
            return $badRes;
        } catch (\InvalidArgumentException $e) {
            return $badRes;
        }
    }
}
