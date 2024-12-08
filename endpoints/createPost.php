<?php
require_once '../models/PostModel.php';
require_once '../logger/Logger.php';
require_once '../middleware/tokenVerification.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Failed to create post'
];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve input data from POST request
    $token = isset($_POST['token']) ? $_POST['token'] : null;
    $creatorId = isset($_POST['creator_id']) ? intval($_POST['creator_id']) : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $location = isset($_POST['location']) ? $_POST['location'] : null;
    $title = isset($_POST['title']) ? $_POST['title'] : null;
    $dateHeld = isset($_POST['date_held']) ? $_POST['date_held'] : null;

    if (!$token) {
        $response['message'] = 'Missing token';
        http_response_code(401);
        echo json_encode($response);
        exit();
    }

    // Validate the token
    $logger = Logger::getInstance();

    $middleware = new JWTValidator();
    $decodedPayload = $middleware->validateJWT($token);

    if (!$decodedPayload) {
        $response['message'] = 'Invalid or expired token';
        http_response_code(401);
        $logger->log("Invalid or expired token provided.");
        echo json_encode($response);
        exit();
    }

    if ($decodedPayload['user_id'] !== $creatorId) {
        $response['message'] = 'Unauthorized user';
        http_response_code(403); // Forbidden
        $logger->log("User ID in token does not match Creator ID: $creatorId.");
        echo json_encode($response);
        exit();
    }

    //if everything is looking good, send the post to the database in the model
    if ($creatorId && $description && $location && $dateHeld) {
        try {
            $model = new PostModel();

            $postId = $model->createPost($creatorId, $title, $description, $location, $dateHeld);
            
            if ($postId) {
                $response['success'] = true;
                $response['message'] = 'Post created successfully';
                $response['postId'] = $postId;
                http_response_code(201);
                $logger->log("Post created by CreatorID: $creatorId with PostID: $postId.");
            } else {
                $logger->log("Failed to create post by CreatorID: $creatorId.");
            }
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            http_response_code(500);
            $logger->log("Exception occurred: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Invalid input: Creator ID, Description, Location, and Date Held are required';
        http_response_code(400);
    }
} else {
    $response['message'] = 'Invalid request method. POST required.';
    http_response_code(405);
}

echo json_encode($response);
exit();