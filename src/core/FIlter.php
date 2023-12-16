<?php


namespace App\Core;

use App\Core\Sanitization;
use App\Core\Validation;

class Filter
{
  // Metode filter menerima data, aturan sanitasi, aturan validasi, dan pesan kustom
  public function filter(array $data, array $fields, array $messages = []): array
  {
    // Inisialisasi array untuk sanitasi dan validasi
    $sanitization = [];
    $validation = [];

    // Iterasi melalui aturan-aturan untuk setiap field
    foreach ($fields as $field => $rules) {
      // Memeriksa apakah aturan sanitasi dan validasi dipisahkan oleh karakter '|'
      if (strpos($rules, '|')) {
        // Jika ya, pecah aturan menjadi sanitasi dan validasi
        [$sanitization[$field], $validation[$field]] = explode('|', $rules, 2);
      } else {
        // Jika tidak, aturan hanya untuk sanitasi
        $sanitization[$field] = $rules;
      }
    }

    // Membuat objek Sanitization
    $sanitize = new Sanitization();

    // Melakukan sanitasi terhadap data menggunakan aturan sanitasi
    $inputs = $sanitize->sanitize($data, $sanitization);

    // Membuat objek Validation
    $validate = new Validation();

    // Melakukan validasi terhadap data yang sudah disanitasi
    // Mengembalikan array hasil sanitasi dan array pesan kesalahan validasi
    $errors = $validate->validate($inputs, $validation, $messages);

    // Mengembalikan array hasil sanitasi dan array pesan kesalahan validasi
    return [$inputs, $errors];
  }
}