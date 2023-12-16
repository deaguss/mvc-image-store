<?php

namespace App\Core;

use App\Core\Filter;

// BaseController adalah kelas dasar yang nantinya akan diwarisi oleh controller-controller lainnya
class BaseController extends Filter
{
    // Fungsi untuk menampilkan view dengan mengirimkan data ke tampilan
    public function view($view, $data = [])
    {
        // Jika ada data yang dikirim, kita ekstrak agar variabel-variabelnya dapat digunakan langsung
        if (count($data)) {
            extract($data);
        }

        // Membutuhkan file view yang sesuai
        require_once '../src/views/' . $view . '.php';
    }

    // Fungsi untuk melakukan redirect ke URL tertentu
    public function redirect($url)
    {
        // Mengarahkan ke URL yang ditentukan dan keluar dari script
        header('Location: ' . BASE_URL . '/' . $url);
        exit;
    }

    // Fungsi untuk membuat instance dari model tertentu
    public function model($model)
    {
        // Mengembalikan instance baru dari model yang ditentukan
        return new $model();
    }
}