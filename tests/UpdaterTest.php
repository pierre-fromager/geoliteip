<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use PierInfor\GeoLite\FileManager;
use PierInfor\GeoLite\Updater;

/**
 * @covers \PierInfor\GeoLite\Updater::<public>
 */
class UpdaterTest extends PFT
{

    const TEST_ENABLE = true;
    const PATH_ASSETS = 'assets/';
    const ASSET_IP_LIST = self::PATH_ASSETS . 'iplist.txt';
    const FIRST_IP = '104.37.188.20';

    /**
     * instance
     *
     * @var Updater
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
        $this->geoInst = new Updater();
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
     * @covers PierInfor\GeoLite\Updater::__construct
     */
    public function testInstance()
    {
        $this->assertTrue($this->geoInst instanceof Updater);
    }

    /**
     * constantsProvider
     * @return Array
     */
    public function constantsProvider()
    {
        return [
            ['DB_PATH'],
            ['ADAPTER_CITY'],
            ['ADAPTER_COUNTRY'],
            ['ADAPTER_ASN'],
            ['DB_CITY_FILENAME'],
            ['DB_COUNTRY_FILENAME'],
            ['DB_ASN_FILENAME'],
            ['ADAPTERS'],
            ['UPDATER_URL'],
            ['UPDATER_EXT'],
            ['UPDATERS'],
            ['UPDATERS_DB_FILENAME'],
        ];
    }

    /**
     * testConstants
     * @covers PierInfor\GeoLite\Updater::__construct
     * @dataProvider constantsProvider
     */
    public function testConstants($k)
    {
        $class = new \ReflectionClass(Updater::class);
        $this->assertArrayHasKey($k, $class->getConstants());
        unset($class);
    }

    /**
     * testGetFileManager
     * @depends testInstance
     * @covers PierInfor\GeoLite\Updater::getFileManager
     */
    public function testGetFileManager()
    {
        $this->assertTrue(
            $this->geoInst->getFileManager() instanceof FileManager
        );
    }

    /**
     * testSetAdapterException
     * @depends testInstance
     * @covers PierInfor\GeoLite\Updater::setAdapter
     */
    public function testSetAdapterException()
    {
        $this->expectException(\Exception::class);
        $this->geoInst->setAdapter('badAdapter');
    }

    /**
     * testSetAdapter
     * @depends testInstance
     * @covers PierInfor\GeoLite\Updater::setAdapter
     */
    public function testSetAdapter()
    {
        $this->assertTrue(
            $this->geoInst->setAdapter(Updater::ADAPTER_CITY) instanceof Updater
        );
        $this->assertTrue(
            $this->geoInst->setAdapter(Updater::ADAPTER_COUNTRY) instanceof Updater
        );
        $this->assertTrue(
            $this->geoInst->setAdapter(Updater::ADAPTER_ASN) instanceof Updater
        );
    }

    /**
     * testClean
     * @depends testInstance
     * @covers PierInfor\GeoLite\Updater::clean
     */
    public function testClean()
    {
        $cle = $this->geoInst->clean();
        $this->assertTrue($cle instanceof Updater);
        /*
        $this->geoInst->setAdapter(Updater::ADAPTER_CITY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(Updater::ADAPTER_COUNTRY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(Updater::ADAPTER_ASN);
        $this->assertTrue($this->isUpdaterInstance());*/
    }

    /**
     * testUpdateCity
     * @depends testInstance
     * @covers PierInfor\GeoLite\Updater::update
     */
    public function testUpdateCity()
    {
        $retInst = $this->geoInst->setAdapter(Updater::ADAPTER_CITY)->update();
        $this->assertTrue($retInst instanceof Updater);
        $this->assertTrue(
            file_exists(
                __DIR__ . Updater::DB_PATH . Updater::DB_CITY_FILENAME
            )
        );
    }

    /**
     * testUpdateRequired
     * @depends testInstance
     * @covers PierInfor\GeoLite\Updater::updateRequired
     */
    public function testUpdateRequired()
    {
        $requireCityUpdate = $this->geoInst
            ->setAdapter(Updater::ADAPTER_CITY)
            ->updateRequired();
        $this->assertTrue(is_bool($requireCityUpdate));
    }
}
