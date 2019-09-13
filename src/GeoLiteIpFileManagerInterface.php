<?php

namespace PierInfor\GeoLite;

interface GeoLiteIpFileManagerInterface
{

    public function __construct();

    public function download(string $url, string $toFilename);

    public function ungz(string $tgzFilename);

    public function untar(string  $tarFilename, string $targetFolder);

    public function copyFile(string $from, string $to): bool;

    public function folderList(string $path): array;

    public function unlinkFiles(string $mask);

    public function unlinkFolders(string $path);

    public function deleteFolder(string $path): bool;
}
