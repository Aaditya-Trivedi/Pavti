<?php
// Start the session to track user login status
session_start();

// Check if the user is logged in and is an admin, otherwise redirect to the login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Include the Database class
include 'database.php';

// Create an instance of the Database class
$database = new Database();
$conn = $database->getConnection();

// Initialize message variable to display error/success messages
$message = "";

// Ensure the 'user_table' exists, if not, create it
try {
    // SQL query to create 'user_table' if it does not already exist
    $sql = "CREATE TABLE IF NOT EXISTS user_table (
        id INT AUTO_INCREMENT PRIMARY KEY,  -- Auto-incrementing ID for each user
        username VARCHAR(50) NOT NULL UNIQUE,  -- Unique username
        email VARCHAR(100) NOT NULL UNIQUE,  -- Unique email
        contact VARCHAR(15) NOT NULL UNIQUE,  -- Unique contact number
        password VARCHAR(255) NOT NULL,  -- Hashed password field
        role ENUM('admin', 'user') NOT NULL,  -- User role: either admin or user
        expiryDate DATE NOT NULL
    )";
    
    // Execute the query
    $conn->exec($sql);
} catch (PDOException $e) {
    // Display error message if the table creation fails
    $message = "<div class='alert alert-danger'>Table creation failed: " . $e->getMessage() . "</div>";
}
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the form data and sanitize it
    $username = trim($_POST['name']);
    $contact = trim($_POST['contact_number']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $exDate = $_POST['exDate'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL query to insert the data into the user_table
    try {
        $stmt = $conn->prepare("INSERT INTO user_table (username, email, contact, password,role,expiryDate) VALUES (:username, :email, :contact, :password,:role, :exDate)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role); // Set the role as 'user' by default
        $stmt->bindParam(':exDate',$exDate);
        // Execute the query
        $stmt->execute();

        // Set success message in session
        $_SESSION['message'] = "User added successfully!";
        header("Location: admin_add_user.php");
        exit;
    } catch (PDOException $e) {
        // If there is an error, set the error message in session
        $_SESSION['message'] = "Error: " . $e->getMessage();
        header("Location: admin_add_user.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>

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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    <div class="container my-4">
        <div class="header text-center mb-4">
            <h1>Add New User</h1>
        </div>

        <!-- Display Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info text-center">
                <?= $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form action="" method="post">
            <div class="row g-3">
                
                <!-- Name -->
                <div class="col-md-6">
                    <label for="name" class="form-label">UserName:</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <!-- Contact Number -->
                <div class="col-md-6">
                    <label for="contact_number" class="form-label">Contact Number:</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" 
                        pattern="\d{10}" maxlength="10" required 
                        title="Contact number must be 10 digits.">
                    <div class="invalid-feedback">
                        Please enter a valid 10-digit contact number.
                    </div>
                </div>
                
                <!-- Password -->
                <div class="col-md-6">
                    <label for="password" class="form-label">Password:</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <!-- Role -->
                <div class="col-md-6">
                    <label for="role" class="form-label">Role:</label>
                    <input type="text" id="role" name="role" class="form-control" value="user" readonly>
                </div>

                <!-- Expiry Date -->
                <div class="col-md-6">
                    <label for="expiryDate" class="form-label">Expiry Date:</label>
                    <input type="date" id="exDate" name="exDate" class="form-control" required>
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-success">Add User</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const contactField = document.getElementById('contact_number');

        contactField.addEventListener('input', function () {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');

            // Check if the value is 10 digits long
            if (this.value.length === 10) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });

        // Additional check on form submission
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            if (contactField.value.length !== 10) {
                contactField.classList.add('is-invalid');
                e.preventDefault(); // Prevent form submission if invalid
            }
        });
    </script>


    <script>
    // Set expiry date to one year from the current date
    const exDateField = document.getElementById("exDate");
    const today = new Date();
    const nextYear = new Date();

    nextYear.setFullYear(today.getFullYear() + 1); // Add one year to the current date

    const year = nextYear.getFullYear();
    const month = (nextYear.getMonth() + 1).toString().padStart(2, '0'); // Months are zero-based
    const day = nextYear.getDate().toString().padStart(2, '0');

    exDateField.value = `${year}-${month}-${day}`; // Set the date in YYYY-MM-DD format
    </script>

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
    

    <!-- Footer -->
    <footer class="footer text-center py-3">
        <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
