<?php
require_once '../models/CommentModel.php';
require_once '../logger/Logger.php';

header('Content-Type: application/json');

$logger = Logger::getInstance();

$response = [
    'success' => false,
    'comments' => [],
    'message' => 'Failed to retrieve comments'
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $logger->log("Attempting to get comments");

    $post_id = $_GET['post_id'] ?? null;

    if ($post_id) {
        try {
            $model = new CommentModel();
            
            //get all the comments
            $comments = $model->getCommentsByPostId($post_id);

            // Check if comments are found
            if (!empty($comments)) {
                $response['success'] = true;
                $response['comments'] = $comments;
                $response['message'] = 'Comments retrieved successfully';
                $logger->log("Comments retrieved: " . count($comments) . " comments found.");
                http_response_code(200);
            } else {
                $response['message'] = 'No comments found';
                $logger->log("No comments found for post_id: $post_id.");
                http_response_code(404);
            }
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            $logger->log("Exception occurred: " . $e->getMessage());
            http_response_code(500);
        }
    } else {
        $response['message'] = "post_id is missing or invalid";
        http_response_code(400);
    }
}

echo json_encode($response);
exit();