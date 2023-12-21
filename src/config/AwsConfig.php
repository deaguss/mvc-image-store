<?php

namespace App\Config;
use Aws\S3\S3Client;

class AwsConfig {
    public static function get(){
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => getenv('AWS_REGION'),
            'credentials' => [
                'key' => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_KEY'),   
            ]          
        ]);

        return $s3;
    }
}