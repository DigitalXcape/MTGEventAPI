<?php
require_once '../logger/Logger.php';
require_once '../models/UserModel.php';

define('JWT_SECRET_KEY', 'gasdf23bd872bncyhbnasdiufg837');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Invalid email or password',
    'token' => null
];

// Function to create a token
function generateJWT($header, $payload, $secret = JWT_SECRET_KEY) {
    $base64UrlHeader = base64UrlEncode(json_encode($header));
    $base64UrlPayload = base64UrlEncode(json_encode($payload));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64UrlEncode($signature);
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logger = Logger::getInstance();

    $email = $_POST['email'];
    $password = $_POST['password'];
    $logger->log("Attempting Login with email: $email");

    try {
        $model = new UserModel();
        $user = $model->getUserByEmail($email);

        if ($user && $model->validateUser($email, $password)) {

            $header = ['alg' => 'HS256', 'typ' => 'JWT'];

            //create a token
            $payload = [
                'user_id' => $user['UserID'],
                'username' => $user['Username'],
                'email' => $user['Email'],
                'role' => $user['Role'],
                'exp' => time() + 99999 
            ];

            $jwt = generateJWT($header, $payload);

            //set the varables a success
            $response['success'] = true;
            $response['message'] = 'Login successful';
            $response['token'] = $jwt;
            $response['username'] = $user['Username'];
            $response['user_id'] = $user['UserID'];
            $response['role'] = $user['Role'];
            http_response_code(200);

        } else {
            $logger->log("Invalid email or password");
            http_response_code(401);
        }
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
        http_response_code(500);
    }
}
echo json_encode($response);
exit();
?>