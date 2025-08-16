<?php
session_start();

// Check if the user is logged in as admin, redirect to login page if not
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Include the database class and establish a connection
include 'database.php';
$database = new Database();
$conn = $database->getConnection();

$error = ""; // Variable to store any error messages
$userId = null;

// Check if the 'upd' parameter is provided in the URL and fetch the user details
if (isset($_GET['upd']) && !empty($_GET['upd'])) {
    $userId = intval($_GET['upd']); // Sanitize the user ID to prevent SQL injection

    // Prepare and execute the query to fetch user details
    $query = "SELECT * FROM user_table WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // If user data is found, store it in the $user variable
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "User not found."; // Error if the user ID is invalid
    }
} else {
    $error = "Invalid user ID."; // Error if no user ID is provided
}

// Handle the form submission to update user details
if ($_SERVER["REQUEST_METHOD"] === "POST" && $userId) {
    // Sanitize and retrieve user inputs
    $username = htmlspecialchars($_POST['username']);
    $contact = htmlspecialchars($_POST['contact_number']);
    $email = htmlspecialchars($_POST['email']);
    $role = htmlspecialchars($_POST['role']);
    $password = $_POST['password']; // Password should remain raw for hashing
    $exDate = htmlspecialchars($_POST['exDate']);

    // Build the update query
    $query = "UPDATE user_table SET username = :username, contact = :contact, email = :email, role = :role, expiryDate = :exDate";
    if (!empty($password)) {
        $query .= ", password = :password"; // Include password update if provided
    }
    $query .= " WHERE id = :id";

    // Prepare and bind parameters to the query
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':exDate',$exDate);

    // Hash the password and bind it if provided
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashedPassword);
    }

    // Execute the query and handle success or failure
    if ($stmt->execute()) {
        header("Location: admin_view_users.php?success=1");
        exit;
    } else {
        $error = "Failed to update user."; // Error if the query fails
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>

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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #ea6d06;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" style="color: white;">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Navigation Links -->
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php" style="color: white;">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_view_users.php" style="color: white;">View Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_add_user.php" style="color: white;">Add User</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_change_password.php" style="color: white;">Change Password</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <!-- Logout Button -->
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

    <!-- Main Container -->
    <div class="container my-4">
        <h1 class="text-center mb-4">Update User</h1>

        <!-- Display error if any -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <a href="admin_view_users.php" class="btn btn-secondary">Go Back</a>
        <?php else: ?>
            <!-- Update User Form -->
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Name</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <!-- Contact Number -->
                <div class="md-3">
                    <label for="contact_number" class="form-label">Contact Number:</label>
                    <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact']); ?>" class="form-control" pattern="\d{10}" maxlength="10" required title="Contact number must be 10 digits.">
                    <div class="invalid-feedback">
                        Please enter a valid 10-digit contact number.
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <input type="text" class="form-control" id="role" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password (leave blank to keep current password)</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div> 
                </div>
                <div class="mb-3">
                    <label for="expiryDate" class="form-label">Expiry Date</label>
                    <input type="date" class="form-control" id="exDate" name="exDate" value="<?php echo htmlspecialchars($user['expiryDate']); ?>">
                </div>

                <!-- Submit and Cancel Buttons -->
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="admin_view_users.php" class="btn btn-outline-secondary">Cancel</a>
            </form>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer text-center py-3">
        <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.</p>
    </footer>

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

    <!-- JavaScript for Password Visibility Toggle -->
    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("bi-eye");
                toggleIcon.classList.add("bi-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("bi-eye-slash");
                toggleIcon.classList.add("bi-eye");
            }
        });
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
