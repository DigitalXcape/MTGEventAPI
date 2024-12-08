<?php
require_once '../models/UserModel.php';
require_once '../logger/Logger.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'User deletion failed'
];

try {
    $logger = Logger::getInstance();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Logger initialization failed: " . $e->getMessage()]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //get the user ID that was send through post
    $userId = $_POST['userId'];

    try {
        $logger->log("Received request to delete user with ID: $userId");

        $model = new UserModel();
        
        //attempt to delete the user
        $success = $model->deleteUser($userId);

        if ($success) {
            $response['success'] = true;
            $response['message'] = 'User deleted successfully';
            $logger->log("User with ID: $userId deleted successfully.");
            http_response_code(200);
        } else {
            $response['message'] = 'Failed to delete user';
            $logger->log("Failed to delete user with ID: $userId. User may not exist.");
            http_response_code(401);
        }
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
        $logger->log("Error while attempting to delete user with ID: $userId. Exception: " . $e->getMessage());
        http_response_code(500);
    }
}

echo json_encode($response);
exit();
?>