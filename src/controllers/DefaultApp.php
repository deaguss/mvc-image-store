<?php

namespace App\Controllers;

use App\Core\BaseController;

class DefaultApp extends BaseController {
    public function index() {
        $data = [
            'status' => '404',
            'error' => '404',
            'message' => 'Pages 404 Not Found',
            'data' => null
        ];

        $this -> view('header');
        echo json_encode($data);
        exit();
    }
}