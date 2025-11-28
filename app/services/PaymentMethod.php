<?php

namespace App\services;

use App\interfaces\PaymentInterface;

class PaymentMethod implements PaymentInterface
{
    public function pay($amount): string
    {
        return "payment made $amount";
    }
}
