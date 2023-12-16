<?php

namespace App\Core;

class App {
    // Properti default untuk controller, method, namespace, dan parameter
  private $controllerFile = 'DefaultApp';
  private $contollerMethod = 'index';
  private $namespace = 'App\Controllers';
  private $parametr = [];

  // Konstanta untuk metode HTTP yang umum
  private const DEFULT_GET = 'GET';
  private const DEFULT_POST = 'POST';
  private const DEFULT_PUT = 'PUT';
  private const DEFULT_DELETE = 'DELETE';
  private const DEFULT_PATCH = 'PATCH';

  // Array untuk menyimpan handler rute
  private $handlers = [];

  // Set default controller
  public function setDefaultController($controller)
  {
    $this->controllerFile = $controller;
  }

  // Set default method
  public function setDefaultMethod($method)
  {
    $this->contollerMethod = $method;
  }

  // Set namespace
  public function setNamespace($namespace)
  {
    $this->namespace = $namespace;
  }

  // Menambahkan handler untuk metode HTTP GET
  public function get($uri, $callback)
  {
    $this->setHandler(self::DEFULT_GET, $uri, $callback);
  }

  // Menambahkan handler untuk metode HTTP POST
  public function post($uri, $callback)
  {
    $this->setHandler(self::DEFULT_POST, $uri, $callback);
  }

  // Menambahkan handler untuk metode HTTP PUT
  public function put($uri, $callback)
  {
    $this->setHandler(self::DEFULT_PUT, $uri, $callback);
  }

  // Menambahkan handler untuk metode HTTP DELETE
  public function delete($uri, $callback)
  {
    $this->setHandler(self::DEFULT_DELETE, $uri, $callback);
  }

  // Menambahkan handler untuk metode HTTP PATCH
  public function patch($uri, $callback)
  {
    $this->setHandler(self::DEFULT_PATCH, $uri, $callback);
  }

  // Menyimpan handler rute dalam array
  private function setHandler(string $method, string $path, $handler)
  {
    $this->handlers[$method . $path] = [
      'path' => $path,
      'method' => $method,
      'handler' => $handler,
    ];
  }

  // Menjalankan aplikasi
  public function run()
  {
    // Inisialisasi variabel
    $execute = 0;
    $url = $this->getUrl();
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Iterasi melalui handler rute
    foreach ($this->handlers as $handler) {
      // Pisahkan path dan url menjadi array
      $path = explode('/', rtrim(ltrim($handler['path'], '/'), '/'));
      $new_path = [];
      $new_url = [];
      $param = [];
      $paramURL = [];
      $objVariable = [];

      // Cek apakah jumlah segmen path sama dengan jumlah segmen url
      if (count($path) == count($url)) {
        // Iterasi melalui segmen path
        foreach ($path as $value) {
          if (!str_contains($value, ':')) {
            array_push($new_path, $value);
          } else {
            array_push($param, str_replace(')', '', str_replace('(', '', str_replace(':', '', $value))));
          }
        }

        // Perbandingan path dan url
        if (str_contains(implode("/", $url), implode("/", $new_path))) {
          // Pisahkan url menjadi segmen path dan parameter
          for ($i = 0; $i < count($url); $i++) {
            if ($i < count($new_path)) {
              array_push($new_url, $url[$i]);
            } else {
              array_push($paramURL, $url[$i]);
            }
          }

          // Cek kesamaan path dan url, serta metode HTTP
          if (
            implode('/', $new_path) == implode('/', $new_url) &&
            count($param) == count($paramURL) &&
            $requestMethod == $handler['method']
          ) {
            // Membuat array asosiatif variabel objek
            for ($i = 0; $i < count($param); $i++) {
              if (str_contains(implode('/', $param), 'segment')) {
                $objVariable[] = $paramURL[$i];
              } else {
                $objVariable[$param[$i]] = $paramURL[$i];
              }
            }

            // Mengganti controller jika diberikan dalam handler
            if (isset($handler['handler'][0]) && class_exists($this->namespace . '\\' . $handler['handler'][0])) {
              $this->controllerFile = $handler['handler'][0];
            }

            // Membuat objek controller
            $fn = $this->namespace . '\\' . $this->controllerFile;
            $this->controllerFile = new $fn();
            $execute = 1;

            // Mengganti method jika diberikan dalam handler
            if (isset($handler['handler'][1]) && method_exists($this->controllerFile, $handler['handler'][1])) {
              $this->contollerMethod = $handler['handler'][1];
            }
            $url = $objVariable;
          }
        }
      }
    }

    // Membuat objek controller jika belum dibuat sebelumnya
    if ($execute == 0) {
      $fn = $this->namespace . '\\' . $this->controllerFile;
      $this->controllerFile = new $fn();
    }

    // Menyimpan parameter sisanya
    if (!empty($url)) {
      $this->parametr = $url;
    }

    // Menjalankan controller dan method dengan parameter
    call_user_func_array([$this->controllerFile, $this->contollerMethod], $this->parametr);
  }

  // Mendapatkan URL dan memformatnya
  private function getUrl()
  {
    $url = rtrim($_SERVER['QUERY_STRING'], "/");
    $url = filter_var($url, FILTER_SANITIZE_URL);
    $url = explode('/', $url);
    return $url;
  }
}