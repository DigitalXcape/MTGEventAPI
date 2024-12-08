<?php
require_once '../models/PostModel.php';
require_once '../logger/Logger.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Failed to retrieve post'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
        $postId = intval($_GET['post_id']);

        try {
            $model = new PostModel();
            $logger = Logger::getInstance();

            $logger->log("Valid post_id received: $postId");

            $post = $model->getPostById($postId);

            if ($post) {
                $response['success'] = true;
                $response['message'] = 'Post retrieved successfully';
                $response['post'] = $post;
                http_response_code(200);
                $logger->log("Post retrieved successfully: PostID: $postId");
            } else {
                $response['message'] = 'Post not found';
                http_response_code(404);
                $logger->log("Post not found: PostID: $postId");
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
    $response['message'] = 'Invalid request method. GET required.';
    http_response_code(405);
}

echo json_encode($response);
exit();