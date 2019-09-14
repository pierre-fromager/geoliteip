<?php

namespace PierInfor\GeoLite;

use Composer\Script\Event;
use PierInfor\GeoLite\Updater;

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
        $composer = $event->getComposer();
        $updater = new Updater();
        echo '-- Update maxmind started.' . "\n";
        $updater->setAdapter(Updater::ADAPTER_CITY)->update();
        $updater->setAdapter(Updater::ADAPTER_COUNTRY)->update();
        $updater->setAdapter(Updater::ADAPTER_ASN)->update();
        echo '-- Update maxmind finished.' . "\n";
    }
}
