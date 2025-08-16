<?php
// Start the session
session_start();  // Initializes the session to access session variables

// Destroy all session variables
$_SESSION = array();  // Clears all session data by setting the session array to an empty array

// Destroy the session
session_destroy();  // Ends the session and deletes session data from the server

// Redirect to login page (or any desired page)
header("Location: index.php");  // Sends a header to redirect the user to the login page
exit;  // Ensure the script stops executing after the redirect
?>
