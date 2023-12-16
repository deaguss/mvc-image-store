<?php
namespace App\Controllers;

use App\Models\AuthModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Core\BaseController;

class Auth extends BaseController
{
  // Mendefinisikan model autentikasi
  private $authModel;

  // Pesan kesalahan yang mungkin muncul
  private const MESSAGE = [
    'username' => [
        'required' => 'Nama harus diisi',
    ],
    'email' => [
      'required' => 'Email harus diisi',
      'email' => 'Email tidak valid'
    ],
    'password' => [
      'required' => 'Password harus diisi',
      'secure' => 'Password minimal 8 karakter, kombinasi huruf besar, huruf kecil dan karakter khusus!'
    ]
  ];

  // Konstruktor untuk inisialisasi model
  public function __construct()
  {
    $this->authModel = $this->model('App\Models\AuthModel');
  }

  // Fungsi untuk mendaftarkan pengguna baru
  public function register()
  {
    // Mendapatkan data pengguna dari input JSON
    $dataUser = json_decode(file_get_contents("php://input"), true);

    // Memeriksa keberhasilan pengambilan data
    if (!$dataUser) {
      $data = [
        'status' => '400',
        'error' => '400',
        'message' => 'Bad Request',
        'data' => null
      ];
      $this->view('header');
      header('HTTP/1.1 400 Bad Request');
      echo json_encode($data);
      exit();
    }

    // Mendefinisikan aturan validasi untuk input
    $field = [
      'username' => 'string | required',
      'email' => 'string | required | email | unique: auth, email',
      'password' => 'string | required | secure',
    ];

    // Melakukan validasi input
    [$inputs, $errors] = $this->filter($dataUser, $field, self::MESSAGE);

    // Memeriksa apakah ada kesalahan validasi
    if ($errors) {
      $data = [
        'status' => '400',
        'error' => '400',
        'message' => $errors,
        'data' => $inputs
      ];
      $this->view('header');
      header('HTTP/1.1 400 Bad Request');
      echo json_encode($data);
      exit();
    } else {
      // Menyimpan data pengguna baru ke dalam database
      $proc = $this->authModel->insert($inputs);

      // Memeriksa keberhasilan penyimpanan
      if ($proc->rowCount() > 0) {
        $data = [
          'status' => '201',
          'error' => '201',
          'message' => "Data ditambahkan " . $proc->rowCount() . " baris",
          'data' => $inputs
        ];
        $this->view('header');
        header('HTTP/1.1 200 OK');
        echo json_encode($data);
        exit();
      } else {
        $data = [
          'status' => '400',
          'error' => '400',
          'message' => "Data gagal ditambahkan",
          'data' => null
        ];
        $this->view('header');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode($data);
        exit();
      }
    }
  }

  // Fungsi untuk proses login
  public function login()
  {
    // Mendapatkan data login dari input JSON
    $data = json_decode(file_get_contents("php://input"), true);

    // Memeriksa keberhasilan pengambilan data
    if (!$data) {
      $data = [
        'status' => '400',
        'error' => '400',
        'message' => 'Bad Request',
        'data' => null
      ];
      $this->view('header');
      header('HTTP/1.1 400 Bad Request');
      echo json_encode($data);
      exit();
    }

    // Mendefinisikan aturan validasi untuk input login
    $field = [
      'email' => 'string | required | email',
      'password' => 'string | required'
    ];

    // Melakukan validasi input login
    [$inputs, $errors] = $this->filter($data, $field, self::MESSAGE);

    // Memeriksa apakah ada kesalahan validasi
    if ($errors) {
      $data = [
        'status' => '400',
        'error' => '400',
        'message' => $errors,
        'data' => $inputs
      ];
      $this->view('header');
      header('HTTP/1.1 400 Bad Request');
      echo json_encode($data);
      exit();
    } else {
      // Mengambil data pengguna berdasarkan email
      $proc = $this->authModel->getByEmail($inputs['email']);

      // Memeriksa apakah pengguna ditemukan
      if ($proc) {
        // Memeriksa kecocokan password
        if (password_verify($inputs['password'], $proc['password'])) {
          // Membuat token akses
          $iat = time();
          $expire_time = $iat + getenv('JWT_TIME_TO_LIVE');
          $expire_time_refresh = $iat + getenv('JWT_TIME_TO_REFRESH');
          $payload = [
            'email' => $inputs['email'],
            'iat' => $iat,
            'exp' => $expire_time
          ];
          $payload_refresh = [
            'email' => $inputs['email'],
            'iat' => $iat,
            'exp' => $expire_time_refresh
          ];
          $acess_token = JWT::encode($payload, getenv('JWT_SECRET_KEY'), 'HS256');
          $acess_refresh = JWT::encode($payload_refresh, getenv('JWT_REFRESH_KEY'), 'HS256');

          // Menyusun respons berhasil
          $data = [
            'status' => '200',
            'error' => '200',
            'message' => "Login berhasil",
            'data' => [
              'accessToken' => $acess_token,
              'refreshToken' => $acess_refresh,
              'expiry' => date(DATE_ISO8601, $expire_time)
            ]
          ];
          $this->view('header');
          header('HTTP/1.1 200 OK');
          echo json_encode($data);
          exit();
        } else {
          // Respons jika password tidak cocok
          $data = [
            'status' => '400',
            'error' => '400',
            'message' => "Password anda salah !",
            'data' => $inputs
          ];
          $this->view('header');
          header('HTTP/1.1 400 Bad Request');
          echo json_encode($data);
          exit();
        }
      } else {
        // Respons jika login gagal
        $data = [
          'status' => '400',
          'error' => '400',
          'message' => "Login gagal",
          'data' => null
        ];
        $this->view('header');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode($data);
        exit();
      }
    }
  }

  // Fungsi untuk mereset token akses
  public function refreshToken()
  {
    // Mendapatkan header dari HTTP request
    $headers = getallheaders();

    // Memeriksa apakah token akses disertakan dalam header
    if (!isset($headers['Authorization']) || $headers['Authorization'] == "") {
      $data = [
        'status' => '401',
        'error' => '401',
        'message' => 'Acess tidak diberikan',
        'data' => null
      ];
      $this->view('header');
      header('HTTP/1.0 401 Unauthorized');
      echo json_encode($data);
      exit();
    }

    // Mendapatkan token akses dari header
    list(, $token) = explode(' ', $headers['Authorization']);

    // Memeriksa validitas token akses
    try {
      $decodedToken = JWT::decode($token, new Key(getenv('JWT_REFRESH_KEY'), 'HS256'));
      $authModel = new AuthModel();
      $dataModel = $authModel->getByEmail($decodedToken->email);

      // Memeriksa apakah pengguna ditemukan
      if ($dataModel) {
        // Membuat token akses baru
        $iat = time();
        $expire_time = $iat + getenv('JWT_TIME_TO_LIVE');
        $expire_time_refresh = $iat + getenv('JWT_TIME_TO_REFRESH');
        $payload = [
          'email' => $decodedToken->email,
          'iat' => $iat,
          'exp' => $expire_time
        ];
        $payload_refresh = [
          'email' => $decodedToken->email,
          'iat' => $iat,
          'exp' => $expire_time_refresh
        ];
        $acess_token = JWT::encode($payload, getenv('JWT_SECRET_KEY'), 'HS256');
        $acess_refresh = JWT::encode($payload_refresh, getenv('JWT_REFRESH_KEY'), 'HS256');

        // Menyusun respons berhasil
        $data = [
          'status' => '200',
          'error' => '200',
          'message' => "Login berhasil",
          'data' => [
            'accessToken' => $acess_token,
            'refreshToken' => $acess_refresh,
            'expiry' => date(DATE_ISO8601, $expire_time)
          ]
        ];
        $this->view('header');
        header('HTTP/1.1 200 OK');
        echo json_encode($data);
        exit();
      }
    } catch (\Exception $e) {
      // Respons jika token tidak valid
      $data = [
        'status' => '401',
        'error' => '401',
        'message' => 'Token tidak valid',
        'data' => null
      ];
      $this->view('header');
      header('HTTP/1.0 401 Unauthorized');
      echo json_encode($data);
      return null;
    }
  }
}