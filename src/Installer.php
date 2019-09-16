<?php

namespace PierInfor\GeoLite;

use PierInfor\GeoLite\Updater;

class Installer
{



    /**
     * postInstall
     *
     * provides access to the current Composer instance
     * run any post install tasks here
     */
    public static function postInstall()
    {
        echo "\n";
        $updater = new Updater();
        $updater
            ->getFileManager()
            ->getDownloader()
            ->setAdapter(Downloader::ADAPTER_CURL)
            ->displayProgress(true);
        self::ouput('Update maxmind databases started');
        self::ouput('Updating city');
        $updater->setAdapter(Updater::ADAPTER_CITY)->update();
        self::ouput('Updating country');
        $updater->setAdapter(Updater::ADAPTER_COUNTRY)->update();
        self::ouput('Updating asn');
        $updater->setAdapter(Updater::ADAPTER_ASN)->update();
        self::ouput('Update maxmind databases finished');
    }

    /**
     * console output
     *
     * @param string $msg
     * @return void
     */
    protected static function ouput(string $msg, $newline = true)
    {
        echo sprintf('%s - %s.%s', date('H:i:s'), $msg, ($newline) ? "\n" : '');
    }
}
