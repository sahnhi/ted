<?php

namespace App\Legacy\Younited;

interface YounitedPaymentInterface
{
    public function authorize();

    public function sessionInitialize(string $authorizeToken);

    public function contractActivate(string $authorizeToken);

    public function contractCancel(string $authorizeToken);

    public function contractRestitute(string $authorizeToken);
}
