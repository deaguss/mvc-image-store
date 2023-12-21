<?php

namespace App\Models;
use App\Core\Database;
use PDO;

class ImageModel extends Database {

    public function __construct()
    {
        parent::__construct();
        $this->setTableName('image');
        $this->setColumn([
            'image_id',
            'image_url',
            'caption',
            'auth_id',
            'status',
            'image'
        ]);
    }

    public function getAll(){
        $params = array(
            'status' => '1'
        );
        return $this->get($params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
      return $this->get(['image_id' => $id])->fetch(PDO::FETCH_ASSOC);
    }
    
    public function insert($data){
        $table = [
            'image_url' => $data['image_url'],
            'caption'=> $data['caption'],   
            'auth_id' => $data['auth_id'],
            'status' => $data['status'],
            'image' => $data['image']
        ];
        return $this->insertData($table);   
    }

    public function delete($data){
        $table = [
            'status' => $data["status"]
        ];
        $key = [
            'image_id' => $data["id"]
        ];
        return $this->updateData($table, $key);
    }
}