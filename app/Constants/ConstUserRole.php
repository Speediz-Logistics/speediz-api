<?php

namespace App\Constants;

use ReflectionClass;

class ConstUserRole
{
    const ADMIN = 1;
    const EMPLOYEE = 2;
    const VENDOR = 3;

    const DELIVERY = 4;


    /**
     * Get all constants
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
