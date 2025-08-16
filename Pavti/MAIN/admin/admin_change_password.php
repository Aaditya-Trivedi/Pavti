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
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #ea6d06;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" style="color: white;">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php" style="color: white;">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_view_users.php" style="color: white;">View Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_add_user.php" style="color: white;">Add User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_change_password.php" style="color: white;">Change Password</a>
                    </li>
                </ul>
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

    <!-- Main Content -->
    <div class="container my-4">
        <div class="header text-center mb-4">
            <h1>Change Admin Password</h1>
        </div>

        <!-- Change Password Form -->
        <form action="change_password.php" method="post">
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword2">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword3">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Change Password</button>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer text-center py-3">
        <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.</p>
    </footer>

    <script>
        const passwordField1 = document.getElementById("current_password");
        const togglePassword1 = document.getElementById("togglePassword1");

        togglePassword1.addEventListener("click", function () {
            const type = passwordField1.type === "password" ? "text" : "password";
            passwordField1.type = type;
            this.innerHTML = passwordField1.type === "password" 
                ? '<i class="bi bi-eye"></i>' 
                : '<i class="bi bi-eye-slash"></i>';
        });
    </script>

    <script>
        const passwordField2 = document.getElementById("new_password");
        const togglePassword2 = document.getElementById("togglePassword2");

        togglePassword2.addEventListener("click", function () {
            const type = passwordField2.type === "password" ? "text" : "password";
            passwordField2.type = type;
            this.innerHTML = passwordField2.type === "password" 
                ? '<i class="bi bi-eye"></i>' 
                : '<i class="bi bi-eye-slash"></i>';
        });
    </script>

    <script>
        const passwordField3 = document.getElementById("confirm_password");
        const togglePassword3 = document.getElementById("togglePassword3");

        togglePassword3.addEventListener("click", function () {
            const type = passwordField3.type === "password" ? "text" : "password";
            passwordField3.type = type;
            this.innerHTML = passwordField3.type === "password" 
                ? '<i class="bi bi-eye"></i>' 
                : '<i class="bi bi-eye-slash"></i>';
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
