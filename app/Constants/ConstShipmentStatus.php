<?php

namespace App\Constants;

use ReflectionClass;

class ConstShipmentStatus
{
    const COMPLETED = 'completed';
    const PENDING = 'pending';
    const IN_TRANSIT = 'in_transit';
    const CANCELLED = 'cancelled';

    /**
     * Get all constants
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
