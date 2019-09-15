<?php

namespace PierInfor\GeoLite;

use Composer\Script\Event;
use PierInfor\GeoLite\Updater;

# Dirty Bugfix Guzzle from Composer for missing choose_handler error
require 'vendor/guzzlehttp/guzzle/src/functions.php';

class Installer
{

    /**
     * postInstall
     *
     * provides access to the current Composer instance
     * run any post install tasks here
     *
     * @param Composer\Script\Event $event
     */
    public static function postInstall(Event $event)
    {
        //$composer = $event->getComposer();
        $updater = new Updater();
        $updater
            ->getFileManager()
            ->getDownloader()
            ->setAdapter(Downloader::ADAPTER_CURL)
            ->displayProgress(true);
        self::ouput('Update maxmind databases started');
        self::ouput('Updating city ', false);
        $updater->setAdapter(Updater::ADAPTER_CITY)->update();
        self::ouput('Updating country ', false);
        $updater->setAdapter(Updater::ADAPTER_COUNTRY)->update();
        self::ouput('Updating asn ', false);
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
        echo sprintf('     %s - %s.%s', date('H:i:s'), $msg, ($newline) ? "\n" : '');
    }
}
