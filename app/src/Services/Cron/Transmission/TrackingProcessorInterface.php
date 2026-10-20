<?php

namespace App\Services\Cron\Transmission;

interface TrackingProcessorInterface extends RecapProcessorInterface
{
    public const EXTENSION_AVAILABLE = ['CSV', 'TXT'];
    public const TRACKING_LOCAL_FOLDER = 'tracking/';
    public const TRACKING_GOOGLE_FOLDER = 'Tracking/';
    public const CSV_ORDER_NUMBER = 8;
    public const CSV_SHIPPING_METHOD = 3;
    public const CSV_TRACKING_URL = 14;
    public const CSV_TRACKING_NUMBER = 13;
}
