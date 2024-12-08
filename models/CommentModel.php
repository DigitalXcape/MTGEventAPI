<?php
require_once '../logger/Logger.php';
require_once '../models/UserModel.php';

class CommentModel {
    private $conn;
    private $logger;

    public function __construct() {
        try {
            $this->conn = new PDO('mysql:host=localhost;dbname=db_mtgeventmaster', 'root', '');
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->logger = Logger::getInstance();
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    //gets a comment by its id
    public function getCommentById($comment_id) {
        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("SELECT * FROM comments WHERE CommentID = :comment_id");
                $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    return [
                        'CommentID' => $result['CommentID'],
                        'CreatorID' => $result['CreatorID'],
                        'Likes' => $result['Likes'],
                        'DateCreated' => $result['DateCreated'],
                        'Body' => $result['Body'],
                    ];
                }
                return null;
            } catch (PDOException $e) {
                $this->logger->log("Query failed: " . $e->getMessage());
                return null;
            }
        } else {
            $this->logger->log("No connection.");
            return null;
        }
    }

    //gets all comments on a post
    public function getCommentsByPostId($post_id) {
        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("SELECT * FROM comments WHERE PostID = :post_id");
                $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $comments = [];
    
                $userModel = new UserModel();
    
                foreach ($results as $result) {
                    $userName = "Deleted User";
    
                    try {
                        $user = $userModel->getUserById($result['CreatorID']);
                        if ($user && isset($user['Username'])) {
                            $userName = $user['Username'];
                        }
                    } catch (Exception $e) {
                        $this->logger->log("Failed to retrieve user for CreatorID " . $result['CreatorID'] . ": " . $e->getMessage());
                    }
    
                    $comments[] = [
                        'CommentID' => $result['CommentID'],
                        'CreatorID' => $result['CreatorID'],
                        'Likes' => $result['Likes'],
                        'DateCreated' => $result['DateCreated'],
                        'Body' => $result['Body'],
                        'Username' => $userName
                    ];
                }
                return $comments;
            } catch (PDOException $e) {
                $this->logger->log("Query failed: " . $e->getMessage());
                return [];
            }
        } else {
            $this->logger->log("No connection.");
            return [];
        }
    }

    //create a comment
    public function createComment($creatorId, $postId, $body, $dateCreated) {
        if ($this->conn) {
            try {
                $likes = 0;
    
                $stmt = $this->conn->prepare(
                    "INSERT INTO comments (CreatorID, PostID, Likes, DateCreated, Body) 
                    VALUES (:creator_id, :post_id, :likes, :date_created, :body)"
                );
                $stmt->bindParam(':creator_id', $creatorId, PDO::PARAM_INT);
                $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
                $stmt->bindParam(':likes', $likes, PDO::PARAM_INT);
                $stmt->bindParam(':date_created', $dateCreated);
                $stmt->bindParam(':body', $body, PDO::PARAM_STR);
    
                $stmt->execute();
                $this->logger->log("Comment created successfully for PostID: $postId by CreatorID: $creatorId.");
                return true;
            } catch (PDOException $e) {
                $this->logger->log("Failed to create comment: " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->log("No connection.");
            return false;
        }
    }
    
    //delete a comment
    public function deleteComment($commentId) {
        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("DELETE FROM comments WHERE CommentID = :comment_id");
                $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);

                $stmt->execute();
                $this->logger->log("Comment with CommentID: $commentId deleted successfully.");
                return true;
            } catch (PDOException $e) {
                $this->logger->log("Failed to delete comment: " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->log("No connection.");
            return false;
        }
    }

    //get all the comments
    public function getAllComments() {
        if ($this->conn) {
            try {
                $stmt = $this->conn->query("SELECT CommentID FROM comments");
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $comments = [];

                foreach ($results as $result) {
                    $comment = $this->getCommentById($result['CommentID']);
                    if ($comment) {
                        $comments[] = $comment;
                    }
                }
                return $comments;
            } catch (PDOException $e) {
                $this->logger->log("Query failed: " . $e->getMessage());
                return [];
            }
        } else {
            $this->logger->log("No connection.");
            return [];
        }
    }
}
?>