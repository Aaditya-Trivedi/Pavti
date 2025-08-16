<?php
// Start the session to track user login status
session_start();

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Include the Database class
include 'database.php';

// Create an instance of the Database class
$database = new Database();
$conn = $database->getConnection();

// Check if the 'delete' parameter is provided in the URL
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $userId = intval($_GET['delete']); // Sanitize the user ID to prevent SQL injection

    // Prepare and execute the query to fetch user details for confirmation
    $query = "SELECT * FROM user_table WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // If the user exists, proceed to delete
    if ($stmt->rowCount() > 0) {
        // Prepare and execute the delete query
        $deleteQuery = "DELETE FROM user_table WHERE id = :id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':id', $userId, PDO::PARAM_INT);

        if ($deleteStmt->execute()) {
            header("Location: admin_view_users.php?success=1"); // Redirect with success message
            exit;
        } else {
            // If deletion fails
            $error = "Failed to delete user.";
        }
    } else {
        // If user not found
        $error = "User not found.";
    }
} else {
    // If no user ID is provided
    $error = "Invalid user ID.";
}

// Show error if any
if (isset($error)) {
    echo $error;
    echo "<br><a href='admin_view_users.php'>Go Back</a>";
}
?>
