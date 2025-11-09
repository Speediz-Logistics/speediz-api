<?php

namespace App\Constants;

use ReflectionClass;

class ConstRollbackType
{
    const DELIVERY_ROLLBACK = 'delivery_rollback';
    const DELIVERY_PENDING = 'delivery_pending';
    const DELIVERY_CANCEL = 'delivery_cancel';

    /**
     * Get all constants
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
