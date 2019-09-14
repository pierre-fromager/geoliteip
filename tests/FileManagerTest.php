<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use PierInfor\GeoLite\FileManager;
use PierInfor\GeoLite\Downloader;

/**
 * @covers \PierInfor\GeoLite\FileManager::<public>
 */
class FileManagerTest extends PFT
{

    const TEST_ENABLE = true;
    const RANDOM_KEYS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const PATH_ASSETS = 'src/assets/';
    const PATH_ASSETS_DB = self::PATH_ASSETS . 'db/';
    const PATH_ASSETS_TESTS = self::PATH_ASSETS . 'tests/';
    const PATH_ASSETS_TESTS_TEMPLATE = self::PATH_ASSETS_TESTS . 'templates/';
    const PATH_ASSETS_TESTS_SANDBOX = self::PATH_ASSETS_TESTS . 'sandbox/';
    const DL_URL = 'http://requestbin.net/ip';
    const DL_TARGET_FILE = self::PATH_ASSETS_TESTS . 'requestbinnetip.txt';
    const UNLINK_EXTS = ['*.txt', '*.dic', '*.dat'];
    const TGZ_TEMPLATE_FILE = 'test.tar.gz';
    const TGZ_TEMPLATE_BAD_FILE = 'bad.tar.gz';
    const TAR_TEMPLATE_FILE = 'test.tar';
    const TAR_TEMPLATE_BAD_FILE = 'bad.tar';

    /**
     * instance
     *
     * @var FileManager
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!self::TEST_ENABLE) {
            $this->markTestSkipped('Test disabled.');
        }
        $this->instance = new FileManager();
        $this->cleanSandbox();
    }

    /**
     * empty sanbox workspace
     *
     * @return void
     */
    protected function cleanSandbox(){
        $this->instance->unlinkFolders(self::PATH_ASSETS_TESTS_SANDBOX . '*');
        $this->instance->unlinkFiles(self::PATH_ASSETS_TESTS_SANDBOX . '*');
        if (!file_exists(self::PATH_ASSETS_TESTS_SANDBOX)) {
            mkdir(self::PATH_ASSETS_TESTS_SANDBOX);
        }
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
     * returns a random string (issued on stackoverflow)
     *
     * @param integer $max
     * @return void
     */
    protected function randomStr($max = 6)
    {
        $i = 0;
        $keyLength = strlen(self::RANDOM_KEYS);
        $str = '';
        for ($c = 0; $c < $max; $c++) {
            $rand = mt_rand(1, $keyLength - 1);
            $str .= self::RANDOM_KEYS[$rand];
        }
        return $str;
    }

    /**
     * generate 3 random nested folders inside a given path
     *
     * @param string $path
     * @return void
     * @todo make a loop with a nested level param to generate deeper
     */
    protected function genRandSubFolder(string $path)
    {
        if (!file_exists($path)) {
            mkdir($path);
        }
        $firstLevelFolder = sprintf('%s%s', $path, $this->randomStr(10));
        mkdir($firstLevelFolder);
        $secondLevelFolder = sprintf(
            '%s/%s',
            $firstLevelFolder,
            $this->randomStr(15)
        );
        mkdir($secondLevelFolder);
        $thirdLevelFolder = sprintf(
            '%s/%s',
            $secondLevelFolder,
            $this->randomStr(15)
        );
        mkdir($thirdLevelFolder);
    }

    /**
     * testInstance
     * @covers PierInfor\GeoLite\FileManager::__construct
     */
    public function testInstance()
    {
        $isGeoInstance = $this->instance instanceof FileManager;
        $this->assertTrue($isGeoInstance);
    }

    /**
     * testGetDownloader
     * @covers PierInfor\GeoLite\FileManager::getDownloader
     */
    public function testGetDownloader()
    {
        $this->assertTrue(
            $this->instance->getDownloader() instanceof Downloader
        );
    }

    /**
     * testDownload
     * @covers PierInfor\GeoLite\FileManager::download
     */
    public function testDownload()
    {
        $this->instance->download(self::DL_URL, self::DL_TARGET_FILE);
        $fileExtist = file_exists(self::DL_TARGET_FILE);
        $this->assertTrue($fileExtist);
        if ($fileExtist) {
            @unlink(self::DL_TARGET_FILE);
        }
    }

    /**
     * testFolderList
     * @covers PierInfor\GeoLite\FileManager::folderList
     */
    public function testFolderList()
    {
        $this->assertEmpty(
            $this->instance->folderList(self::PATH_ASSETS_DB)
        );
        $this->assertTrue(
            is_array($this->instance->folderList(self::PATH_ASSETS))
        );
        $this->assertNotEmpty(
            $this->instance->folderList(self::PATH_ASSETS)
        );
    }

    /**
     * testFileDate
     * @covers PierInfor\GeoLite\FileManager::fileDate
     */
    public function testFileDate()
    {
        $fileDate = $this->instance->fileDate(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE
        );
        $validator = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
        $validate = preg_match($validator, $fileDate) ? true : false;
        $this->assertTrue($validate);
    }

    /**
     * testIsFileDateToday
     * @covers PierInfor\GeoLite\FileManager::isFileDateToday
     */
    public function testIsFileDateToday()
    {
        $copyResult = $this->instance->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE,
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertTrue($copyResult);
        $isTodayFile = $this->instance->isFileDateToday(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertTrue($isTodayFile);
        $fileDateChangeOlder = strtotime("23 April 2005");
        @touch(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE,
            $fileDateChangeOlder
        );
        $isTodayFile = $this->instance->isFileDateToday(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertFalse($isTodayFile);
    }

    /**
     * testUnlinkFiles
     * @covers PierInfor\GeoLite\FileManager::unlinkFiles
     */
    public function testUnlinkFiles()
    {
        $extsCount = count(self::UNLINK_EXTS);
        // Generate random files with mask
        for ($c = 0; $c < $extsCount; $c++) {
            $content = $this->randomStr();
            $ext = substr(self::UNLINK_EXTS[$c], 1);
            $filename = $this->randomStr() . $ext;
            file_put_contents(
                self::PATH_ASSETS_TESTS_SANDBOX . $filename,
                $content
            );
        }
        // Unlink random files by mask
        for ($c = 0; $c < $extsCount; $c++) {
            $this->instance->unlinkFiles(
                self::PATH_ASSETS_TESTS_SANDBOX . self::UNLINK_EXTS[$c]
            );
        }
        // Assert no more files in sandbox
        $this->assertEmpty(glob(self::PATH_ASSETS_TESTS_SANDBOX . '*'));
    }

    /**
     * testFolderList
     * @covers PierInfor\GeoLite\FileManager::deleteFolder
     */
    public function testDeleteFolder()
    {
        $folderName = self::PATH_ASSETS_TESTS_SANDBOX . 'toDelete/';
        $this->genRandSubFolder($folderName);
        $this->instance->deleteFolder($folderName);
        $this->assertEmpty(glob($folderName, GLOB_ONLYDIR));
    }

    /**
     * testFileList
     * @covers PierInfor\GeoLite\FileManager::fileList
     */
    public function testFileList()
    {
        $fileList = $this->instance->fileList(self::PATH_ASSETS_TESTS_TEMPLATE);
        $this->assertTrue(is_array($fileList));
        $this->assertNotEmpty($fileList);
    }

    /**
     * testUnlinkFolders
     * @covers PierInfor\GeoLite\FileManager::unlinkFolders
     */
    public function testUnlinkFolders()
    {
        $folderNameList = ['a/', 'b/', 'c/'];
        foreach ($folderNameList as $folder) {
            $this->genRandSubFolder(self::PATH_ASSETS_TESTS_SANDBOX . $folder);
        }
        $this->instance->unlinkFolders(self::PATH_ASSETS_TESTS_SANDBOX);
        $folderList = glob(self::PATH_ASSETS_TESTS_SANDBOX . '*', GLOB_ONLYDIR);
        $this->assertEmpty($folderList);
    }

    /**
     * testCopyFile
     * @covers PierInfor\GeoLite\FileManager::copyFile
     */
    public function testCopyFile()
    {
        $existBefore  = file_exists(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertFalse($existBefore);
        $copyResult = $this->instance->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE,
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $existAfter  = file_exists(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertTrue($existAfter);
        $this->assertTrue($copyResult);
        if ($existAfter) {
            unlink(self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE);
        }
    }

    /**
     * testUngz
     * @covers PierInfor\GeoLite\FileManager::copyFile
     * @covers PierInfor\GeoLite\FileManager::ungz
     */
    public function testUngz()
    {
        $existBefore  = file_exists(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertFalse($existBefore);
        $copyResult = $this->instance->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE,
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $existAfter  = file_exists(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertTrue($existAfter);
        $this->assertTrue($copyResult);
        $ungzResult = $this->instance->ungz(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $tarExist = file_exists(self::PATH_ASSETS_TESTS_SANDBOX . self::TAR_TEMPLATE_FILE);
        $this->assertTrue($tarExist);
        $this->assertTrue($ungzResult);
    }

    /**
     * testUngzNotFound
     * @covers PierInfor\GeoLite\FileManager::ungz
     */
    public function testUngzNotFound()
    {
        $ungzResult = $this->instance->ungz('');
        $this->assertFalse($ungzResult);
    }

    /**
     * testUngzMalformed
     * @covers PierInfor\GeoLite\FileManager::ungz
     */
    public function testUngzMalformed()
    {
        $copyResult = $this->instance->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_BAD_FILE,
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_BAD_FILE
        );
        $this->assertTrue($copyResult);
        $this->expectException(\Exception::class);
        $ungzResult = $this->instance->ungz(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_BAD_FILE
        );
        $this->assertFalse($ungzResult);
    }

    /**
     * testUntar
     * @covers PierInfor\GeoLite\FileManager::ungz
     * @covers PierInfor\GeoLite\FileManager::untar
     * @bugs https://bugs.php.net/bug.php?id=75101
     */
    public function testUntar()
    {
        // because of bug 
        // phar require <> filename or it fails 
        // with BadMethodException on decompress
        // when called multiple times
        $tgzTarget = sprintf(
            '%s%d-%s',
            self::PATH_ASSETS_TESTS_SANDBOX,
            time(),
            self::TGZ_TEMPLATE_FILE
        );
        $copyResult = $this->instance->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE,
            $tgzTarget
        );
        $this->assertTrue($copyResult);
        $tgzExists = file_exists($tgzTarget);
        $this->assertTrue($tgzExists);
        $ungzResult = $this->instance->ungz($tgzTarget);
        $this->assertTrue($ungzResult);
        $tarFilename = substr(basename($tgzTarget), 0, -3);
        $this->assertTrue(
            file_exists(self::PATH_ASSETS_TESTS_SANDBOX . $tarFilename)
        );
        $untarResult = $this->instance->untar(
            self::PATH_ASSETS_TESTS_SANDBOX . $tarFilename,
            self::PATH_ASSETS_TESTS_SANDBOX
        );
        $this->assertTrue($untarResult);
        $result = $this->instance->untar(
            '',
            self::PATH_ASSETS_TESTS_SANDBOX
        );
        $this->assertFalse($result);
        $result = $this->instance->untar(
            self::PATH_ASSETS_TESTS_SANDBOX . $tarFilename,
            ''
        );
        $this->assertFalse($result);
        $this->cleanSandbox();
    }
}
