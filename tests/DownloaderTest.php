<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use PierInfor\GeoLite\FileManager;
use PierInfor\GeoLite\Downloader;

/**
 * @covers \PierInfor\GeoLite\Downloader::<public>
 */
class DownloaderTest extends PFT
{

    const TEST_ENABLE = true;
    const PATH_ASSETS = 'assets/';
    const PATH_ASSETS_TESTS = self::PATH_ASSETS . 'tests/';
    const PATH_ASSETS_TESTS_SANDBOX = self::PATH_ASSETS_TESTS . 'sandbox/';
    const DL_URL = 'http://requestbin.net/ip';
    const FILE_GUZZLE = 'guzzle.txt';
    const FILE_CURL = 'curl.txt';
    const STAR = '*';

    /**
     * instance
     *
     * @var Downloader
     */
    protected $instance;

    /**
     * instance
     *
     * @var FileManager
     */
    protected $fileManager;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!self::TEST_ENABLE) {
            $this->markTestSkipped('Test disabled.');
        }
        $this->instance = new Downloader();
        $this->fileManager = new FileManager();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->instance = null;
    }

    /**
     * get any method from a class to be invoked whatever the scope
     *
     * @param String $name
     * @return void
     */
    protected static function getMethod(string $name)
    {
        $class = new \ReflectionClass('PierInfor\GeoLite\Downloader');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        unset($class);
        return $method;
    }

    /**
     * empty sanbox workspace
     *
     * @return void
     */
    protected function cleanSandbox()
    {
        $this->fileManager->unlinkFolders(self::PATH_ASSETS_TESTS_SANDBOX . self::STAR);
        $this->fileManager->unlinkFiles(self::PATH_ASSETS_TESTS_SANDBOX . self::STAR);
        if (!file_exists(self::PATH_ASSETS_TESTS_SANDBOX)) {
            mkdir(self::PATH_ASSETS_TESTS_SANDBOX);
        }
    }

    /**
     * testInstance
     * @covers PierInfor\GeoLite\Downloader::__construct
     */
    public function testInstance()
    {
        $this->assertTrue(
            $this->instance instanceof Downloader
        );
    }

    /**
     * testDisplayProgress
     * @covers PierInfor\GeoLite\Downloader::displayProgress
     */
    public function testDisplayProgress()
    {
        $this->assertTrue(
            $this->instance->displayProgress(true) instanceof Downloader
        );
        $this->assertTrue(
            $this->instance->displayProgress(false) instanceof Downloader
        );
    }

    /**
     * testContentsDownload
     * @covers PierInfor\GeoLite\Downloader::contentsDownload
     */
    public function testContentsDownload()
    {
        $this->cleanSandbox();
        $targetFile = self::PATH_ASSETS_TESTS_SANDBOX . self::FILE_GUZZLE;
        $this->instance->contentsDownload(self::DL_URL, $targetFile);
        $this->assertTrue(file_exists($targetFile));
    }

    /**
     * testCurlDownload
     * @covers PierInfor\GeoLite\Downloader::curlDownload
     */
    public function testCurlDownload()
    {
        $this->cleanSandbox();
        $targetFile = self::PATH_ASSETS_TESTS_SANDBOX . self::FILE_CURL;
        $this->instance
            ->displayProgress(true)
            ->curlDownload(self::DL_URL, $targetFile);
        $this->assertTrue(file_exists($targetFile));
        $this->cleanSandbox();
        $this->instance
            ->displayProgress(false)
            ->curlDownload(self::DL_URL, $targetFile);
        $this->assertTrue(file_exists($targetFile));
        $this->cleanSandbox();
    }

    /**
     * testDownloadProgressWithOutput
     * @covers PierInfor\GeoLite\Downloader::downloadProgress
     * @covers PierInfor\GeoLite\Downloader::getProgress
     */
    public function testDownloadProgressWithOutput()
    {
        $this->expectOutputString("\ 10%\r");
        $this->instance->displayProgress(true);
        $args = [null, 100, 10, 0, 0];
        self::getMethod('downloadProgress')->invokeArgs($this->instance, $args);
        $value = self::getMethod('getProgress')->invokeArgs($this->instance, []);
        $this->assertTrue(is_int($value));
        $this->assertEquals($value, 10);
    }

    /**
     * testDownloadProgressWithoutOutput
     * @covers PierInfor\GeoLite\Downloader::downloadProgress
     * @covers PierInfor\GeoLite\Downloader::getProgress
     */
    public function testDownloadProgressWithoutOutput()
    {
        $this->expectOutputString('');
        $this->instance->displayProgress(false);
        $args = [null, 1000, 100, 0, 0];
        self::getMethod('downloadProgress')->invokeArgs($this->instance, $args);
        $value = self::getMethod('getProgress')->invokeArgs($this->instance, []);
        $this->assertTrue(is_int($value));
        $this->assertEquals($value, 10);
    }
}
