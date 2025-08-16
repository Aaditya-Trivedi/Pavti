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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
       .dashboard-container {
            margin-top: 100px;
        }
        .dashboard-item {
            text-align: center;
            padding: 20px;
            margin: 15px;
            height: 250px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }
        .dashboard-item i {
            font-size: 50px;

            color: #ea6d06;
        }
        .dashboard-item:hover {
            background-color: #f1f1f1;
        }
        
        html, body {
            height: 100%;
            margin: 0;
        }

        .container {
            min-height: 100%; /* Ensure container stretches the full height */
            display: flex;
            flex-direction: column;
        }

        .content {
            flex-grow: 1; /* This makes sure the content area takes up available space */
        }

        .footer {
            background-color: #ea6d06;
            color: white;
            padding: 15px 0;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg" style="background-color: #ea6d06;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" style="color: white;">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="btn btn-danger" onclick="logoutUser()">Logout</button>
                    </li>
                    <script>
                        function logoutUser() {
                            window.location.href = 'logout.php';
                        }
                    </script>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        <div class="row">
            <!-- View Users Option -->
            <div class="col-md-4">
                <div class="dashboard-item">
                    <br>
                    <i class="fas fa-users"></i>
                    <h4>View Users</h4>
                    <a href="admin_view_users.php" class="btn btn-primary">View All Users</a>
                </div>
            </div>

            <!-- Add New User Option -->
            <div class="col-md-4">
                <div class="dashboard-item">
                    <br>
                    <i class="fas fa-user-plus"></i>
                    <h4>Add New User</h4>
                    <a href="admin_add_user.php" class="btn btn-success">Add New User</a>
                </div>
            </div>

            <!-- Change Password Option -->
            <div class="col-md-4">
                <div class="dashboard-item">
                    <br>
                    <i class="fas fa-key"></i>
                    <h4>Change Password</h4>
                    <a href="admin_change_password.php" class="btn btn-warning">Change Password</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center py-3">
        <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
