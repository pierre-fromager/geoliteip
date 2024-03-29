<?php

namespace PierInfor\GeoLite;

/**
 * Updater class update free maxmind dbs in mmdb format
 */
class Updater implements Interfaces\UpdaterInterface
{

    const ABS_DB_PATH = __DIR__ . self::DB_PATH;

    /**
     * Current adapter
     *
     * @var String
     */
    private $adapter;

    /**
     * File manager
     *
     * @var FileManager
     */
    private $fileManager;

    /**
     * Instanciate with given locales
     *
     * @param array $locales
     */
    public function __construct()
    {
        $this->fileManager = new FileManager();
        $this->adapter = self::ADAPTER_COUNTRY;
    }

    /**
     * on unset instance
     */
    public function __destruct()
    {
        $this->fileManager  = null;
    }

    /**
     * returns FileManager instance
     *
     * @return FileManager
     */
    public function getFileManager(): FileManager
    {
        return $this->fileManager;
    }

    /**
     * set adapter
     *
     * @param string $adapter
     * @return Updater
     * @throws \Exception
     */
    public function setAdapter(string $adapter = self::ADAPTER_COUNTRY): Updater
    {
        if (!in_array($adapter, self::ADAPTERS)) {
            throw new \Exception('Error: Unkown adapter ' . $adapter);
        }
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * update
     *
     * @return Updater
     */
    public function update(): Updater
    {
        $this->clean();
        $url = self::UPDATERS[$this->adapter];
        $localTarget = self::ABS_DB_PATH . basename($url);
        $this->fileManager->download($url, $localTarget);
        $this->fileManager->ungz($localTarget);
        list($archive, $tar) = explode('.', basename($url));
        $archive = sprintf('%s%s.%s', self::ABS_DB_PATH, $archive, $tar);
        $this->fileManager->untar($archive, self::ABS_DB_PATH);
        $folderList = $this->fileManager->folderList(self::ABS_DB_PATH);
        if (count($folderList) == 1) {
            $this->fileManager->copyFile(
                sprintf('%s/%s', $folderList[0], $this->dbFilename()),
                self::ABS_DB_PATH . $this->dbFilename()
            );
        }
        $this->clean();
        return $this;
    }

    /**
     * return true if current db file date is different from today
     *
     * @return boolean
     */
    public function updateRequired(): bool
    {
        $updatedToday = $this->fileManager->isFileDateToday(
            self::ABS_DB_PATH . $this->dbFilename()
        );
        return (false === $updatedToday);
    }

    /**
     * return db filename for a given adapter
     *
     * @return string
     */
    public function dbFilename(): string
    {
        return self::UPDATERS_DB_FILENAME[$this->adapter];
    }

    /**
     * clean unnattended files and folders in db path
     *
     * @return Updater
     */
    public function clean(): Updater
    {
        $this->fileManager->unlinkFolders(self::ABS_DB_PATH);
        $extCount = count(self::CLEAN_DB_EXTS);
        for ($c = 0; $c < $extCount; $c++) {
            $this->fileManager->unlinkFiles(
                self::ABS_DB_PATH . self::CLEAN_DB_EXTS[$c]
            );
        }
        return $this;
    }
}
