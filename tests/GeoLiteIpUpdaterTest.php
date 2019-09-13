<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use PierInfor\GeoLiteIpUpdater;

/**
 * @covers \PierInfor\GeoLiteIpUpdater::<public>
 */
class GeoLiteIpUpdaterTest extends PFT
{
    const PATH_ASSETS = 'src/assets/';
    const ASSET_IP_LIST = self::PATH_ASSETS . 'iplist.txt';
    const FIRST_IP = '104.37.188.20';
    const TEST_ENABLE = false;

    /**
     * instance
     *
     * @var GeoLiteIpUpdater
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
        $this->geoInst = new GeoLiteIpUpdater();
    }

    /**
     * facility to check instanceof
     *
     * @return boolean
     */
    protected function isUpdaterInstance(): bool
    {
        return $this->geoInst instanceof GeoLiteIpUpdater;
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
     * @covers PierInfor\GeoLiteIpUpdater::__construct
     */
    public function testInstance()
    {
        $this->assertTrue($this->isUpdaterInstance());
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
     * @covers PierInfor\GeoLiteIpUpdater::setAdapter
     */
    public function testSetAdapter()
    {
        $this->geoInst->setAdapter(GeoLiteIpUpdater::ADAPTER_CITY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(GeoLiteIpUpdater::ADAPTER_COUNTRY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(GeoLiteIpUpdater::ADAPTER_ASN);
        $this->assertTrue($this->isUpdaterInstance());
    }

    /**
     * testClean
     * @covers PierInfor\GeoLiteIpUpdater::clean
     */
    public function testClean()
    {
        $this->geoInst->clean();
        $this->geoInst->setAdapter(GeoLiteIpUpdater::ADAPTER_CITY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(GeoLiteIpUpdater::ADAPTER_COUNTRY);
        $this->assertTrue($this->isUpdaterInstance());
        $this->geoInst->setAdapter(GeoLiteIpUpdater::ADAPTER_ASN);
        $this->assertTrue($this->isUpdaterInstance());
    }

    /**
     * testUpdateCity
     * @covers PierInfor\GeoLiteIpUpdater::update
     */
    public function testUpdateCity()
    {
        $this->geoInst->setAdapter(GeoLiteIpUpdater::ADAPTER_CITY)->update();
        $this->assertTrue($this->isUpdaterInstance());
    }

    /**
     * testUpdateRequired
     * @covers PierInfor\GeoLiteIpUpdater::updateRequired
     */
    public function testUpdateRequired()
    {
        $requireCityUpdate = $this->geoInst
            ->setAdapter(GeoLiteIpUpdater::ADAPTER_CITY)
            ->updateRequired();
        $this->assertTrue(is_bool($requireCityUpdate));
    }
}
