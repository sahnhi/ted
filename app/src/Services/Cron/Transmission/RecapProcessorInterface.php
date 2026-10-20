<?php

namespace App\Services\Cron\Transmission;

interface RecapProcessorInterface
{
    public const FTP_SPIDY = 'Spidy';
    public const FTP_VIR = 'Vir';
    public const GOOGLE_FOLDER_TODO = 'Todo/';
    public const GOOGLE_FOLDER_IGNORED = 'Ignored/';

    public function prepare(): void;

    public function process(): void;
}
