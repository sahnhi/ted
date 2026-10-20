<?php

namespace App\ViewRepository;

use App\View\CartSummaryView;

interface CartViewRepositoryInterface
{
    public function getOneByToken(string $orderToken, bool $withVariants = false): CartSummaryView;

    public function getOneByEmailAndNumber(string $email, string $number, bool $withVariants = false): CartSummaryView;
}
