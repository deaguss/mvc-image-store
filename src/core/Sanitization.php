<?php

namespace App\Core;

class Sanitization {
    const FIlTER = [
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

        private function array_trim(array $items): array {
            return array_map(function ($item) {
                if(is_string($item)){
                    return trim($item);
                } elseif(is_array($item)){
                    return $this->array_trim($item);
                } else {
                    return $item;
                }
            }, $items); 
        }
}