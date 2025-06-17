<?php

namespace App\Constants;

use ReflectionClass;

class ConstVendorRequestStatus
{
    const Pending = 1;
    const APPROVED = 2;
    const DECLINED = 3;


    /**
     * Get all constants
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
