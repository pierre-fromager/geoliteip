<?php

namespace PierInfor\GeoLite;

use PierInfor\GeoLite\Updater;
use PierInfor\GeoLite\Downloader;

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
        $cityUpdateError = false;
        $updater = new Updater();
        $updater
            ->getFileManager()
            ->getDownloader()
            ->setAdapter(Downloader::ADAPTER_CURL)
            ->displayProgress(true);
        self::output('Update maxmind databases started');
        try {
            self::output('Updating city');
            $updater->setAdapter(Updater::ADAPTER_CITY)->update();
        } catch (\Exception $e) {
            $cityUpdateError = true;
            self::output('Download from maxmind failed : ' . $e->getMessage());
        }
        if (!$cityUpdateError) {
            self::output('Updating country');
            $updater->setAdapter(Updater::ADAPTER_COUNTRY)->update();
            self::output('Updating asn');
            $updater->setAdapter(Updater::ADAPTER_ASN)->update();
        }
        self::output('Update maxmind databases finished');
    }

    /**
     * console output
     *
     * @param string $msg
     * @param boolean $newline
     * @return void
     */
    public static function output(string $msg, $newline = true)
    {
        echo sprintf('%s - %s.%s', date('H:i:s'), $msg, ($newline) ? "\n" : '');
    }
}
