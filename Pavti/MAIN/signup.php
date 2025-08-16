<?php
// Start the session
session_start();  // Initializes session to track user sessions

// Include the database file for connection
include 'database.php';  // Ensure the path to your database file is correct

// Create a new Database object and establish the connection
$database = new Database();
$conn = $database->getConnection();

// Initialize message variable to display error/success messages
$message = "";

// Ensure the 'user_table' exists, if not, create it
try {
    // SQL query to create 'user_table' if it does not already exist
    $sql = "CREATE TABLE IF NOT EXISTS admin_table (
        id INT AUTO_INCREMENT PRIMARY KEY,  -- Auto-incrementing ID for each user
        username VARCHAR(50) NOT NULL UNIQUE,  -- Unique username
        email VARCHAR(100) NOT NULL UNIQUE,  -- Unique email
        contact VARCHAR(15) NOT NULL UNIQUE,  -- Unique contact number
        password VARCHAR(255) NOT NULL,  -- Hashed password field
        role ENUM('admin', 'user') NOT NULL  -- User role: either admin or user
    )";
    
    // Execute the query
    $conn->exec($sql);
} catch (PDOException $e) {
    // Display error message if the table creation fails
    $message = "<div class='alert alert-danger'>Table creation failed: " . $e->getMessage() . "</div>";
}

// Check if the form is submitted
if (isset($_POST['btn_signup']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Check if the passwords match
    if ($password === $confirm_password) {
        // Check if the username already exists in the database
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_table WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $userCount = $stmt->fetchColumn();  // Fetch the count of matching usernames

        // If the username exists, show error message
        if ($userCount > 0) {
            $message = "<div class='alert alert-danger'>Username already exists. Please choose a different one.</div>";
        } else {
            // Hash the password for secure storage
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Prepare and execute the SQL query to insert a new user into the database
                $stmt = $conn->prepare("INSERT INTO admin_table (username, email, contact, password, role) VALUES (:username, :email, :contact, :password, :role)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':contact', $contact);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                $stmt->execute();

                // Success message if registration is successful
                $message = "<div class='alert alert-success'>
                Registration successful. You can now <a href='login.php' class='alert-link'>login</a>.
                </div>";

            } catch (PDOException $e) {
                // Display error message if insertion fails
                $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    } else {
        // Display error message if passwords do not match
        $message = "<div class='alert alert-danger'>Passwords do not match.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Bootstrap CSS link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General body styling */
        body {
            display: flex;  /* Flexbox layout to position content easily */
            flex-direction: column;  /* Ensures the content is stacked vertically */
            min-height: 100vh;  /* Ensures the body takes full viewport height */
            background-color: #f8f9fa;  /* Light gray background color */
        }

        /* Content section that holds the sign-up form */
        .content {
            flex: 1;  /* Makes this section flexible so it takes the remaining space */
            display: flex;  /* Flexbox for centering content */
            justify-content: center;  /* Centers content horizontally */
            align-items: center;  /* Centers content vertically */
        }

        /* Styling for the background of the sign-up form with blur effect */
        .blur-bg {
            background: rgba(255, 255, 255, 0.7);  /* Semi-transparent white background */
            backdrop-filter: blur(10px);  /* Applies a blur effect to the background */
            border-radius: 10px;  /* Rounded corners for the form */
            padding: 30px;  /* Padding around the content */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);  /* Light shadow to create depth */
        }

        /* Footer section styling */
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



        /* Input fields with focus effect */
        input:focus, select:focus, textarea:focus {
            outline: none;  /* Removes the default outline */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);  /* Adds blue glow effect on focus */
        }

        /* Custom styling for the toggle password button */
        .btn-outline-secondary {
            border-radius: 0 5px 5px 0;  /* Rounded top-left corner, other corners remain sharp */
        }

        /* Adjust button size for password visibility toggle */
        .btn {
            padding: 0.375rem 0.75rem;  /* Standard button padding */
            font-size: 1rem;  /* Font size of the button */
        }

        /* General input field spacing */
        .mb-3 {
            margin-bottom: 1.5rem;  /* Adds margin between form fields */
        }

        /* Form container padding */
        .container {
            padding: 20px;  /* Adds padding inside the container */
        }

    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-dark border-bottom border-body" style="background-color: #ea6d06;">
        <div class="container-fluid">
            <a class="navbar-brand">Tanishi Infotech</a>
        </div>
    </nav>
    <br><br>

    <!-- Main content for sign-up -->
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 blur-bg">
                    <h1 class="text-center mb-4">Sign Up</h1>

                    <!-- Display error or success message -->
                    <?php echo $message; ?>

                    <!-- Sign-up form -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <input type="text" id="contact" name="contact" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Role selection -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" name="btn_signup" class="btn btn-primary w-100">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle password visibility script -->
    <script>
        const passwordField = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function () {
            const type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;
            this.innerHTML = passwordField.type === "password" 
                ? '<i class="bi bi-eye"></i>' 
                : '<i class="bi bi-eye-slash"></i>';
        });
    </script>

    <!-- Toggle confirm password visibility script -->
    <script>
        const passwordField1 = document.getElementById("confirm_password");
        const togglePassword1 = document.getElementById("togglePassword1");

        togglePassword1.addEventListener("click", function () {
            const type = passwordField1.type === "password" ? "text" : "password";
            passwordField1.type = type;
            this.innerHTML = passwordField1.type === "password" 
                ? '<i class="bi bi-eye"></i>' 
                : '<i class="bi bi-eye-slash"></i>';
        });
    </script>

    <!-- Footer section -->
    <footer class="footer text-center">
        <div class="container">
            <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS and Bootstrap Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
