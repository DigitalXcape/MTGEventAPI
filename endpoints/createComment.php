<?php
require_once '../models/CommentModel.php';
require_once '../logger/Logger.php';
require_once '../models/NotificationModel.php';
require_once '../models/PostModel.php';
require_once '../middleware/tokenVerification.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Failed to create comment'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logger = Logger::getInstance();
    $logger->log("Attempting to create a comment");
    // set variables that were sent through post
    $creatorId = isset($_POST['creator_id']) ? intval($_POST['creator_id']) : null;
    $postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : null;
    $dateCreated = isset($_POST['date_created']) ? $_POST['date_created'] : date('Y/m/d');
    $commentBody = isset($_POST['comment_body']) ? $_POST['comment_body'] : null;
    $token = isset($_POST['token']) ? $_POST['token'] : null;

    $logger->log("creator_id: $creatorId, post_id: $postId, token: $token, comment_body: $commentBody");
    if ($creatorId && $postId) {
        try {
            $model = new CommentModel();
            $notificationModel = new NotificationModel();
            $postModel = new PostModel();
            $middleware = new JWTValidator();

            //validate token
            $decodedPayload = $middleware->validateJWT($token);

            if (!$decodedPayload) {
                $response['message'] = 'Invalid or expired token';
                http_response_code(401);
                $logger->log("Invalid or expired token provided.");
                echo json_encode($response);
                exit();
            }

            //create the comment
            if ($model->createComment($creatorId, $postId, $commentBody, $dateCreated)) {
                $response['success'] = true;
                $response['message'] = 'Comment created successfully';
                http_response_code(201);
                $logger->log("Comment created for PostID: $postId by CreatorID: $creatorId.");

                // Send notification
                $post = $postModel->getPostById($postId);
                $notificationMessage = "A user commented on your post on $dateCreated: '$commentBody'";
                $notificationModel->createNotification($post['CreatorID'], $notificationMessage);

            } else {
                $logger->log("Failed to create comment for PostID: $postId by CreatorID: $creatorId.");
            }
        } catch (Exception $e) {
            $response['message'] = "Error: " . $e->getMessage();
            http_response_code(500);
            $logger->log("Exception occurred: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Invalid input: Creator ID and Post ID are required';
        $logger->log('Invalid input: Creator ID and Post ID are required');
        http_response_code(400);
    }
} else {
    $response['message'] = 'Invalid request method. POST required.';
    $logger->log('Invalid request method. POST required.');
    http_response_code(405);
}

echo json_encode($response);
exit();