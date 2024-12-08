<?php
require_once '../logger/Logger.php';
require_once '../models/UserModel.php';

class NotificationModel {
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

    //create a notification
    public function createNotification($userId, $message) {
        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare(
                    "INSERT INTO notifications (UserID, Message) 
                    VALUES (:user_id, :message)"
                );
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    
                $stmt->execute();
                $this->logger->log("Notification successfully created for user: " . $userId);
                return true;
            } catch (PDOException $e) {
                $this->logger->log("Failed to create notification: " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->log("No connection.");
            return false;
        }
    }

    //get all the notifications for a specific user
    public function getNotificationsByUserID($userId) {
        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE UserID = :user_id");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $notifications = [];

                foreach ($results as $result) {
                    $notifications[] = [
                        'Message' => $result['Message'],
                    ];
                }
                return $notifications;
            } catch (PDOException $e) {
                $this->logger->log("Query failed: " . $e->getMessage());
                return [];
            }
        } else {
            $this->logger->log("No connection.");
            return [];
        }
    }

    //delete all the notifications for a user
    public function deleteNotificationsByUserID($userId)
    {
        if ($this->conn) {
            try {
                $stmt = $this->conn->prepare("DELETE FROM notifications WHERE UserID = :user_id");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

                $stmt->execute();

                // Log success
                $this->logger->log("Deleted notifications for user ID: " . $userId);

                return true;
            } catch (PDOException $e) {

                $this->logger->log("Failed to delete notifications: " . $e->getMessage());

                return false;
            }
        } else {
            $this->logger->log("No connection.");
            return false;
        }
    }
}