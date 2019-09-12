<?php

namespace PierInfor;

use PierInfor\GeoLiteIp;

class GeoLiteIpSample extends GeoLiteIp
{
    
    public function __construct($locales = ['fr'])
    {
        parent::__construct($locales);
    }

    public function byCities(int $colSort = 1): GeoLiteIp
    {
        try {
            $this
                ->setAdapter(self::ADAPTER_COUNTRY)
                ->process()
                ->sort($colSort);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $this;
    }

    public function byCountries(int $colSort = 1): GeoLiteIp
    {
        try {
            $this
                ->setAdapter(self::ADAPTER_CITY)
                ->process()
                ->sort($colSort);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $this;
    }
}
