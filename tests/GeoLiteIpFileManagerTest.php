<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PFT;
use PierInfor\GeoLiteIpFileManager;

/**
 * @covers \PierInfor\GeoLiteIpFileManager::<public>
 */
class GeoLiteIpFileManagerTest extends PFT
{

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
    const TEST_ENABLE = true;

    /**
     * instance
     *
     * @var GeoLiteIpFileManager
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
        $this->geoInst = new GeoLiteIpFileManager();
        $this->cleanSandbox();
    }

    /**
     * empty sanbox workspace
     *
     * @return void
     */
    protected function cleanSandbox(){
        $this->geoInst->unlinkFolders(self::PATH_ASSETS_TESTS_SANDBOX . '*');
        $this->geoInst->unlinkFiles(self::PATH_ASSETS_TESTS_SANDBOX . '*');
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
        $this->geoInst = null;
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
     * @covers PierInfor\GeoLiteIpFileManager::__construct
     */
    public function testInstance()
    {
        $isGeoInstance = $this->geoInst instanceof \PierInfor\GeoLiteIpFileManager;
        $this->assertTrue($isGeoInstance);
    }

    /**
     * testDownload
     * @covers PierInfor\GeoLiteIpFileManager::download
     */
    public function testDownload()
    {
        $this->geoInst->download(self::DL_URL, self::DL_TARGET_FILE);
        $fileExtist = file_exists(self::DL_TARGET_FILE);
        $this->assertTrue($fileExtist);
        if ($fileExtist) {
            @unlink(self::DL_TARGET_FILE);
        }
    }

    /**
     * testFolderList
     * @covers PierInfor\GeoLiteIpFileManager::folderList
     */
    public function testFolderList()
    {
        $this->assertEmpty(
            $this->geoInst->folderList(self::PATH_ASSETS_DB)
        );
        $this->assertTrue(
            is_array($this->geoInst->folderList(self::PATH_ASSETS))
        );
        $this->assertNotEmpty(
            $this->geoInst->folderList(self::PATH_ASSETS)
        );
    }

    /**
     * testFileDate
     * @covers PierInfor\GeoLiteIpFileManager::fileDate
     */
    public function testFileDate()
    {
        $fileDate = $this->geoInst->fileDate(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE
        );
        $validator = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
        $validate = preg_match($validator, $fileDate) ? true : false;
        $this->assertTrue($validate);
    }

    /**
     * testIsFileDateToday
     * @covers PierInfor\GeoLiteIpFileManager::isFileDateToday
     */
    public function testIsFileDateToday()
    {
        $isTodayFile = $this->geoInst->isFileDateToday(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE
        );
        $this->assertFalse($isTodayFile);
        $copyResult = $this->geoInst->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE,
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertTrue($copyResult);
        $isTodayFile = $this->geoInst->isFileDateToday(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertTrue($isTodayFile);
    }

    /**
     * testUnlinkFiles
     * @covers PierInfor\GeoLiteIpFileManager::unlinkFiles
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
            $this->geoInst->unlinkFiles(
                self::PATH_ASSETS_TESTS_SANDBOX . self::UNLINK_EXTS[$c]
            );
        }
        // Assert no more files in sandbox
        $this->assertEmpty(glob(self::PATH_ASSETS_TESTS_SANDBOX . '*'));
    }

    /**
     * testFolderList
     * @covers PierInfor\GeoLiteIpFileManager::deleteFolder
     */
    public function testDeleteFolder()
    {
        $folderName = self::PATH_ASSETS_TESTS_SANDBOX . 'toDelete/';
        $this->genRandSubFolder($folderName);
        $this->geoInst->deleteFolder($folderName);
        $this->assertEmpty(glob($folderName, GLOB_ONLYDIR));
    }

    /**
     * testFileList
     * @covers PierInfor\GeoLiteIpFileManager::fileList
     */
    public function testFileList()
    {
        $fileList = $this->geoInst->fileList(self::PATH_ASSETS_TESTS_TEMPLATE);
        $this->assertTrue(is_array($fileList));
        $this->assertNotEmpty($fileList);
    }

    /**
     * testUnlinkFolders
     * @covers PierInfor\GeoLiteIpFileManager::unlinkFolders
     */
    public function testUnlinkFolders()
    {
        $folderNameList = ['a/', 'b/', 'c/'];
        foreach ($folderNameList as $folder) {
            $this->genRandSubFolder(self::PATH_ASSETS_TESTS_SANDBOX . $folder);
        }
        $this->geoInst->unlinkFolders(self::PATH_ASSETS_TESTS_SANDBOX);
        $folderList = glob(self::PATH_ASSETS_TESTS_SANDBOX . '*', GLOB_ONLYDIR);
        $this->assertEmpty($folderList);
    }

    /**
     * testCopyFile
     * @covers PierInfor\GeoLiteIpFileManager::copyFile
     */
    public function testCopyFile()
    {
        $existBefore  = file_exists(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertFalse($existBefore);
        $copyResult = $this->geoInst->copyFile(
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
     * @covers PierInfor\GeoLiteIpFileManager::copyFile
     * @covers PierInfor\GeoLiteIpFileManager::ungz
     */
    public function testUngz()
    {
        $existBefore  = file_exists(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertFalse($existBefore);
        $copyResult = $this->geoInst->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE,
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $existAfter  = file_exists(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $this->assertTrue($existAfter);
        $this->assertTrue($copyResult);
        $ungzResult = $this->geoInst->ungz(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_FILE
        );
        $tarExist = file_exists(self::PATH_ASSETS_TESTS_SANDBOX . self::TAR_TEMPLATE_FILE);
        $this->assertTrue($tarExist);
        $this->assertTrue($ungzResult);
    }

    /**
     * testUngzNotFound
     * @covers PierInfor\GeoLiteIpFileManager::ungz
     */
    public function testUngzNotFound()
    {
        $ungzResult = $this->geoInst->ungz('');
        $this->assertFalse($ungzResult);
    }

    /**
     * testUngzMalformed
     * @covers PierInfor\GeoLiteIpFileManager::ungz
     */
    public function testUngzMalformed()
    {
        $copyResult = $this->geoInst->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_BAD_FILE,
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_BAD_FILE
        );
        $this->assertTrue($copyResult);
        $this->expectException(\Exception::class);
        $ungzResult = $this->geoInst->ungz(
            self::PATH_ASSETS_TESTS_SANDBOX . self::TGZ_TEMPLATE_BAD_FILE
        );
        $this->assertFalse($ungzResult);
    }

    /**
     * testUntar
     * @covers PierInfor\GeoLiteIpFileManager::ungz
     * @covers PierInfor\GeoLiteIpFileManager::untar
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
        $copyResult = $this->geoInst->copyFile(
            self::PATH_ASSETS_TESTS_TEMPLATE . self::TGZ_TEMPLATE_FILE,
            $tgzTarget
        );
        $this->assertTrue($copyResult);
        $tgzExists = file_exists($tgzTarget);
        $this->assertTrue($tgzExists);
        $ungzResult = $this->geoInst->ungz($tgzTarget);
        $this->assertTrue($ungzResult);
        $tarFilename = substr(basename($tgzTarget), 0, -3);
        $this->assertTrue(
            file_exists(self::PATH_ASSETS_TESTS_SANDBOX . $tarFilename)
        );
        $untarResult = $this->geoInst->untar(
            self::PATH_ASSETS_TESTS_SANDBOX . $tarFilename,
            self::PATH_ASSETS_TESTS_SANDBOX
        );
        $this->assertTrue($untarResult);
        $result = $this->geoInst->untar(
            '',
            self::PATH_ASSETS_TESTS_SANDBOX
        );
        $this->assertFalse($result);
        $result = $this->geoInst->untar(
            self::PATH_ASSETS_TESTS_SANDBOX . $tarFilename,
            ''
        );
        $this->assertFalse($result);
        $this->cleanSandbox();
    }
}
