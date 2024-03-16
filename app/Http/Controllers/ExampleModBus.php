<?php

namespace App\Http\Controllers;

use App\Business\ModBuss as BusinessExampleModBus;
use Illuminate\Http\Request;


class ExampleModBus extends Controller
{
    public function __construct()
    {
        // BusinessExampleModBus::connect();
    }

    public function index(){
        BusinessExampleModBus::ReadInputRegisters();
    }
}
