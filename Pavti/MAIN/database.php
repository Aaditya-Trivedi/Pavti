<?php
    // Database class to handle database connection and disconnection
    class Database {
        
        // Database credentials
        private $servername = "localhost";  // Hostname of the database server (localhost)
        private $username = "root";  // Username to connect to the database (default is 'root' for local MySQL server)
        private $password = "";  // Password to connect to the database (default is empty for local MySQL)
        private $database = "motrage_web_app_db";  // The name of the database to be used
        public $conn;  // Variable to hold the connection object

        // Method to get a database connection
        public function getConnection(){
            
            // Set the connection variable to null initially
            $this->conn = null;

            try{
                // Connect to the MySQL server without specifying a database yet
                $this->conn = new PDO("mysql:host={$this->servername}", $this->username, $this->password);
                
                // Set the error reporting mode to throw exceptions for any errors
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Execute a query to check if the database exists, and create it if it doesn't
                $this->conn->exec("CREATE DATABASE IF NOT EXISTS {$this->database}");
                //echo "Database '{$this->database}' is ready!<br>";  // Confirm that the database is created or exists

                // Now connect to the newly created or existing database
                $this->conn = new PDO("mysql:host={$this->servername};dbname={$this->database}", $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Set error mode to exception
                //echo "Connected to the database successfully!";  // Confirm successful connection
            } catch (PDOException $e) {
                // Catch any exceptions (errors) during the connection and display an error message
                //echo "Connection failed: " . $e->getMessage();
            }

            // Return the connection object
            return $this->conn;
        }

        // Method to close the database connection
        public function closeConnection() {
            $this->conn = null;  // Set the connection to null, which effectively closes the connection
            //echo "Database connection closed.";  // Confirm that the connection has been closed
        }
    }
?>
