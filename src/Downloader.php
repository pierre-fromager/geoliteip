<?php

namespace PierInfor\GeoLite;

/**
 * Downloader class let download files
 */
class Downloader implements Interfaces\DownloaderInterface
{

    /**
     * factory adapter
     *
     * @var String
     */
    private $adapter;

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
        $this->setAdapter();
    }

    /**
     * on unset instance
     */
    public function __destruct()
    {
        $this->client  = null;
    }

    /**
     * set adapter for download factory
     *
     * @param string $adapter
     * @return Downloader
     */
    public function setAdapter(string $adapter = self::ADAPTER_CURL): Downloader
    {
        if (!in_array($adapter, self::ADAPTERS)) {
            throw new \Exception('Downloader - bad adapter');
        }
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * download factory
     *
     * @param string $url
     * @param string $toFilename
     * @return Downloader
     */
    public function download(string $url, string $toFilename): Downloader
    {
        switch ($this->adapter) {
            case self::ADAPTER_CONTENTS:
                $this->contentsDownload($url, $toFilename);
                break;
            case self::ADAPTER_CURL:
                $this->curlDownload($url, $toFilename);
                break;
        }
        return $this;
    }

    /**
     * download a file using file_get_content
     *
     * @param string $url
     * @param string $toFilename
     * @return Downloader
     */
    public function contentsDownload(string $url, string $toFilename): Downloader
    {
        $headers = get_headers($url);
        $statusCode = 0;
        if (isset($headers[0])) {
            $header = $headers[0];
            preg_match("/^HTTP.+\s(\d\d\d)\s/", $header, $m);
            $statusCode = $m[1];
        }
        if ($statusCode != 200) {
            throw new \Exception('Bad http code : ' . $statusCode);
        }
        file_put_contents($toFilename, file_get_contents($url));
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
     * @return Downloader
     */
    public function curlDownload(string $url, string $toFilename): Downloader
    {
        touch($toFilename, 0777);
        $fp = fopen($toFilename, 'wba+');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, self::BUFFER_SIZE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [$this, self::DOWNLOAD_CALLBACK]);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_exec($ch);
        if (!curl_errno($ch)) {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($statusCode != 200) {
                throw new \Exception('Bad http code : ' . $statusCode);
            }
        }
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
                echo self::WHEELS[$this->progress % 4]
                    . ' ' . $this->getProgress() . "%\r";
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
