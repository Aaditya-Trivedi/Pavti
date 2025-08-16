<?php
session_start(); // Start a new session or resume the existing one
include 'database.php';  // Include the database connection file

$database = new Database();  // Create a new instance of the Database class
$conn = $database->getConnection();  // Establish the database connection

$error = "";  // Initialize an empty string to store error messages

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);  // Get and trim the username from the form
    $password = $_POST['password'];  // Get the password from the form

    try {
        // Prepare the SQL statement to fetch user data based on the provided username
        $stmt = $conn->prepare("SELECT * FROM admin_table WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);  // Bind the username parameter
        $stmt->execute();  // Execute the statement
        $user = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch the user data

        if ($user) {
            // If user is found, verify the password using password_verify
            if (password_verify($password, $user['password'])) {
                // Store user data in session variables
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on the user's role (admin or user)
                if ($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php");  // Redirect to admin dashboard
                    exit();  // Stop further script execution
                }
                
            } else {
                $error = "<div class='alert alert-danger'>Invalid password.</div>";  // Invalid password
            }
        } else {
            $error = "<div class='alert alert-danger'>User not found.</div>";  // User not found
        }
    } catch (PDOException $e) {
        $error = "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";  // Database error
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">  <!-- Character encoding for the webpage -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- Make the page mobile responsive -->
    <title>Login</title>  <!-- Page title displayed in the browser tab -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  <!-- Link to Bootstrap CSS for styling -->
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;  /* Ensure the body covers full height of the screen */
            background-color: #f8f9fa;  /* Light background color */
        }

        .content {
            flex: 1;
            display: flex;
            justify-content: center;  /* Center content horizontally */
            align-items: center;  /* Center content vertically */
        }

        .blur-bg {
            background: rgba(255, 255, 255, 0.7);  /* Semi-transparent background */
            backdrop-filter: blur(10px);  /* Apply blur effect */
            border-radius: 10px;  /* Rounded corners */
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);  /* Box shadow for a floating effect */
        }

        .footer {
            background-color: #ea6d06;  /* Orange background color for footer */
            color: white;  /* White text color */
            padding: 15px 0;  /* Vertical padding */
        }

        .footer a {
            color: white;  /* White text color for links */
            text-decoration: none;  /* Remove underline from links */
        }

        .footer a:hover {
            text-decoration: underline;  /* Underline links on hover */
        }
    </style>
</head>
<body>
    <!-- Navbar with brand name -->
    <nav class="navbar navbar-dark border-bottom border-body" style="background-color: #ea6d06;">
        <div class="container-fluid">
            <a class="navbar-brand">Tanishi Infotech</a>  <!-- Website name/logo -->
        </div>
    </nav>

    <!-- Main content section -->
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 blur-bg">  <!-- Form container with background blur -->
                    <h1 class="text-center mb-4">Welcome to Website!</h1>  <!-- Page title -->

                    <!-- Display error message if any -->
                    <?php echo $error; ?>

                    <!-- Login form -->
                    <form method="post">
                        <!-- Username Field -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>  <!-- Label for username -->
                            <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>  <!-- Input field for username -->
                        </div>

                        <!-- Password Field with Show/Hide Icon -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>  <!-- Label for password -->
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>  <!-- Password input field -->
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="bi bi-eye"></i>  <!-- Show/hide password icon -->
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success w-100">Login</button>  <!-- Submit button for login -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer section -->
    <footer class="footer text-center">
        <div class="container">
            <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.  <!-- Copyright message -->
                <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a> | <a href="#">Contact Us</a>  <!-- Footer links -->
            </p>
        </div>
    </footer>

    <script>
        // Toggle password visibility
        const passwordField = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function () {
            const type = passwordField.type === "password" ? "text" : "password";  // Toggle the password input field type
            passwordField.type = type;
            this.innerHTML = passwordField.type === "password" 
                ? '<i class="bi bi-eye"></i>'  // Show eye icon when password is hidden
                : '<i class="bi bi-eye-slash"></i>';  // Show eye-slash icon when password is visible
        });
    </script>

    <!-- Bootstrap and icon scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">  <!-- Bootstrap Icons for eye icon -->
</body>
</html>
