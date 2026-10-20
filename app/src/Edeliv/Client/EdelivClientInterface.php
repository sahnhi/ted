<?php

namespace App\Edeliv\Client;

interface EdelivClientInterface
{
    public function cancelTask(string $taskId): void;
}
