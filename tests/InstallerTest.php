<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use PierInfor\GeoLite\Installer;
use PierInfor\GeoLite\Updater;

/**
 * @covers \PierInfor\GeoLite\Installer::<public>
 */
class InstallerTest extends PFT
{

    const PATH_ASSETS = 'assets/';
    const PATH_ASSET_DB = self::PATH_ASSETS . 'db/';

    /**
     * emptyDbFiles
     * @param string $mask
     * @return void
     */
    protected function emptyDbFiles(string $mask)
    {
        $dbFiles = glob(self::PATH_ASSET_DB . $mask);
        foreach ($dbFiles as $dbFile) {
            @unlink($dbFile);
        }
    }

    /**
     * removeArchives
     * @return void
     */
    protected function removeArchives()
    {
        $this->emptyDbFiles('*.tar.gz');
        $this->emptyDbFiles('*.tar');
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->removeArchives();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    { }

    /**
     * testPostInstall
     * @covers PierInfor\GeoLite\Installer::postInstall
     */
    function testPostInstall()
    {
        $this->emptyDbFiles('*');
        $cityFile = self::PATH_ASSET_DB . Updater::DB_CITY_FILENAME;
        $countryFile = self::PATH_ASSET_DB . Updater::DB_COUNTRY_FILENAME;
        $ansFile = self::PATH_ASSET_DB . Updater::DB_ASN_FILENAME;
        $this->assertFalse(file_exists($cityFile));
        $this->assertFalse(file_exists($countryFile));
        $this->assertFalse(file_exists($ansFile));
        Installer::postInstall();
        $this->assertTrue(file_exists($cityFile));
        $this->assertTrue(file_exists($countryFile));
        $this->assertTrue(file_exists($ansFile));
    }


    /**
     * testOutput
     * @covers PierInfor\GeoLite\Installer::output
     */
    function testOutput()
    {
        $date = date('H:i:s');
        $msg = 'test';
        $expected = sprintf('%s - %s.%s', $date, $msg, "\n");
        $this->expectOutputString($expected);
        Installer::output($msg);
    }
}
