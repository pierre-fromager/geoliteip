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
        if (!self::TEST_ENABLE){
            $this->markTestSkipped('Test disabled.');
        }
        $this->geoInst = new Updater();
    }

    /**
     * facility to check instanceof
     *
     * @return boolean
     */
    protected function isUpdaterInstance(): bool
    {
        return $this->geoInst instanceof Updater;
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
        $this->assertTrue($this->isUpdaterInstance());
    }

    /**
     * testGetFileManager
     * @covers PierInfor\GeoLite\Updater::getFileManager
     */
    public function testGetFileManager()
    {
        $res = $this->geoInst->getFileManager();
        $this->assertTrue(
            $this->geoInst->getFileManager() instanceof FileManager
        );
    }

    /**
     * testSetAdapterException
     * @covers PierInfor\GeoLite\Updater::setAdapter
     */
    public function testSetAdapterException()
    {
        $this->expectException(\Exception::class);
        $this->geoInst->setAdapter('badAdapter');
    }

    /**
     * testSetAdapter
     * @covers PierInfor\GeoLite\Updater::setAdapter
     */
    public function testSetAdapter()
    {
        $this->geoInst->setAdapter(Updater::ADAPTER_CITY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(Updater::ADAPTER_COUNTRY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(Updater::ADAPTER_ASN);
        $this->assertTrue($this->isUpdaterInstance());
    }

    /**
     * testClean
     * @covers PierInfor\GeoLite\Updater::clean
     */
    public function testClean()
    {
        $this->geoInst->clean();
        $this->geoInst->setAdapter(Updater::ADAPTER_CITY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(Updater::ADAPTER_COUNTRY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(Updater::ADAPTER_ASN);
        $this->assertTrue($this->isUpdaterInstance());
    }

    /**
     * testUpdateCity
     * @covers PierInfor\GeoLite\Updater::update
     */
    public function testUpdateCity()
    {
        $this->geoInst->setAdapter(Updater::ADAPTER_CITY)->update();
        $this->assertTrue($this->isUpdaterInstance());
    }

    /**
     * testUpdateRequired
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
