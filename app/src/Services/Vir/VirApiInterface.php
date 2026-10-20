<?php

namespace App\Services\Vir;

interface VirApiInterface
{
    public function getToken(): string;

    public function getUrlTracking($number, $token): string;

    public function releaseToken($token);
}
