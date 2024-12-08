<?php
require_once '../logger/Logger.php';
require_once '../models/UserModel.php';

class PostModel {
    private $conn;
    private $logger;

    // Define constants for table and column names to prevent mistakes
    const TABLE_NAME = 'posts';
    const POST_ID = 'PostID';
    const CREATOR_ID = 'CreatorID';
    const DESCRIPTION = 'Description';
    const LOCATION = 'Location';
    const DATE_HELD = 'DateHeld';
    const DATE_CREATED = 'DateCreated';
    const LIKES = 'Likes';
    const TITLE = 'Title';

    public function __construct() {
        try {
            $this->conn = new PDO('mysql:host=localhost;dbname=db_mtgeventmaster', 'root', '');
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->logger = Logger::getInstance();
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    //get a post by its id
    public function getPostById($post_id) {
        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::POST_ID . " = :post_id");
                $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
                $this->logger->log("Attempting to retrieve post with ID: " . $post_id);
    
                $userName = "Deleted User";
    
                if ($result) {
                    try {
                        $userModel = new UserModel();
                        $user = $userModel->getUserById($result[self::CREATOR_ID]);
    

                        if ($user && isset($user['Username'])) {
                            $userName = $user['Username'];
                        }
                    } catch (Exception $e) {
                        $this->logger->log("Failed to retrieve user with ID " . $result[self::CREATOR_ID] . ": " . $e->getMessage());
                    }
    
                    return [
                        'PostID' => $result[self::POST_ID],
                        'CreatorID' => $result[self::CREATOR_ID],
                        'Description' => $result[self::DESCRIPTION],
                        'Location' => $result[self::LOCATION],
                        'DateHeld' => $result[self::DATE_HELD],
                        'DateCreated' => $result[self::DATE_CREATED],
                        'Likes' => $result[self::LIKES],
                        'Title' => $result[self::TITLE],
                        'Username' => $userName,
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

    //get all the posts from the database
    public function getAllPosts() {
        if ($this->conn) {
            try {
                $this->logger->log("Retrieving all posts");

                $stmt = $this->conn->query("SELECT * FROM " . self::TABLE_NAME);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $this->logger->log("Raw results: " . json_encode($results));

                $posts = [];
                foreach ($results as $result) {
                    $posts[] = [
                        'PostID' => $result[self::POST_ID],
                        'CreatorID' => $result[self::CREATOR_ID],
                        'Description' => $result[self::DESCRIPTION],
                        'Location' => $result[self::LOCATION],
                        'DateHeld' => $result[self::DATE_HELD],
                        'DateCreated' => $result[self::DATE_CREATED],
                        'Likes' => $result[self::LIKES],
                        'Title' => $result[self::TITLE],
                    ];
                }

                return $posts;
            } catch (PDOException $e) {
                $this->logger->log("Query failed: " . $e->getMessage());
                return [];
            }
        } else {
            $this->logger->log("No connection.");
            return [];
        }
    }

    //create a post
    public function createPost($creator_id, $title, $description, $location, $date_held) {
        if ($this->conn) {
            try {
                $currentDate = date('Y/m/d');
    
                $stmt = $this->conn->prepare(
                    "INSERT INTO " . self::TABLE_NAME . " (" . self::CREATOR_ID . ", " . self::TITLE . ", " . self::DESCRIPTION . ", " . self::LOCATION . ", " . self::DATE_HELD . ", " . self::DATE_CREATED . ") 
                    VALUES (:creator_id, :title, :description, :location, :date_held, :date_created)"
                );
    

                $stmt->bindParam(':creator_id', $creator_id, PDO::PARAM_INT);
                $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':location', $location, PDO::PARAM_STR);
                $stmt->bindParam(':date_held', $date_held, PDO::PARAM_STR);
                $stmt->bindParam(':date_created', $currentDate, PDO::PARAM_STR);
    

                $stmt->execute();
    

                return $this->conn->lastInsertId();
            } catch (PDOException $e) {
                $this->logger->log("Post creation failed: " . $e->getMessage());
                return null;
            } catch (Exception $e) {

                $this->logger->log("Error: " . $e->getMessage());
                return null;
            }
        } else {
            $this->logger->log("No connection.");
            return null;
        }
    }

    //delete a post from the database
    public function deletePost($post_id) {
        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("DELETE FROM " . self::TABLE_NAME . " WHERE " . self::POST_ID . " = :post_id");
                $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $this->logger->log("Post deleted: PostID $post_id.");
                    return true;
                } else {
                    $this->logger->log("No post found with PostID $post_id to delete.");
                    return false;
                }
            } catch (PDOException $e) {
                $this->logger->log("Post deletion failed: " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->log("No connection.");
            return false;
        }
    }
}
?>