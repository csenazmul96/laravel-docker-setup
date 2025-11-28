<?php

namespace App\interfaces;

interface PaymentInterface
{
    public function pay($amount);
}
