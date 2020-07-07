<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;

return function (App $app) {
    $container = $app->getContainer();

    // การสร้าง Rounting
    $app->get('/', function(Request $request, Response $response, array $args) use ($container){
        echo 'Hello First Page...';
    });

    // การเซต Routing Group 
    $app->group('/apitest', function() use ($app){

        $container = $app->getContainer();

        // Method Get
        $app->get('/data', function(Request $request, Response $response, array $args) use ($container){
            echo 'This is Get data route';
        });
        
        // Method Post
        $app->post('/data', function(Request $request, Response $response, array $args) use ($container){
            echo 'This is Post data route';
        });
        
        // Method Put
        $app->put('/data', function(Request $request, Response $response, array $args) use ($container){
            echo 'This is Put data route';
        });
    
        // Method Delete
        $app->delete('/data', function(Request $request, Response $response, array $args) use ($container){
            echo 'This is Delete data route';
        });

    });

    // Login and Get Token
    $app->post('/login', function (Request $request, Response $response, array $args) {

        $input = $request->getParsedBody();
        $password = sha1($input['password']);

        $sql = "SELECT * FROM users WHERE username= :username";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("username", $input['username']);
        $sth->execute();
        $user = $sth->fetchObject();
     
        // verify username address.
        if(!$user) {
            return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match our records.']);  
        }
     
        // verify password.
        if ($password  != $user->password) {
            return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match our records.']);  
        }
     
        $settings = $this->get('settings'); // get settings array.
        
        $token = JWT::encode(['id' => $user->id, 'username' => $user->username], $settings['jwt']['secret'], "HS256");
     
        return $this->response->withJson(['token' => $token]);
     
    });

    // Api Routing Group 
    $app->group('/api', function() use ($app){

        $container = $app->getContainer();

        // Get All Product (Method [Get])
        $app->get('/products', function(Request $request, Response $response, array $args) use ($container){
            // Read product
            $strSQL = 'SELECT * FROM products';
            $stmt = $this->db->prepare($strSQL);
            $stmt->execute();
            $product = $stmt->fetchAll();

            if (count($product)) {
                $input = [
                    'status' => 'success',
                    'message' => 'Read Product Success',
                    'data' => $product,
                ];
            } else {
                $input = [
                    'status' => 'fail',
                    'message' => 'Empty Product Data',
                    'data' => $product,
                ];
            }

            return $this->response->withJson($input);

        });

        // Get By Id Product (Method [Get])
        $app->get('/products/{id}', function(Request $request, Response $response, array $args) use ($container){
            
            $strSQL = 'SELECT * FROM products WHERE id='.$args['id'];
            $stmt = $this->db->prepare($strSQL);
            $stmt->execute();
            $product = $stmt->fetchAll(); 

            if (count($product)) {
                $input = [
                    'status' => 'success',
                    'message' => 'Read Product Success',
                    'data' => $product,
                ];
            } else {
                $input = [
                    'status' => 'fail',
                    'message' => 'Empty Product Data',
                    'data' => $product,
                ];
            }
            return $this->response->withJson($input);


        });
        
        // Method Post
        $app->post('/products', function(Request $request, Response $response, array $args) use ($container){
            
            // รับค่าจาก Client
            $body = $this->request->getParsedBody();
            
            $img = 'noimg.png';
            // INSERT Data To products
            $strSQL = ' INSERT INTO products (product_name,product_detail,product_barcode,product_qty,product_price,product_image) 
                        VALUES (:product_name,:product_detail,:product_barcode,:product_qty,:product_price,:product_image)';

            $sth = $this->db->prepare($strSQL);
            $sth->bindParam(':product_name', $body['product_name']);
            $sth->bindParam(':product_detail', $body['product_detail']);
            $sth->bindParam(':product_barcode', $body['product_barcode']);
            $sth->bindParam(':product_qty', $body['product_qty']);
            $sth->bindParam(':product_price', $body['product_price']);
            $sth->bindParam(':product_image', $img);

            if ($sth->execute()) {
                $data = $this->db->lastInsertId();
                $input = [
                    'id' => $data,
                    'message' => 'Success',
                ];
            } else {
                $input = [
                    'id' => '',
                    'message' => 'fail',
                ];
            }
            return $this->response->withJson($input);
            
        });
        
        // Method Put
        $app->put('/products/{id}', function(Request $request, Response $response, array $args) use ($container){

            // รับค่าจาก Client
            $body = $this->request->getParsedBody();

            $strSQL = ' UPDATE products SET 
                            product_name=:product_name,
                            product_detail=:product_detail,
                            product_barcode=:product_barcode,
                            product_qty=:product_qty,
                            product_price=:product_price
                        WHERE id='.$args['id'];

            $sth = $this->db->prepare($strSQL);
            $sth->bindParam(':product_name', $body['product_name']);
            $sth->bindParam(':product_detail', $body['product_detail']);
            $sth->bindParam(':product_barcode', $body['product_barcode']);
            $sth->bindParam(':product_qty', $body['product_qty']);
            $sth->bindParam(':product_price', $body['product_price']);

            if ($sth->execute()) {
                $data = $args['id'];
                $input = [
                    'id' => $data,
                    'message' => 'Success',
                ];
            } else {
                $input = [
                    'id' => '',
                    'message' => 'fail',
                ];
            }
            return $this->response->withJson($input);

        });
    
        // Method Delete
        $app->delete('/products/{id}', function(Request $request, Response $response, array $args) use ($container){
            
            $strSQL = 'DELETE FROM products WHERE id='.$args['id'];
            $sth = $this->db->prepare($strSQL);

            if ($sth->execute()) {
                $data = $args['id'];
                $input = [
                    'id' => $data,
                    'message' => 'Success',
                ];
            } else {
                $input = [
                    'id' => '',
                    'message' => 'fail',
                ];
            }
            return $this->response->withJson($input);


        });

    });

};
