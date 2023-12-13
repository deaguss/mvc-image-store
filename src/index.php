<?php

use App\Config\Config;  

if(!session_id()){
    session_start();
}

Config::load();