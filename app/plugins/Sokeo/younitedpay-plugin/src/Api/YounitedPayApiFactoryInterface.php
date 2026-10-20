<?php

namespace Sokeo\SyliusYounitedpayPlugin\Api;

interface YounitedPayApiFactoryInterface
{
    public function create(): YounitedpayApiInterface;
}
