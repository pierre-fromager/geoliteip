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
     * on unset instance
     */
    public function __destruct()
    {
        $this->client = null;
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
     */
    public function ungz(string $tgzFilename): bool
    {
        $result = false;
        if (empty($tgzFilename) || !file_exists($tgzFilename)) {
            return $result;
        }
        (new \PharData($tgzFilename))->decompress();
        return true;
    }

    /**
     * extract a tar archive to a target folder
     *
     * @param string $tarFilename
     * @param string $targetFolder
     * @return boolean
     * @return void
     *
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
        return @glob($path . '*', GLOB_ONLYDIR);
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
     * @return void
     */
    public function unlinkFiles(string $mask)
    {
        $toDelete = glob($mask);
        $toDeleteCount = count($toDelete);
        for ($c = 0; $c < $toDeleteCount; $c++) {
            @unlink($toDelete[$c]);
        }
    }

    /**
     * unlink multiples folders even if not empty from a path
     *
     * @param string $mask
     * @return void
     */
    public function unlinkFolders(string $path)
    {
        $toDelete = $this->folderList($path);
        $toDeleteCount = count($toDelete);
        for ($c = 0; $c < $toDeleteCount; $c++) {
            $this->deleteFolder($toDelete[$c]);
        }
    }

    /**
     * delete a not empty folder
     *
     * @param string $path
     * @return boolean
     */
    public function deleteFolder(string $path): bool
    {
        $fsItNoDots = \FilesystemIterator::SKIP_DOTS;
        $rdiT = new \RecursiveDirectoryIterator($path, $fsItNoDots);
        $riiFirstChild = \RecursiveIteratorIterator::CHILD_FIRST;
        $rii = new \RecursiveIteratorIterator($rdiT, $riiFirstChild);
        foreach ($rii as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
        return @rmdir($path);
    }
}
