<?php

namespace App\Services\Loyalty;

interface LoyaltyServiceInterface
{
    public function verifyCustomerEligibilityByEmailAndCoupon(string $email, string $coupon);

    public function verifyCouponIsValid(string $coupon);

    public function verifyGroupExistByCode(string $groupCode);
}
