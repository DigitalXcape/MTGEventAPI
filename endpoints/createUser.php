<?php
require_once '../models/UserModel.php';
require_once '../logger/Logger.php';
ob_start();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Failed to create user.'
];

// Create a logger instance
$logger = Logger::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the variables from post
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $model = new UserModel();

        //add the user
        $success = $model->addUser($username, $email, $password);
        if ($success) {
            $response['success'] = true;
            $response['message'] = "User created successfully!";
            $logger->log("User '$username' created successfully.");
            http_response_code(200);
        } else {
            $response['message'] = "Failed to create user.";
            $logger->log("Failed to create user '$username'.");
            http_response_code(401);
        }
        
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
        $logger->log("Error while creating user '$username': " . $e->getMessage());
        http_response_code(500);
    }

    echo json_encode($response);
    exit();
} else {
    $response['message'] = "Invalid request method.";
    http_response_code(401);
    ob_end_clean();
    echo json_encode($response);
    exit();
}
?>