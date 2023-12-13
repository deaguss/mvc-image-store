<?php

namespace App\Core;

class Sanitization {
    // Daftar filter yang dapat digunakan
    const FILTER = [
        'string' => FILTER_SANITIZE_SPECIAL_CHARS,
        'string[]' => [
            'filter' => FILTER_SANITIZE_SPECIAL_CHARS,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'email' => FILTER_SANITIZE_EMAIL,
        'int' => [
            'filter' => FILTER_SANITIZE_NUMBER_INT,
            'flags' => FILTER_REQUIRE_SCALAR
        ],
        'int[]' => [
            'filter' => FILTER_SANITIZE_NUMBER_INT,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'float' => [
            'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
            'flags' => FILTER_FLAG_ALLOW_FRACTION
        ],
        'float[]' => [
            'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'url' => FILTER_SANITIZE_URL
    ];

    // Fungsi untuk menghilangkan spasi pada setiap elemen array
    private function array_trim(array $items): array {
        return array_map(function ($item) {
            if (is_string($item)) {
                return trim($item);
            } elseif (is_array($item)) {
                return $this->array_trim($item);
            } else {
                return $item;
            }
        }, $items);
    }

    // Fungsi utama untuk membersihkan dan memvalidasi input
    public function sanitize(
        array $inputs,
        array $fields = [],
        int $default_filter = FILTER_SANITIZE_SPECIAL_CHARS,
        array $filters = self::FILTER,
        bool $trim = true
    ) {
        // Jika ada field yang harus dihandle secara khusus
        if ($fields) {
            foreach ($fields as $key => $field) {
                // Jika field adalah string, hapus tag HTML
                if ($field == 'string' && isset($inputs[$key])) {
                    $tempvar = strip_tags($inputs[$key]);
                    $inputs[$key] = $tempvar;
                }
            }

            // Membuat daftar filter sesuai dengan fields
            $options = array_map(fn ($field) => $filters[trim($field)], $fields);
            // Membersihkan dan memvalidasi input menggunakan filter yang telah ditentukan
            $data = filter_var_array($inputs, $options);
        } else {
            // Jika tidak ada field yang harus dihandle secara khusus, gunakan filter default
            $data = filter_var_array($inputs, $default_filter);
        }

        // Menghilangkan spasi pada setiap elemen array jika trim diaktifkan
        return $trim ? $this->array_trim($data) : $data;
    }
}
