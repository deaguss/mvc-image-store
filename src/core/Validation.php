<?php

namespace App\Core;

use App\Models\UniqueModel;
class Validation {

  // Mendefinisikan pesan kesalahan validasi default
  const DEFAULT_VALIDATION_ERRORS = [
    'required' => 'Data %s harus diisi',
    'email' => ' %s email tidak valid',
    'min' => '%s harus lebih dari %d karakter',
    'max' => '%s harus kurang dari %d karakter',
    'between' => '%s harus diantara %d dan %d karakter',
    'same' => '%s dan %s tidak sama',
    'alphanumeric' => '%s harus diisi huruf dan angka',
    'secure' => '%s jumalah diantara 8 dan 64 karakter dan ada angka, huruf besar, huruf kecil dan karakter spesial',
    'unique' => '%s sudah ada',
  ];

  // Fungsi utama untuk melakukan validasi
  public function validate(
    array $data,
    array $fields,
    array $messages = []
  ): array {
      // Fungsi anonim untuk memecah string berdasarkan pemisah
      $split = function($str, $separator) {
          return array_map('trim', explode($separator, $str));
      };

      // Memfilter pesan aturan validasi yang valid
      $rule_messages = array_filter($messages, function($message) {
          return is_string($message);
      });

      // Menggabungkan pesan kesalahan validasi default dengan pesan yang disediakan pengguna
      $validation_errors = array_merge(self::DEFAULT_VALIDATION_ERRORS, $rule_messages);

      // Array untuk menyimpan kesalahan validasi
      $errors = [];

      // Iterasi melalui setiap bidang dan aturan validasi yang diberikan
      foreach($fields as $field => $option){
          // Memecah aturan validasi untuk bidang tertentu
          $rules = $split($option, '|');

          // Iterasi melalui setiap aturan validasi
          foreach($rules as $rule){
             $params = [];

             // Memeriksa apakah aturan memiliki parameter
             if(strpos($rule, ':')){
              // Jika ada, memecah nama aturan dan parameter
              list($rule_name, $param_str) = $split($rule, ':');
              $params = $split($param_str, ',');
             } else {
              // Jika tidak ada, mengambil nama aturan saja
              $rule_name = trim($rule);   
             }

             // Membuat nama fungsi validasi
             $fn = 'is_'. $rule_name;

             // Memeriksa apakah fungsi validasi tersedia
             if(method_exists(new Validation(), $fn)){
                // Memanggil fungsi validasi dan menyimpan hasilnya
                $pass = $this->$fn($data, $field, ...$params);

                // Jika validasi tidak berhasil, menambahkan pesan kesalahan ke array
                if(!$pass){
                    array_push(
                        $errors,
                        sprintf(
                            $messages[$field][$rule_name] ?? $validation_errors[$rule_name],
                            str_replace("_", " ", $field),
                            ...$params
                          )
                    );
                }
             }
          }
      }

      // Mengembalikan array kesalahan validasi
      return $errors;
  }

  // Fungsi validasi: Bidang wajib diisi
  public function is_required(array $data, string $field): bool
  {
    return isset($data[$field]) && $data[$field] !== '';
  }

  // Fungsi validasi: Format email
  public function is_email(array $data, string $field): bool
  {
    if (empty($data[$field])) {
      return true;
    }

    return filter_var($data[$field], FILTER_VALIDATE_EMAIL);
  }

  // Fungsi validasi: Panjang minimal
  public function is_min(array $data, string $field, int $min): bool
  {
    if (!isset($data[$field])) {
      return true;
    }

    return mb_strlen($data[$field]) >= $min;
  }

  // Fungsi validasi: Panjang maksimal
  public function is_max(array $data, string $field, int $max): bool
  {
    if (!isset($data[$field])) {
      return true;
    }

    return mb_strlen($data[$field]) <= $max;
  }

  // Fungsi validasi: Panjang di antara
  public function is_between(array $data, string $field, int $min, int $max): bool
  {
    if (!isset($data[$field])) {
      return true;
    }

    $len = mb_strlen($data[$field]);
    return $len >= $min && $len <= $max;
  }

  // Fungsi validasi: Sama dengan bidang lain
  public function is_same(array $data, string $field, string $other): bool
  {
    if (isset($data[$field], $data[$other])) {
      return $data[$field] === $data[$other];
    }

    if (!isset($data[$field]) && !isset($data[$other])) {
      return true;
    }

    return false;
  }

  // Fungsi validasi: Alfanumerik (huruf dan angka)
  public function is_alphanumeric(array $data, string $field): bool
  {
    if (!isset($data[$field])) {
      return true;
    }

    return ctype_alnum($data[$field]);
  }

  // Fungsi validasi: Keamanan (karakter tertentu)
  public function is_secure(array $data, string $field): bool
  {
    if (!isset($data[$field])) {
      return true;
    }

    // Pola keamanan untuk memeriksa kombinasi karakter tertentu
    $pattern = "#.*^(?=.{8,64})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#";

    return preg_match($pattern, $data[$field]);
  }

  // Fungsi validasi: Unik dalam database
  public function is_unique(array $data, string $field, string $table, string $column): bool
  {
    if (!isset($data[$field])) {
      return true;
    }

    // Disini cek ke database menggunakan model UniqueModel
    $uniqueModel = new UniqueModel();
    $stmt = $uniqueModel->check($table, $column, $data[$field]);

    // Kembalikan true jika tidak ada hasil yang cocok di database
    return $stmt->fetchColumn() === false;
  }
}
