<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private $conn;
    private $tableName;
    private $column = [];

    public function __construct() {
        $this->conn = $this->setConnection();       
    }

    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    public function setColumn($column) {
        $this->column = $column;
    }
    
    protected function setConnection(){
        try {
            $host = getenv('DB_HOST');
            $user = getenv('DB_USER');
            $password = getenv('DB_PASSWORD');
            $db = getenv('DB_NAME');
            $port = getenv('DB_PORT');

            $conn = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $conn;       
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());  
        }
    }

    public function qry($query, $params = array()) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }

     // Metode untuk mendapatkan data dari tabel dengan parameter
  public function get($params = array())
  {
    // Membuat query SELECT berdasarkan kolom dan nama tabel
    $column = implode(",", $this->column);
    $query = "SELECT $column FROM {$this->tableName}";
    
    // Menyiapkan array untuk nilai parameter
    $paramValue = [];

    // Menambahkan kondisi WHERE berdasarkan parameter
    if (!empty($params)) {
      $query .= " WHERE 1=1 ";
      foreach ($params as $key => $value) {
        $query .= " AND {$key} = ? ";
        array_push($paramValue, $value);
      }
    }
    
    // Menjalankan query dengan parameter dan mengembalikan objek statement
    return $this->qry($query, $paramValue);
  }

  // Metode untuk menyisipkan data ke dalam tabel
  public function insertData($data = array())
  {
    // Menangani kasus di mana data kosong
    if (empty($data)) {
      return false;
    }

    // Menyiapkan array untuk nilai kolom, nama kolom, dan parameter
    $columnValue = [];
    $kolom = [];
    $param = [];

    // Mengambil data dan membentuk array untuk query INSERT
    foreach ($data as $key => $value) {
      array_push($kolom, $key);
      array_push($columnValue, $value);
      array_push($param, "?");
    }

    // Mengonversi array menjadi string untuk query
    $kolom = implode(", ", $kolom);
    $param = implode(", ", $param);

    // Membuat query INSERT dan menjalankannya
    $query = "INSERT INTO {$this->tableName} ($kolom) VALUES ($param)";
    return $this->qry($query, $columnValue);
  }

  // Metode untuk memperbarui data dalam tabel dengan parameter
  public function updateData($data = array(), $param = array())
  {
    // Menangani kasus di mana data kosong
    if (empty($data)) {
      return false;
    }

    // Menyiapkan array untuk nilai kolom, nama kolom, dan query UPDATE
    $columnValue = [];
    $kolom = [];
    $query = "UPDATE {$this->tableName} ";

    // Mengambil data dan membentuk array untuk query UPDATE
    foreach ($data as $key => $value) {
      array_push($kolom, $key . "= ? ");
      array_push($columnValue, $value);
    }

    // Mengonversi array menjadi string untuk query
    $kolom = implode(", ", $kolom);
    $query = $query . " SET $kolom WHERE 1=1 ";

    // Menyiapkan array untuk nilai parameter WHERE
    $whereColumn = [];

    // Menambahkan kondisi WHERE berdasarkan parameter
    foreach ($param as $key => $value) {
      array_push($whereColumn, "AND {$key} = ?");
      array_push($columnValue, $value);
    }

    // Mengonversi array menjadi string untuk query
    $whereColumn = implode(", ", $whereColumn);
    $query = $query . $whereColumn;

    // Menjalankan query UPDATE dengan parameter
    return $this->qry($query, $columnValue);
  }

  // Metode untuk menghapus data dari tabel dengan parameter
  public function deleteData($param = array())
  {
    // Menangani kasus di mana parameter kosong
    if (empty($param)) {
      return false;
    }

    // Menyiapkan query DELETE dan array untuk nilai parameter
    $query = "DELETE FROM {$this->tableName} WHERE 1=1 ";
    $whereColumn = [];
    $columnValue = [];

    // Menambahkan kondisi WHERE berdasarkan parameter
    foreach ($param as $key => $value) {
      array_push($whereColumn, "AND {$key} = ?");
      array_push($columnValue, $value);
    }

    // Mengonversi array menjadi string untuk query
    $whereColumn = implode(",", $whereColumn);
    $query = $query . $whereColumn;

    // Menjalankan query DELETE dengan parameter
    return $this->qry($query, $columnValue);
  }
}