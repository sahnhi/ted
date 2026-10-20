<?php

declare(strict_types=1);

namespace App\Analytic\Client;

use App\Analytic\Model\Order;
use App\Analytic\Model\User;
use App\Analytic\Model\UsersList;

interface AnalyticClient
{
    public function sendUser(User $user): void;

    public function sendOrder(Order $order): void;

    public function sendBatchUsers(UsersList $usersList): void;
}
