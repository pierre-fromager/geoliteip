<?php

namespace PierInfor\GeoLite\Interfaces;

use PierInfor\GeoLite\Downloader;

/**
 * @codeCoverageIgnore
 */
interface DownloaderInterface
{
    const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13';
    const DOWNLOAD_CALLBACK = 'downloadProgress';
    const BUFFER_SIZE = 10485764;

    public function __construct();

    public function __destruct();

    public function guzzleDownload(string $url, string $toFilename): Downloader;

    public function displayProgress(bool $show = false): Downloader;

    public function curlDownload(string $url, string $toFilename): Downloader;
}
