<?php
require_once '../models/PostModel.php';
require_once '../logger/Logger.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Failed to retrieve posts'
];

$logger = Logger::getInstance();

$logger->log("GET request received for fetching posts.");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $model = new PostModel();

        $logger->log("Attempting to fetch all posts from the database.");

        //get all the posts
        $posts = $model->getAllPosts();

        $logger->log("Posts data: " . json_encode($posts));

        if ($posts) {
            $logger->log("Raw Posts Data: " . json_encode($posts));

            $response['success'] = true;
            $response['message'] = 'Posts retrieved successfully';
            $response['data'] = [];

            foreach ($posts as $post) {
                $response['data'][] = [
                    'post_id' => $post['PostID'],
                    'description' => $post['Description'],
                    'location' => $post['Location'],
                    'date_held' => $post['DateHeld'],
                    'date_created' => $post['DateCreated'],
                    'likes' => $post['Likes'],
                    'title' => $post['Title'],
                ];
            }

            $logger->log("Formatted Response Data: " . json_encode($response['data']));
        } else {
            $response['success'] = false;
            $response['message'] = 'No posts found';
        }
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
        http_response_code(500);
        
        $logger->log("Exception occurred: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method. GET required.';
    http_response_code(405);
    
    $logger->log("Invalid request method received: " . $_SERVER['REQUEST_METHOD']);
}

$logger->log("Response: " . json_encode($response));

echo json_encode($response);
exit();