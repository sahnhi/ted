<?php

namespace App\Doctrine\Entity\Sylius;

use Sylius\Component\Customer\Model\CustomerGroupInterface as BaseCustomerGroupInterface;

interface CustomerGroupInterface extends BaseCustomerGroupInterface
{
    public const GROUP_SPONSOR_CODE = 'SPONSOR';
    public const GROUP_SPONSOR_RECOMMENDEE_CODE = 'SPONSOR_RECOMMENDEE';
    public const GROUP_RECOMMENDEE_CODE = 'RECOMMENDEE';
}
