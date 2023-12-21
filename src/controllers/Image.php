<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\AuthModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\AwsConfig;
use Aws\S3\Exception\S3Exception;

class Image extends BaseController {
    private $imageModel;
    
    
    public function __construct() {
        $this->imageModel = $this->model('App\Models\ImageModel');
    }

    private function getToken(){
        $headers = getallheaders();
        if(!isset($headers['Authorization']) || $headers['Authorization'] == ''){         
            $data = [
                'status' => '401',
                'error' => '401',
                'message' => 'Invalid authorization, you must be login first!',
                'data' => null
            ];

            $this -> view('header');
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode($data);
            exit();
        }

        list(, $token) = explode(' ', $headers['Authorization']);

        try {
            $decodedToken = JWT::decode($token, new Key(getenv('JWT_SECRET_KEY'), 'HS256'));

            $authModel = new AuthModel();
            $dataModel = $authModel->getByEmail($decodedToken->email);

            return $dataModel;
        } catch (\Exception $e) {
            $data = [
                'status' => '401',
                'error' => '401',
                'message' => 'You have an invalid token',
                'data' => null
            ];

            $this -> view('header');
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode($data);
            return null;    
        }
         
    }

    public function index($id = null) {
        if($this->getToken()){
           if($id == null){
                try {
                    $data = $this->imageModel->getAll();
                } catch (\Exception $e) {
                    $data = [
                        'status' => '500',
                        'error' => '500',
                        'message' => 'Something went wrong',
                        'data' => null,
                    ];
                    $this->view('header');
                    header('HTTP/1.0 500 Internal Server Error');
                    echo json_encode($data);
                    exit();
                }
           } else{
                try {
                    $data = $this->imageModel->getById($id);
                } catch (\Exception $e) {
                    $data = [
                        'status' => '500',
                        'error' => '500',
                        'message' => 'Something went wrong',
                        'data' => null,
                    ];
                    $this->view('header');
                    header('HTTP/1.0 500 Internal Server Error');
                    echo json_encode($data);
                    exit();
                }
           }

           if($data){
                $data = [
                    'status' => '200',
                    'error' => 'null',
                    'message' => 'Success',
                    'data' => $data
                ];
                $this->view('header');
                header('HTTP/1.0 200 OK');
                echo json_encode($data);
           } else {
            $data = [
                'status' => '404',
                'error' => '404',
                'message' => 'Not Found Data',
                'data' => null
              ];
              $this->view('header');
              header('HTTP/1.0 404 Not Found');
              echo json_encode($data);
           }
        }
    }

    // public function insert(){
    //     if($this->getToken()){
    //         $data = json_decode(file_get_contents('php://input'), true);

    //         $fields = [
    //             'image_url' => 'string',
    //             'caption'=> 'string | required',   
    //             'auth_id' => 'int',
    //             'status' => 'boolean',
    //             'image' => 'string | required'   
    //         ];

    //         $message = [
    //             'caption'=> [
    //                 'required' => 'caption is required' 
    //             ],
    //             'image' =>  [
    //                 'required' => 'image is required'
    //             ]
    //         ];
                
    //         [$inputs, $errors] = $this->filter($data, $fields, $message);

    //         if($inputs["auth_id"] == "") {
    //             $inputs["auth_id"] = $this->getToken()["id"];
    //         }

    //         if($inputs["status"] == "") {
    //             $inputs["status"] = true;
    //         }

    //         if($errors){
    //             $data = [
    //                 'status' => '400',
    //                 'error' => '400',
    //                 'message' => $errors,
    //                 'data' => $inputs
    //             ];

    //             $this->view('header');
    //             header('HTTP/1.0 400 Bad Request');
    //             echo json_encode($data);
    //             exit();     
    //         } else{
    //             if(isset($_POST["caption"]) && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK){

    //                 $file = $_FILES['image'];
    //                 $caption = $_POST["caption"];

    //                 $allowedFormats = ['image/jpg', 'image/jpeg', 'image/png'];
    //                 $maxSize = 5 * 1024 * 1024;

    //                 $fileInfo = pathinfo($file['name']);
    //                 $ext = strtolower($fileInfo['extension']);

    //                 if(in_array($file['type'], $allowedFormats) && $file['size'] <= $maxSize){
                    
    //                     $encryptedFileName = md5(uniqid()) . '.' . $ext;

    //                     try {
    //                         $s3 = AwsConfig::get();

    //                         $result = $s3->putObject([
    //                             'Bucket' => getenv('AWS_BUCKET_NAME'),
    //                             'Key' => $encryptedFileName,
    //                             'SourceFile' => $file['tmp_name'],
    //                             'ACL' => 'public-read',
    //                         ]);

    //                         $imageUrl = $result['ObjectURL'];
    //                         $inputs["image_url"] = $imageUrl;
    //                         $inputs["image"] = $encryptedFileName;

    //                         $proc = $this->imageModel->insert($inputs);

    //                         if($proc->rowCount() > 0){
    //                             $data = [
    //                                 'status' => '201',
    //                                 'error' => null,
    //                                 'message' => 'Success insert data' . $proc->rowCount() . ' rows',
    //                                 'data' => $inputs
    //                             ];
    //                             $this->view('header');
    //                             header('HTTP/1.0 201 OK');
    //                             echo json_encode($data);
    //                         }else {
    //                             $data = [
    //                                 'status' => '400',
    //                                 'error' => '400',
    //                                 'message' => 'invalid Upload image',
    //                                 'data' => null
    //                                 ];
    //                                 $this->view('header');
    //                                 header('HTTP/1.0 400 Bad Request');
    //                                 echo json_encode($data);
    //                         }
    //                     } catch (S3MultipartUploadException $e) {
    //                         $data = [
    //                             'status' => '400',
    //                             'error' => '400',
    //                             'message' => $e->getMessage(),
    //                             'data' => null
    //                             ];
    //                             $this->view('header');
    //                             header('HTTP/1.0 400 Bad Request');
    //                             echo json_encode($data);
    //                     }
    //                 } else {
    //                     $data = [
    //                         'status' => '400',
    //                         'error' => '400',
    //                         'message' => 'invalid Format image or image size than 5MB',
    //                         'data' => null
    //                         ];
    //                         $this->view('header');
    //                         header('HTTP/1.0 400 Bad Request');
    //                         echo json_encode($data);
    //                 }
    //             }

    //         }
    //     }
    // }

    public function insert()
{
    if ($this->getToken()) {
        if (isset($_POST["caption"]) && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // Get the form fields
            $caption = $_POST["caption"];
            $file = $_FILES['image'];

            $allowedFormats = ['image/jpg', 'image/jpeg', 'image/png'];
            $maxSize = 5 * 1024 * 1024;

            $fileInfo = pathinfo($file['name']);
            $ext = strtolower($fileInfo['extension']);

            if (in_array($file['type'], $allowedFormats) && $file['size'] <= $maxSize) {

                $encryptedFileName = md5(uniqid()) . '.' . $ext;

                try {
                    $s3 = AwsConfig::get();
                    $result = $s3->putObject([
                        'Bucket' => getenv('AWS_BUCKET_NAME'),
                        'Key' => $encryptedFileName,
                        'SourceFile' => $file['tmp_name'],
                        'ACL' => 'public-read',
                    ]);

                    $imageUrl = $result['ObjectURL'];

                    $inputs = [
                        'image_url' => $imageUrl,
                        'caption' => $caption,
                        'auth_id' => $this->getToken()["id"],
                        'status' => true,
                        'image' => $encryptedFileName,
                    ];

                    $proc = $this->imageModel->insert($inputs);

                    if ($proc->rowCount() > 0) {
                        $data = [
                            'status' => '201',
                            'error' => null,
                            'message' => 'Success insert data ' . $proc->rowCount() . ' rows',
                            'data' => $inputs,
                        ];
                        $this->view('header');
                        header('HTTP/1.0 201 OK');
                        echo json_encode($data);
                    } else {
                        $data = [
                            'status' => '400',
                            'error' => '400',
                            'message' => 'Invalid upload image',
                            'data' => null,
                        ];
                        $this->view('header');
                        header('HTTP/1.0 400 Bad Request');
                        echo json_encode($data);
                    }
                } catch (S3Exception $e) {
                    $data = [
                        'status' => '400',
                        'error' => '400',
                        'message' => $e->getMessage(),
                        'data' => null,
                    ];
                    $this->view('header');
                    header('HTTP/1.0 400 Bad Request');
                    echo json_encode($data);
                }
            } else {
                $data = [
                    'status' => '400',
                    'error' => '400',
                    'message' => 'Invalid format or image size greater than 5MB',
                    'data' => null,
                ];
                $this->view('header');
                header('HTTP/1.0 400 Bad Request');
                echo json_encode($data);
            }
        } else {
            $data = [
                'status' => '400',
                'error' => '400',
                'message' => 'Missing form fields',
                'data' => null,
            ];
            $this->view('header');
            header('HTTP/1.0 400 Bad Request');
            echo json_encode($data);
        }
    }
}

public function delete($id = null){
    if ($this->getToken()) {
        $inputs = [
            'status' => '0',
            'id' => $id
        ];
        if($id == null){
            $data = [
                'status' => '404',
                'error' => '404',
                'message' => 'Not Found',
                'data' => null,
            ];
            $this->view('header');
            header('HTTP/1.0 404 Not Found');
            echo json_encode($data);
            exit();
        }else{
            $proc = $this->imageModel->delete($inputs);
            if($proc -> rowCount() > 0){
                $data = [
                    'status' => '201',
                    'error' => null,
                    'message' => 'Success delete data ' . $proc->rowCount() . ' rows',
                    'data' => null,
                ];
                $this->view('header');
                header('HTTP/1.0 201 OK');
                echo json_encode($data);
                exit();
            }else {
                $data = [
                    'status' => '404',
                    'error' => '404',
                    'message' => 'Not Found buddy',
                    'data' => null,
                ];
                $this->view('header');
                header('HTTP/1.0 404 Not Found');
                echo json_encode($data);
                exit();
            }
        }
    }
}

}