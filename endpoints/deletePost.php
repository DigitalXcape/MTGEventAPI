<?php
require_once '../models/PostModel.php';
require_once '../logger/Logger.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Failed to delete post'
];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Retrieve the post ID from the request
    parse_str(file_get_contents("php://input"), $input);
    $postId = isset($input['post_id']) ? intval($input['post_id']) : null;

    if ($postId) {
        try {
            $model = new PostModel();
            $logger = Logger::getInstance();

            // Call deletePost to remove the post from the database
            if ($model->deletePost($postId)) {
                $response['success'] = true;
                $response['message'] = 'Post deleted successfully';
                http_response_code(200);
                $logger->log("Post deleted with PostID: $postId.");
            } else {
                $logger->log("Failed to delete post with PostID: $postId.");
            }
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            http_response_code(500);
            $logger->log("Exception occurred: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Invalid input: Post ID is required';
        http_response_code(400);
    }
} else {
    $response['message'] = 'Invalid request method. DELETE required.';
    http_response_code(405);
}

echo json_encode($response);
exit();