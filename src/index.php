<?php

use App\Config\Config;  

if(!session_id()){
    session_start();
}

require_once '../vendor/autoload.php';

Config::load();