<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use PierInfor\GeoLiteIp;
use PierInfor\GeoLiteIpUpdater;

/**
 * @covers \PierInfor\GeoLiteIp::<public>
 */
class GeoLiteIpTest extends PFT
{
    const PATH_ASSETS_TESTS = 'src/assets/tests/';
    const ASSET_IP_LIST = self::PATH_ASSETS_TESTS . 'iplist.txt';
    const FIRST_IP = '104.37.188.20';
    const IANA_IP = '192.168.0.1';
    const TEST_ENABLE = true;
    const QM = '?';
    const INV_ARG = 'badip';
    const ADAPTER_ASN = 'asnAdapter';
    const ADAPTER_COUNTRY = 'countryAdapter';
    const ADAPTER_CITY = 'cityAdapter';

    /**
     * instance
     *
     * @var GeoLiteIp
     */
    protected $geoInst;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!self::TEST_ENABLE) {
            $this->markTestSkipped('Test disabled.');
        }
        $this->geoInst = new GeoLiteIp();
    }

    /**
     * get any method from a class to be invoked whatever the scope
     *
     * @param String $name
     * @return void
     */
    protected static function getMethod(string $name)
    {
        $class = new \ReflectionClass('PierInfor\GeoLiteIp');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->geoInst = null;
    }

    /**
     * testInstance
     * @covers PierInfor\GeoLiteIp::__construct
     * @covers PierInfor\GeoLiteIp::setAdapter
     */
    public function testInstance()
    {
        $isGeoInstance = $this->geoInst instanceof GeoLiteIp;
        $this->assertTrue($isGeoInstance);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_ASN);
        $isGeoInstance = $this->geoInst instanceof GeoLiteIp;
        $this->assertTrue($isGeoInstance);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_CITY);
        $isGeoInstance = $this->geoInst instanceof GeoLiteIp;
        $this->assertTrue($isGeoInstance);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_COUNTRY);
        $isGeoInstance = $this->geoInst instanceof GeoLiteIp;
        $this->assertTrue($isGeoInstance);
    }

    /**
     * testSetReaders
     * @covers PierInfor\GeoLiteIp::setReaders
     */
    public function testSetReaders()
    {
        $inst = self::getMethod('setReaders')->invokeArgs(
            $this->geoInst,
            []
        );
        $this->assertTrue($inst instanceof GeoLiteIp);
        $this->assertNotNull($this->geoInst->getReader());
        $this->assertEquals($this->geoInst, $inst);
    }

    /**
     * testGetHeaders
     * @covers PierInfor\GeoLiteIp::getHeaders
     */
    public function testGetHeaders()
    {
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_CITY);
        $headers = self::getMethod('getHeaders')->invokeArgs(
            $this->geoInst,
            []
        );
        $this->assertEquals($headers,GeoLiteIp::HEADERS_COMMON);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_ASN);
        $headers = self::getMethod('getHeaders')->invokeArgs(
            $this->geoInst,
            []
        );
        $this->assertEquals($headers,GeoLiteIp::HEADERS_ASN);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_COUNTRY);
        $headers = self::getMethod('getHeaders')->invokeArgs(
            $this->geoInst,
            []
        );
        $this->assertTrue(is_array($headers));
        $this->assertTrue(count($headers) == 2);
    }

    /**
     * testReset
     * @covers PierInfor\GeoLiteIp::reset
     */
    public function testReset()
    {
        $this->geoInst->reset();
        $this->assertEquals($this->geoInst->getIpList(), []);
        $this->assertEquals($this->geoInst->toArray(), []);
    }

    /**
     * testAddIp
     * @covers PierInfor\GeoLiteIp::addIp
     */
    public function testAddIp()
    {
        $this->geoInst->addIp(self::FIRST_IP);
        $ipList = $this->geoInst->getIpList();
        $this->assertTrue(isset($ipList[0]));
        $this->assertEquals($ipList, [self::FIRST_IP]);
        $result = $this->geoInst
            ->setAdapter(GeoLiteIp::ADAPTER_CITY)
            ->process()
            ->toArray();
        $this->assertEquals(count($result), 1);
    }

    /**
     * testOkFromfile
     * @covers PierInfor\GeoLiteIp::fromFile
     */
    public function testFromfileException()
    {
        $this->expectException(\Exception::class);
        $this->geoInst->fromFile('');
    }

    /**
     * testSetAdapterException
     * @covers PierInfor\GeoLiteIp::setAdapter
     */
    public function testSetAdapterException()
    {
        $this->expectException(\Exception::class);
        $this->geoInst->setAdapter('badAdapter');
    }

    /**
     * testSetAdapter
     * @covers PierInfor\GeoLiteIp::setAdapter
     */
    public function testSetAdapters()
    {
        $this->geoInst->addIp(self::FIRST_IP);
        $result = $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_CITY)->process()->toArray();
        $this->assertNotEmpty($result);
        $result = $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_COUNTRY)->process()->toArray();
        $this->assertNotEmpty($result);
        $result = $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_ASN)->process()->toArray();
        $this->assertNotEmpty($result);
    }

    /**
     * testGetUpdater
     * @covers PierInfor\GeoLiteIp::getUpdater
     */
    public function testGetUpdater()
    {
        $updater = $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_ASN)->getUpdater();
        $isUpdaterInstance = $updater instanceof GeoLiteIpUpdater;
        $this->assertTrue($isUpdaterInstance);
        $this->assertTrue(is_bool($updater->updateRequired()));
        $updater = $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_CITY)->getUpdater();
        $isUpdaterInstance = $updater instanceof GeoLiteIpUpdater;
        $this->assertTrue($isUpdaterInstance);
        $this->assertTrue(is_bool($updater->updateRequired()));
        $updater = $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_COUNTRY)->getUpdater();
        $isUpdaterInstance = $updater instanceof GeoLiteIpUpdater;
        $this->assertTrue($isUpdaterInstance);
        $this->assertTrue(is_bool($updater->updateRequired()));
    }

    /**
     * testUpdate
     * @covers PierInfor\GeoLiteIp::update
     */
    public function testUpdate()
    {
        $updater = $this->geoInst->update($force = true);
        $isUpdaterInstance = $updater instanceof GeoLiteIpUpdater;
        $this->assertTrue($isUpdaterInstance);
        $shouldUpdate = $updater->setAdapter(GeoLiteIp::ADAPTER_CITY)->updateRequired();
        $this->assertTrue(is_bool($shouldUpdate));
        $resupAsn = $updater->setAdapter(GeoLiteIp::ADAPTER_ASN)->update();
        $isUpdaterInstance = $resupAsn instanceof GeoLiteIpUpdater;
        $this->assertTrue($isUpdaterInstance);
    }

    /**
     * testOkFromfile
     * @covers PierInfor\GeoLiteIp::fromFile
     */
    public function testOkFromfile()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
            $this->assertNotEmpty($this->geoInst->getIpList());
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
    }

    /**
     * testNokFromfile
     * @covers PierInfor\GeoLiteIp::fromFile
     */
    public function testNokFromfile()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(
                self::PATH_ASSETS_TESTS . 'filenotfound.txt'
            );
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertTrue($error);
        $this->assertEmpty($this->geoInst->getIpList());
    }

    /**
     * testProcessCountry
     * @covers PierInfor\GeoLiteIp::fromFile
     * @covers PierInfor\GeoLiteIp::setAdapter
     * @covers PierInfor\GeoLiteIp::process
     * @covers PierInfor\GeoLiteIp::toArray
     */
    public function testProcessCountry()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
            $ipList = $this->geoInst->getIpList();
            $this->assertNotEmpty($ipList);
            $this->assertEquals($ipList[0], self::FIRST_IP);
            $record = $this->geoInst
                ->setAdapter(GeoLiteIp::ADAPTER_COUNTRY)
                ->process()
                ->toArray();
            $this->assertNotEmpty($record);
            $this->assertTrue(count($record[0]) === 2);
            $this->assertEquals($record[0][0], self::FIRST_IP);
            $this->assertEquals(strlen($record[0][1]), 2);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
    }

    /**
     * testProcessCity
     * @covers PierInfor\GeoLiteIp::fromFile
     * @covers PierInfor\GeoLiteIp::setAdapter
     * @covers PierInfor\GeoLiteIp::process
     * @covers PierInfor\GeoLiteIp::toArray
     */
    public function testProcessCity()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
            $ipList = $this->geoInst->getIpList();
            $this->assertNotEmpty($ipList);
            $this->assertEquals($ipList[0], self::FIRST_IP);
            $record = $this->geoInst
                ->setAdapter(GeoLiteIp::ADAPTER_CITY)
                ->process()
                ->toArray();
            $this->assertNotEmpty($record);
            $this->assertTrue(count($record[0]) === 6);
            $this->assertEquals($record[0][0], self::FIRST_IP);
            $this->assertEquals(strlen($record[0][1]), 2);
            $this->assertEquals($record[0][2], self::QM);
            $this->assertTrue(is_float($record[0][3]));
            $this->assertTrue(is_float($record[0][4]));
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
    }

    /**
     * testSort
     * @covers PierInfor\GeoLiteIp::fromFile
     * @covers PierInfor\GeoLiteIp::setAdapter
     * @covers PierInfor\GeoLiteIp::process
     * @covers PierInfor\GeoLiteIp::sort
     * @covers PierInfor\GeoLiteIp::toArray
     */
    public function testSort()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
            $record = $this->geoInst
                ->setAdapter(GeoLiteIp::ADAPTER_CITY)
                ->process()
                ->sort(0)
                ->toArray();
            $this->assertNotEmpty($record[0]);
            $this->assertEquals($record[0][0], self::FIRST_IP);
            $record = $this->geoInst->sort(1)->toArray();
            $this->assertNotEquals($record[0][0], self::FIRST_IP);
            $record = $this->geoInst->sort(0)->toArray();
            $this->assertEquals($record[0][0], self::FIRST_IP);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
    }

    /**
     * testToJson
     * @covers PierInfor\GeoLiteIp::fromFile
     * @covers PierInfor\GeoLiteIp::setAdapter
     * @covers PierInfor\GeoLiteIp::process
     * @covers PierInfor\GeoLiteIp::toJson
     * @covers PierInfor\GeoLiteIp::getIpList
     */
    public function testToJson()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
            $json = $this->geoInst
                ->setAdapter(GeoLiteIp::ADAPTER_COUNTRY)
                ->process()
                ->toJson();
            $this->assertNotEmpty($json);
            $this->assertFalse(is_array($json));
            $result = json_decode($json, true);
            $nbResult = count($result);
            $npIp = count($this->geoInst->getIpList());
            $this->assertEquals($nbResult, $npIp);
            $record = $result[0];
            $this->assertEquals($record['ip'], self::FIRST_IP);
            $this->assertEquals(strlen($record['country']), 2);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
    }

    /**
     * testToCsv
     * @covers PierInfor\GeoLiteIp::fromFile
     * @covers PierInfor\GeoLiteIp::setAdapter
     * @covers PierInfor\GeoLiteIp::process
     * @covers PierInfor\GeoLiteIp::toCsv
     */
    public function testToCsv()
    {
        $error = false;
        try {
            $delimiter = ',';
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
            $csv = $this->geoInst
                ->setAdapter(GeoLiteIp::ADAPTER_COUNTRY)
                ->process()
                ->toCsv($delimiter);
            $this->assertNotEmpty($csv);
            $this->assertFalse(is_array($csv));
            $result = [];
            $csvLines = explode("\n", $csv);
            $csvLinesCount = count($csvLines);
            for ($c = 0; $c < $csvLinesCount; $c++) {
                $result[] = explode($delimiter, $csvLines[$c]);
            }
            $this->assertEquals($result[0][0], self::FIRST_IP);
            $this->assertEquals(strlen($result[0][1]), 2);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
    }

    /**
     * testCityAdapter
     * @covers PierInfor\GeoLiteIp::cityAdapter
     */
    public function testCityAdapter()
    {
        $cityAdapterMethod = self::getMethod(self::ADAPTER_CITY);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_CITY);
        $record = $cityAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::FIRST_IP]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 6);
        $this->assertEquals($record[0], self::FIRST_IP);
        $this->assertEquals(strlen($record[1]), 2);
        $this->assertNotEmpty($record[2]);
        $this->assertTrue(is_float($record[3]));
        $this->assertTrue(is_float($record[4]));
        $this->assertTrue(is_int($record[5]));
    }

    /**
     * testCityAdapterAddressNotFound
     * @covers PierInfor\GeoLiteIp::cityAdapter
     */
    public function testCityAdapterAddressNotFound()
    {
        $cityAdapterMethod = self::getMethod(self::ADAPTER_CITY);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_CITY);
        $record = $cityAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::IANA_IP]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 6);
        $expectedRecord = array_fill(0, 6, self::QM);
        $this->assertEquals($record, $expectedRecord);
    }

    /**
     * testCityAdapterInvalidArgument
     * @covers PierInfor\GeoLiteIp::cityAdapter
     */
    public function testCityAdapterInvalidArgument()
    {
        $cityAdapterMethod = self::getMethod(self::ADAPTER_CITY);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_CITY);
        $record = $cityAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::INV_ARG]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 6);
        $expectedRecord = array_fill(0, 6, self::QM);
        $this->assertEquals($record, $expectedRecord);
    }

    /**
     * testCountryAdapter
     * @covers PierInfor\GeoLiteIp::countryAdapter
     */
    public function testCountryAdapter()
    {
        $countryAdapterMethod = self::getMethod(self::ADAPTER_COUNTRY);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_COUNTRY);
        $record = $countryAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::FIRST_IP]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 2);
        $this->assertEquals($record[0], self::FIRST_IP);
        $this->assertEquals(strlen($record[1]), 2);
    }

    /**
     * testCountryAdapterAddressNotFound
     * @covers PierInfor\GeoLiteIp::countryAdapter
     */
    public function testCountryAdapterAddressNotFound()
    {
        $countryAdapterMethod = self::getMethod(self::ADAPTER_COUNTRY);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_COUNTRY);
        $record = $countryAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::IANA_IP]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 2);
        $expectedRecord = array_fill(0, 2, self::QM);
        $this->assertEquals($record, $expectedRecord);
    }

    /**
     * testCountryAdapterInvalidArgument
     * @covers PierInfor\GeoLiteIp::countryAdapter
     */
    public function testCountryAdapterInvalidArgument()
    {
        $countryAdapterMethod = self::getMethod(self::ADAPTER_COUNTRY);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_COUNTRY);
        $record = $countryAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::INV_ARG]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 2);
        $expectedRecord = array_fill(0, 2, self::QM);
        $this->assertEquals($record, $expectedRecord);
    }

    /**
     * testAsnAdapter
     * @covers PierInfor\GeoLiteIp::asnAdapter
     */
    public function testAsnAdapter()
    {
        $asnAdapterMethod = self::getMethod(self::ADAPTER_ASN);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_ASN);
        $record = $asnAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::FIRST_IP]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 3);
        $this->assertEquals($record[0], self::FIRST_IP);
        $this->assertTrue(is_int($record[1]));
        $this->assertNotEmpty($record[2]);
    }

    /**
     * testAsnAdapterAddressNotFound
     * @covers PierInfor\GeoLiteIp::asnAdapter
     */
    public function testAsnAdapterAddressNotFound()
    {
        $asnAdapterMethod = self::getMethod(self::ADAPTER_ASN);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_ASN);
        $record = $asnAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::IANA_IP]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 3);
        $expectedRecord = array_fill(0, 3, self::QM);
        $this->assertEquals($record, $expectedRecord);
    }

    /**
     * testAsnAdapterInvalidArgument
     * @covers PierInfor\GeoLiteIp::asnAdapter
     */
    public function testAsnAdapterInvalidArgument()
    {
        $asnAdapterMethod = self::getMethod(self::ADAPTER_ASN);
        $this->geoInst->setAdapter(GeoLiteIp::ADAPTER_ASN);
        $record = $asnAdapterMethod->invokeArgs(
            $this->geoInst,
            [$this->geoInst->getReader(), self::INV_ARG]
        );
        $this->assertTrue(is_array($record));
        $this->assertTrue(count($record) == 3);
        $expectedRecord = array_fill(0, 3, self::QM);
        $this->assertEquals($record, $expectedRecord);
    }
}
