<?php

namespace App\Constants;

use ReflectionClass;

class ConstInvoiceStatus
{
    const UNPAID = 'unpaid';
    const PAID = 'paid';

    /**
     * Get all constants
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
