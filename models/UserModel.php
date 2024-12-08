<?php
require_once '../logger/Logger.php';

class UserModel {

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

//get all the users in the database
function getAllUsers() {
    global $logger;
    $users = [];
    
    if ($this->conn) {
        try {
            $stmt = $this->conn->query("SELECT * FROM users");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as $result) {
                $users[] = [
                    'UserID' => $result['UserID'],
                    'Username' => $result['UserName'],
                    'Email' => $result['Email'],
                    'Password' => $result['Password'],
                    'Role' => $result['Role']
                ];

                $logger->log($result['UserName']);
            }
        } catch (PDOException $e) {
            $logger->log("Query failed: " . $e->getMessage());
        }
    } else {
        $logger->log("No connection.");
    }
    
    return $users;
}

//get a specific user from their id
function getUserById($userId) {
    global $logger;
    
    if ($this->conn) {
        try {
            $stmt = $this->conn->prepare("SELECT UserID, UserName, Email, Password, Role FROM users WHERE UserID = :userId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {

                $logger->log('User: ' . $result['UserName'] . ' was found');

                return [
                    'UserID' => $result['UserID'],
                    'Username' => $result['UserName'],
                    'Email' => $result['Email'],
                    'Password' => $result['Password'],
                    'Role' => $result['Role']
                ];

            } else {
                return null;
            }
        } catch (PDOException $e) {
            $logger->log("Query failed: " . $e->getMessage());
            return null;
        }
    } else {
        $logger->log("No connection.");
        return null;
    }
}

//get a user by their email
function getUserByEmail($email) {
    global $logger;

    if ($this->conn) {
        try {
            $stmt = $this->conn->prepare("SELECT UserID, UserName, Email, Password, Role FROM users WHERE Email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR); // Use PDO::PARAM_STR for strings
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $logger->log('User: ' . $result['UserName'] . ' was found');

                return [
                    'UserID' => $result['UserID'],
                    'Username' => $result['UserName'],
                    'Email' => $result['Email'],
                    'Password' => $result['Password'],
                    'Role' => $result['Role']
                ];
            } else {
                return null;
            }
        } catch (PDOException $e) {
            $logger->log("Query failed: " . $e->getMessage());
            return null;
        }
    } else {
        $logger->log("No connection.");
        return null;
    }
}

//add a user to the database
function addUser($userName, $email, $password) {
    global $logger;
    
    if ($this->conn) {
        try {
            $lengthPattern = '/^.{8,20}$/';
            $numberPattern = '/[0-9]/';
            $lowercasePattern = '/[a-z]/';
            $uppercasePattern = '/[A-Z]/';
    
            $requirements = [];
    
            if (!preg_match($lengthPattern, $password)) {
                $requirements[] = "Password must be between 8 and 20 characters long.";
            }
            if (!preg_match($numberPattern, $password)) {
                $requirements[] = "Password must contain at least one number.";
            }
            if (!preg_match($lowercasePattern, $password)) {
                $requirements[] = "Password must contain at least one lowercase letter.";
            }
            if (!preg_match($uppercasePattern, $password)) {
                $requirements[] = "Password must contain at least one uppercase letter.";
            }
    
            if (count($requirements) > 0) {
                throw new Exception(implode("\n", $requirements));
            }
    
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
            $stmt = $this->conn->prepare("INSERT INTO users (UserName, Email, Password) VALUES (:userName, :email, :password)");
            $stmt->bindParam(':userName', $userName, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) {
                $logger->log('User ' . $userName . ' added successfully.');
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $logger->log("Insert failed: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $logger->log("Validation failed for user '$userName': " . $e->getMessage());
            return false;
        }
    } else {
        $logger->log("No connection.");
        return false;
    }

}

//validate user to make sure their password is correct
    public function validateUser($email, $password) {
        $this->logger->log("Attempting to validate password for email: " . $email);
    
        if (!$this->conn) {
            $this->logger->log("No database connection.");
            return false;
        }
    
        try {

            $stmt = $this->conn->prepare("SELECT Password FROM users WHERE Email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    

            if (!$result) {
                $this->logger->log("User not found for email: " . $email);
                return false;
            }
            $this->logger->log("Hashed password:" . $result['Password']);
            $this->logger->log("Inputted Password:" . $password);

            //validate the hashed password
            if (password_verify($password, $result['Password'])) {
                $this->logger->log("Password validation successful for email: " . $email);
                return true;
            } else {
                $this->logger->log("Password validation failed for email: " . $email);
                return false;
            }
        } catch (PDOException $e) {
            $this->logger->log("Validation query failed: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->logger->log("Validation error: " . $e->getMessage());
            return false;
        }
    }
}
