<?php
/*
    CONNECTION UTILITY
        *Use this class to establish a connection with the database.
        *This file uses the configuration set in Config.php
        *Return the needed information to help resolve the issue
*/
class DbConnect {
 
    private $conn;
 
    function __construct() {        
    }
 
    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect() {
        include_once dirname(__FILE__) . './Config.php';
       // include_once 'Config.php';
        // Connecting to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
        // Check for database connection error
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL Database: " . mysqli_connect_error();
        }
 
        // returing connection resource
        return $this->conn;
    }
 
}
 
?>