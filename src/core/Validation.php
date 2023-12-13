<?php

namespace App\Core;

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
}
