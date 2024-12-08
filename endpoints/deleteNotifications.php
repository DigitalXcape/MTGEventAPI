<?php
require_once '../models/NotificationModel.php';
require_once '../logger/Logger.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Failed to retrieve notifications'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);

        try {
            $model = new NotificationModel();
            $logger = Logger::getInstance();

            $logger->log("Attempting to delete notifications for user id: $userId");

            $notifications = $model->deleteNotificationsByUserID($userId);

            if ($notifications) {
                $response['success'] = true;
                $response['message'] = 'Notifications deleted successfully';
                
                http_response_code(200);
                $logger->log("Notifications deleted successfully: UserID: $userId");
            } else {
                $response['message'] = 'Notifications not found';
                http_response_code(404);
                $logger->log("Notifications not found: UserID: $userId");
            }
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            http_response_code(500);
            $logger->log("Exception occurred: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Invalid input: User ID is required';
        http_response_code(400);
    }
} else {
    $response['message'] = 'Invalid request method. GET required.';
    http_response_code(405);
}

echo json_encode($response);
exit();