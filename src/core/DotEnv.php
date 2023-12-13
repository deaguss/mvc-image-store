<?php

namespace App\Core;

class DotEnv {
    protected $path;

    public function __construct(string $path){
        if(!file_exists($path)){
            throw new \InvalidArgumentException(sprintf("File '%s' not found", $path));
        }
        $this->path = $path;    
    }

    public function load(): void{
        if(!is_readable($this -> path)){
            throw new \InvalidArgumentException(sprintf("File '%s' not readable", $this -> path));
        }

        // Membaca file baris per baris
        $lines = file($this -> path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach($lines as $line){
            // Melewati baris yang dimulai dengan karakter '#'
            if(strpos(trim($line), '#') === 0){
                continue;
            }

            // Memisahkan nama variabel dan nilainya
            list($name , $value) = explode('=', $line,2);
            $name = trim($name);
            $value = trim($value);

            // Mengatur variabel lingkungan dan variabel server jika belum ada
            if(!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)){
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        } 
    }
}

?>
