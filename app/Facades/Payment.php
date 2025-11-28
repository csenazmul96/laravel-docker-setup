<?php
namespace App\Facades;

use App\interfaces\PaymentInterface;
use Illuminate\Support\Facades\Facade;

class Payment extends Facade
{
     protected static function getFacadeAccessor(): string
     {
         return PaymentInterface::class;
     }
}
