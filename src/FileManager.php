<?php

namespace PierInfor\GeoLite;

use PierInfor\GeoLite\Downloader;

/**
 * FileManager class to manage files
 */
class FileManager implements Interfaces\FileManagerInterface
{

    /**
     * Downloader instance
     *
     * @var Downloader
     */
    private $downloader;

    /**
     * Instanciate
     */
    public function __construct()
    {
        $this->downloader = new Downloader();
    }

    /**
     * returns downloader instance
     *
     * @return Downloader
     */
    public function getDownloader(): Downloader
    {
        return $this->downloader;
    }

    /**
     * download a file for a given url and filename
     *
     * @param string $url
     * @param string $toFilename
     * @return FileManager
     */
    public function download(string $url, string $toFilename): FileManager
    {
        $this->downloader->download($url, $toFilename);
        return $this;
    }

    /**
     * uncompress a tar gz file to a tar archive and return true if ok
     *
     * @param string $tgzFilename
     * @return boolean
     * @throws \Exception
     */
    public function ungz(string $tgzFilename): bool
    {
        $result = false;
        if (empty($tgzFilename) || !file_exists($tgzFilename)) {
            return $result;
        }
        try {
            (new \PharData($tgzFilename))->decompress();
        } catch (\Exception $e) {
            if ($e instanceof \UnexpectedValueException) {
                throw new \Exception('Malformed gzip');
            }
        }
        return true;
    }

    /**
     * extract a tar archive to a target folder
     *
     * @param string $tarFilename
     * @param string $targetFolder
     * @return boolean
     */
    public function untar(string $tarFilename, string $targetFolder): bool
    {
        if (empty($tarFilename)
            || empty($targetFolder)
            || !file_exists($tarFilename)
        ) {
            return false;
        }
        (new \PharData($tarFilename))->extractTo($targetFolder);
        return true;
    }

    /**
     * copy file from to and returns operation status
     *
     * @param string $from
     * @param string $to
     * @return boolean
     */
    public function copyFile(string $from, string $to): bool
    {
        return @copy($from, $to);
    }

    /**
     * returns folders list for a given path
     *
     * @param string $path
     * @return array
     */
    public function folderList(string $path): array
    {
        if (false === is_dir($path)) {
            return [];
        }
        $list = @glob($path . '*', GLOB_ONLYDIR);
        return (false === $list) ? [] : $list;
    }

    /**
     * returns file list for a given path
     *
     * @param string $path
     * @return array
     */
    public function fileList(string $path): array
    {
        return @glob(rtrim($path, '/') . '/*.{*}', GLOB_BRACE);
    }

    /**
     * returns Y-m-d modification file date for a $path
     *
     * @param string $path
     * @return string
     */
    public function fileDate(string $path): string
    {
        clearstatcache();
        if (!file_exists($path)) {
            return '1971-01-14';
        }
        return date('Y-m-d', filemtime($path));
    }

    /**
     * returns true if modified file date is today
     *
     * @param string $path
     * @return string
     */
    public function isFileDateToday(string $path): bool
    {
        return (date('Y-m-d') == $this->fileDate($path));
    }

    /**
     * unlink multiples files from a path and a mask
     *
     * @param string $mask
     * @return boolean
     */
    public function unlinkFiles(string $mask): bool
    {
        if (empty($mask)) {
            return false;
        }
        if (false === is_dir(dirname($mask))) {
            return false;
        }
        $toDelete = @glob($mask);
        if (false === $toDelete) {
            return false;
        }
        if ($toDelete == []) {
            return true;
        }
        $toDeleteCount = count($toDelete);
        $errors = 0;
        for ($c = 0; $c < $toDeleteCount; $c++) {
            $errors += (@unlink($toDelete[$c])) ? 0 : 1;
        }
        return ($errors === 0);
    }

    /**
     * unlink multiples folders even if not empty from a path
     *
     * @param string $mask
     * @return boolean
     */
    public function unlinkFolders(string $path): bool
    {
        $toDelete = $this->folderList($path);
        $toDeleteCount = count($toDelete);
        $errors = 0;
        for ($c = 0; $c < $toDeleteCount; $c++) {
            $errors += ($this->deleteFolder($toDelete[$c])) ?  0 : 1;
        }
        return ($errors === 0);
    }

    /**
     * delete a not empty folder
     *
     * @param string $path
     * @return boolean
     */
    public function deleteFolder(string $path): bool
    {
        $errors = 0;
        $fsItNoDots = \FilesystemIterator::SKIP_DOTS;
        $rdiT = new \RecursiveDirectoryIterator($path, $fsItNoDots);
        $riiFirstChild = \RecursiveIteratorIterator::CHILD_FIRST;
        $rii = new \RecursiveIteratorIterator($rdiT, $riiFirstChild);
        foreach ($rii as $file) {
            if ($file->isDir()) {
                $errors += (@rmdir($file->getPathname())) ? 0 : 1;
            } else {
                $errors += (@unlink($file->getPathname())) ? 0 : 1;
            }
        }
        $errors += (@rmdir($path)) ? 0 : 1;
        return ($errors === 0);
    }
}
