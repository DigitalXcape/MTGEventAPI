<?php
class Logger {
    private static $instance = null;
    private $logFile;

    private function __construct($file) {
        $this->logFile = $file;
    }

    public static function getInstance($file = '../logger/log.log') {
        if (self::$instance === null) {
            self::$instance = new Logger($file);
        }
        return self::$instance;
    }

    public function log($message) {
        $date = date('Y-m-d H:i:s');
        $formattedMessage = "[$date] $message\n";
        if (file_put_contents($this->logFile, $formattedMessage, FILE_APPEND) === false) {
            throw new Exception("Unable to write to log file: " . $this->logFile);
        }
    }
}
?>