<?php

namespace Api\Models;

use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Robots extends Controller
{
    public function add()
    {
        $insert = $this->request->getPost();
        $data = $this->mongo->rest_api->products->insertOne([
            'name' => $insert['name'],
            'price' => $insert['price'],
            'category' => $insert['category'],
            'quantity' => $insert['quantity'],
        ]);
        echo "<pre>";
        print_r($data);
    }
    public function search($name = "")
    {
        $name = urldecode($name);
        $value = explode(" ", $name);
        $key = "Admin_Key";
        // $bearer = $this->request->getHeaders();
        $bearer = $this->request->get('Token');
        $jwt = JWT::decode($bearer, new Key($key, 'HS256'));
        if ($jwt->role == 'admin') {
            foreach ($value as $key => $val) {

                $data = $this->mongo->rest_api->products->findOne(['name' => $val]);
                foreach ($data as $k => $v) {
                    $getdata = json_encode($v);
                    echo $getdata;
                }
            }
        } else {
            echo "Access denied";
        }
    }
    public function gettoken()
    {
        $key = "example_key";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "exp" => time()*24+3600,
            "role" => 'admin',
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        echo $jwt;
    }
    public function getpage($per_page, $page)
    {
        $per_page = (int)$per_page;
        $page = (int)$page;
        $results = ($page - 1) * $per_page;
        $search = $this->mongo->rest_api->products->find([], ['limit' => $per_page, 'skip' => $results])->toArray();

        echo "<pre>";
        foreach ($search as $value) {
            print_r(json_encode($value)."<br>");
        }
        die;
    }
}
