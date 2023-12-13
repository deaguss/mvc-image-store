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
}