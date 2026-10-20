<?php

namespace App\Generator;

interface InvoiceNumberGenerator
{
    public function generate($date): string;
}
