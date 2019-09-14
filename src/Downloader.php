<?php

namespace PierInfor\GeoLite;

use GuzzleHttp\Client;

/**
 * Downloader class let download files
 */
class Downloader implements Interfaces\DownloaderInterface
{

    /**
     * Http client
     *
     * @var Client
     */
    private $client;

    /**
     * Download progress percent
     *
     * @var Integer
     */
    private $progress;

    /**
     * Download progress percent
     *
     * @var Boolean
     */
    private $showProgress;

    /**
     * Instanciate
     */
    public function __construct()
    {
        $this->showProgress = false;
        $this->client = new Client();
    }

    /**
     * on unset instance
     */
    public function __destruct()
    {
        $this->client  = null;
    }

    /**
     * download a file using guzzle
     *
     * @param string $url
     * @param string $toFilename
     * @return Downloader
     */
    public function guzzleDownload(string $url, string $toFilename): Downloader
    {
        $this->client->get($url, ['save_to' => $toFilename]);
        return $this;
    }

    /**
     * enable progress output when using curlDownload
     *
     * @param boolean $show
     * @return Downloader
     */
    public function displayProgress(bool $show = false): Downloader
    {
        $this->showProgress = $show;
        return $this;
    }

    /**
     * download a file using curl
     *
     * @param string $url
     * @param string $toFilename
     * @return FileManager
     */
    public function curlDownload(string $url, string $toFilename): Downloader
    {
        $fp = fopen($toFilename, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, self::BUFFER_SIZE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [$this, self::DOWNLOAD_CALLBACK]);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $this;
    }

    /**
     * download progress callback
     *
     * @param Ressource $resource
     * @param integer $download_size
     * @param integer $downloaded
     * @param integer $upload_size
     * @param integer $uploaded
     * @return void
     */
    protected function downloadProgress($resource, int $download_size, int $downloaded, int $upload_size, int $uploaded)
    {
        if ($download_size > 0) {
            $this->progress = ($downloaded / $download_size) * 100;
            if ($this->showProgress === true) {
                echo $this->getProgress() . "%\n";
            }
        }
    }

    /**
     * returns progress value
     *
     * @return integer
     */
    protected function getProgress(): int
    {
        return $this->progress;
    }
}
