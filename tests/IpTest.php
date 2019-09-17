<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use PierInfor\GeoLite\Ip;
use PierInfor\GeoLite\Updater;

/**
 * @covers \PierInfor\GeoLite\Ip::<public>
 */
class IpTest extends PFT
{
    const TEST_ENABLE = true;
    const PATH_ASSETS_TESTS = 'assets/tests/';
    const ASSET_IP_LIST = self::PATH_ASSETS_TESTS . 'iplist.txt';
    const FIRST_IP = '104.37.188.20';
    const IANA_IP = '192.168.0.1';
    const QM = '?';
    const INV_ARG = 'badip';
    const ADAPTER_ASN = 'asnAdapter';
    const ADAPTER_COUNTRY = 'countryAdapter';
    const ADAPTER_CITY = 'cityAdapter';

    /**
     * instance
     *
     * @var Ip
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
        $this->geoInst = new Ip();
    }

    /**
     * get any method from a class to be invoked whatever the scope
     *
     * @param String $name
     * @return void
     */
    protected static function getMethod(string $name)
    {
        $class = new \ReflectionClass(Ip::class);
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
     * @covers PierInfor\GeoLite\Ip::__construct
     * @covers PierInfor\GeoLite\Ip::setAdapter
     */
    public function testInstance()
    {
        $isGeoInstance = $this->geoInst instanceof Ip;
        $this->assertTrue($isGeoInstance);
        $this->geoInst->setAdapter(Ip::ADAPTER_ASN);
        $isGeoInstance = $this->geoInst instanceof Ip;
        $this->assertTrue($isGeoInstance);
        $this->geoInst->setAdapter(Ip::ADAPTER_CITY);
        $isGeoInstance = $this->geoInst instanceof Ip;
        $this->assertTrue($isGeoInstance);
        $this->geoInst->setAdapter(Ip::ADAPTER_COUNTRY);
        $isGeoInstance = $this->geoInst instanceof Ip;
        $this->assertTrue($isGeoInstance);
    }

    /**
     * constantsProvider
     * @depends testInstance
     * @return Array
     */
    public function constantsProvider()
    {
        return [
            ['HEADERS_COMMON'],
            ['HEADERS_ASN'],
            ['BUFFER'],
        ];
    }

    /**
     * testConstants
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::__construct
     * @dataProvider constantsProvider
     */
    public function testConstants($k)
    {
        $class = new \ReflectionClass(Ip::class);
        $this->assertArrayHasKey($k, $class->getConstants());
        unset($class);
    }

    /**
     * testSetReaders
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::setReaders
     */
    public function testSetReaders()
    {
        $inst = self::getMethod('setReaders')->invokeArgs(
            $this->geoInst,
            []
        );
        $this->assertTrue($inst instanceof Ip);
        $this->assertNotNull($this->geoInst->getReader());
        $this->assertEquals($this->geoInst, $inst);
    }

    /**
     * testGetHeaders
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::getHeaders
     */
    public function testGetHeaders()
    {
        $method = 'getHeaders';
        $this->geoInst->setAdapter(Ip::ADAPTER_CITY);
        $headers = self::getMethod($method)->invokeArgs(
            $this->geoInst,
            []
        );
        $this->assertEquals($headers, Ip::HEADERS_COMMON);
        $this->geoInst->setAdapter(Ip::ADAPTER_ASN);
        $headers = self::getMethod($method)->invokeArgs(
            $this->geoInst,
            []
        );
        $this->assertEquals($headers, Ip::HEADERS_ASN);
        $this->geoInst->setAdapter(Ip::ADAPTER_COUNTRY);
        $headers = self::getMethod($method)->invokeArgs(
            $this->geoInst,
            []
        );
        $this->assertTrue(is_array($headers));
        $this->assertTrue(count($headers) == 2);
    }

    /**
     * testReset
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::reset
     */
    public function testReset()
    {
        $this->geoInst->reset();
        $this->assertEquals($this->geoInst->getIpList(), []);
        $this->assertEquals($this->geoInst->toArray(), []);
    }

    /**
     * testAddIp
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::addIp
     */
    public function testAddIp()
    {
        $this->geoInst->addIp(self::FIRST_IP);
        $ipList = $this->geoInst->getIpList();
        $this->assertTrue(isset($ipList[0]));
        $this->assertEquals($ipList, [self::FIRST_IP]);
        $result = $this->geoInst
            ->setAdapter(Ip::ADAPTER_CITY)
            ->process()
            ->toArray();
        $this->assertEquals(count($result), 1);
    }

    /**
     * testOkFromfile
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::fromFile
     */
    public function testFromfileException()
    {
        $this->expectException(\Exception::class);
        $this->geoInst->fromFile('');
    }

    /**
     * testSetAdapterException
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::setAdapter
     */
    public function testSetAdapterException()
    {
        $this->expectException(\Exception::class);
        $this->geoInst->setAdapter('badAdapter');
    }

    /**
     * testSetAdapter
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::setAdapter
     */
    public function testSetAdapters()
    {
        $this->geoInst->addIp(self::FIRST_IP);
        $result = $this->geoInst->setAdapter(Ip::ADAPTER_CITY)->process()->toArray();
        $this->assertNotEmpty($result);
        $result = $this->geoInst->setAdapter(Ip::ADAPTER_COUNTRY)->process()->toArray();
        $this->assertNotEmpty($result);
        $result = $this->geoInst->setAdapter(Ip::ADAPTER_ASN)->process()->toArray();
        $this->assertNotEmpty($result);
    }

    /**
     * testGetUpdater
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::getUpdater
     */
    public function testGetUpdater()
    {
        $updater = $this->geoInst->setAdapter(Ip::ADAPTER_ASN)->getUpdater();
        $isUpdaterInstance = $updater instanceof Updater;
        $this->assertTrue($isUpdaterInstance);
        $this->assertTrue(is_bool($updater->updateRequired()));
        $updater = $this->geoInst->setAdapter(Ip::ADAPTER_CITY)->getUpdater();
        $isUpdaterInstance = $updater instanceof Updater;
        $this->assertTrue($isUpdaterInstance);
        $this->assertTrue(is_bool($updater->updateRequired()));
        $updater = $this->geoInst->setAdapter(Ip::ADAPTER_COUNTRY)->getUpdater();
        $isUpdaterInstance = $updater instanceof Updater;
        $this->assertTrue($isUpdaterInstance);
        $this->assertTrue(is_bool($updater->updateRequired()));
    }

    /**
     * testUpdate
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::update
     */
    public function testUpdate()
    {
        $updater = $this->geoInst->update($force = true);
        $isUpdaterInstance = $updater instanceof Updater;
        $this->assertTrue($isUpdaterInstance);
        $shouldUpdate = $updater->setAdapter(Ip::ADAPTER_CITY)->updateRequired();
        $this->assertTrue(is_bool($shouldUpdate));
        $resupAsn = $updater->setAdapter(Ip::ADAPTER_ASN)->update();
        $isUpdaterInstance = $resupAsn instanceof Updater;
        $this->assertTrue($isUpdaterInstance);
    }

    /**
     * testOkFromfile
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::fromFile
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::fromFile
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::fromFile
     * @covers PierInfor\GeoLite\Ip::setAdapter
     * @covers PierInfor\GeoLite\Ip::process
     * @covers PierInfor\GeoLite\Ip::toArray
     */
    public function testProcessCountry()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
        $ipList = $this->geoInst->getIpList();
        $this->assertNotEmpty($ipList);
        $this->assertEquals($ipList[0], self::FIRST_IP);
        $record = $this->geoInst
            ->setAdapter(Ip::ADAPTER_COUNTRY)
            ->process()
            ->toArray();
        $this->assertNotEmpty($record);
        $this->assertTrue(count($record[0]) === 2);
        $this->assertEquals($record[0][0], self::FIRST_IP);
        $this->assertEquals(strlen($record[0][1]), 2);
        $this->assertFalse($error);
    }

    /**
     * testProcessCity
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::fromFile
     * @covers PierInfor\GeoLite\Ip::setAdapter
     * @covers PierInfor\GeoLite\Ip::process
     * @covers PierInfor\GeoLite\Ip::toArray
     */
    public function testProcessCity()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
        $ipList = $this->geoInst->getIpList();
        $this->assertNotEmpty($ipList);
        $this->assertEquals($ipList[0], self::FIRST_IP);
        $record = $this->geoInst
            ->setAdapter(Ip::ADAPTER_CITY)
            ->process()
            ->toArray();
        $this->assertNotEmpty($record);
        $this->assertTrue(count($record[0]) === 6);
        $this->assertEquals($record[0][0], self::FIRST_IP);
        $this->assertEquals(strlen($record[0][1]), 2);
        $this->assertEquals($record[0][2], self::QM);
        $this->assertTrue(is_float($record[0][3]));
        $this->assertTrue(is_float($record[0][4]));
        $this->assertFalse($error);
    }

    /**
     * testSort
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::fromFile
     * @covers PierInfor\GeoLite\Ip::setAdapter
     * @covers PierInfor\GeoLite\Ip::process
     * @covers PierInfor\GeoLite\Ip::sort
     * @covers PierInfor\GeoLite\Ip::toArray
     */
    public function testSort()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
        $record = $this->geoInst
            ->setAdapter(Ip::ADAPTER_CITY)
            ->process()
            ->sort(0)
            ->toArray();
        $this->assertNotEmpty($record[0]);
        $this->assertEquals($record[0][0], self::FIRST_IP);
        $record = $this->geoInst->sort(1)->toArray();
        $this->assertNotEquals($record[0][0], self::FIRST_IP);
        $record = $this->geoInst->sort(0)->toArray();
        $this->assertEquals($record[0][0], self::FIRST_IP);
        $this->assertFalse($error);
    }

    /**
     * testCompareArray
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::compareArray
     */
    public function testCompareArray()
    {
        $method = 'compareArray';
        $this->geoInst->setAdapter(Ip::ADAPTER_CITY);
        $compareArray = $this->getMethod($method);
        $result = $compareArray->invokeArgs(
            $this->geoInst,
            [['a', 'b'], ['c', 'd']]
        );
        $this->assertTrue(is_int($result));
        $this->assertTrue($result < 0);
        $result = $compareArray->invokeArgs(
            $this->geoInst,
            [['c', 'd'], ['a', 'b']]
        );
        $this->assertTrue(is_int($result));
        $this->assertTrue($result > 0);
        $result = $compareArray->invokeArgs(
            $this->geoInst,
            [['a', 'b'], ['a', 'b']]
        );
        $this->assertTrue(is_int($result));
        $this->assertEquals($result, 0);
    }

    /**
     * testToJson
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::fromFile
     * @covers PierInfor\GeoLite\Ip::setAdapter
     * @covers PierInfor\GeoLite\Ip::process
     * @covers PierInfor\GeoLite\Ip::toJson
     * @covers PierInfor\GeoLite\Ip::getIpList
     */
    public function testToJson()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
        $json = $this->geoInst
            ->setAdapter(Ip::ADAPTER_COUNTRY)
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
        $this->assertFalse($error);
    }

    /**
     * testToCsv
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::fromFile
     * @covers PierInfor\GeoLite\Ip::setAdapter
     * @covers PierInfor\GeoLite\Ip::process
     * @covers PierInfor\GeoLite\Ip::toCsv
     */
    public function testToCsv()
    {
        $error = false;
        try {
            $this->geoInst->fromFile(self::ASSET_IP_LIST);
        } catch (\Exception $e) {
            $error = true;
        }
        $this->assertFalse($error);
        $delimiter = ',';
        $csv = $this->geoInst
            ->setAdapter(Ip::ADAPTER_COUNTRY)
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
        $this->assertFalse($error);
    }

    /**
     * testCityAdapter
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::cityAdapter
     */
    public function testCityAdapter()
    {
        $cityAdapterMethod = self::getMethod(self::ADAPTER_CITY);
        $this->geoInst->setAdapter(Ip::ADAPTER_CITY);
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::cityAdapter
     */
    public function testCityAdapterAddressNotFound()
    {
        $cityAdapterMethod = self::getMethod(self::ADAPTER_CITY);
        $this->geoInst->setAdapter(Ip::ADAPTER_CITY);
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::cityAdapter
     */
    public function testCityAdapterInvalidArgument()
    {
        $cityAdapterMethod = self::getMethod(self::ADAPTER_CITY);
        $this->geoInst->setAdapter(Ip::ADAPTER_CITY);
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::countryAdapter
     */
    public function testCountryAdapter()
    {
        $countryAdapterMethod = self::getMethod(self::ADAPTER_COUNTRY);
        $this->geoInst->setAdapter(Ip::ADAPTER_COUNTRY);
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::countryAdapter
     */
    public function testCountryAdapterAddressNotFound()
    {
        $countryAdapterMethod = self::getMethod(self::ADAPTER_COUNTRY);
        $this->geoInst->setAdapter(Ip::ADAPTER_COUNTRY);
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::countryAdapter
     */
    public function testCountryAdapterInvalidArgument()
    {
        $countryAdapterMethod = self::getMethod(self::ADAPTER_COUNTRY);
        $this->geoInst->setAdapter(Ip::ADAPTER_COUNTRY);
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::asnAdapter
     */
    public function testAsnAdapter()
    {
        $asnAdapterMethod = self::getMethod(self::ADAPTER_ASN);
        $this->geoInst->setAdapter(Ip::ADAPTER_ASN);
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::asnAdapter
     */
    public function testAsnAdapterAddressNotFound()
    {
        $asnAdapterMethod = self::getMethod(self::ADAPTER_ASN);
        $this->geoInst->setAdapter(Ip::ADAPTER_ASN);
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
     * @depends testInstance
     * @covers PierInfor\GeoLite\Ip::asnAdapter
     */
    public function testAsnAdapterInvalidArgument()
    {
        $asnAdapterMethod = self::getMethod(self::ADAPTER_ASN);
        $this->geoInst->setAdapter(Ip::ADAPTER_ASN);
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
