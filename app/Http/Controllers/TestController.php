<?php

namespace App\Http\Controllers;


use App\Facades\Payment;

class TestController extends Controller
{

    public function index(){
         return Payment::pay(20);
    }
}
