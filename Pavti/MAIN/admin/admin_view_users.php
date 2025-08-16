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
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;  /* Ensure the body covers full height of the screen */
            background-color: #f8f9fa;  /* Light background color */
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
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

        .footer {
            background-color: #ea6d06;
            color: white;
            padding: 15px 0;
            width: 100%;
            text-align: center;
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
            <h1>View All Users</h1>
        </div>

        <!-- Table to display all users -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr align="center">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Expiry Date</th>
                        <th colspan="2">Edit OR Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all users from the user_table
                    $query = "SELECT id, username, contact, email, role, expiryDate FROM user_table";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();

                    // Check if any records were found
                    if ($stmt->rowCount() > 0) {
                        // Fetch and display each record
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr align='center'>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['expiryDate']) . "</td>"; ?>
                            <td>
                                <button class="btn btn-danger" onclick="openDeleteModal(<?php echo $row['id']; ?>)">DELETE</button>
                            </td>
                            <td>
                                <a class='btn btn-warning' href='update_user.php?upd=<?php echo $row['id']; ?>'>EDIT</a>
                            </td>
                            <?php echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center py-3">
        <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.</p>
    </footer>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user?
                </div>
                <div class="modal-footer">
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> 
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let deleteUrl = "";

        function openDeleteModal(userId) {
            // Set the URL for the delete button
            deleteUrl = `delete_user.php?delete=${userId}`;
            document.getElementById('confirmDeleteBtn').href = deleteUrl;

            // Show the modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>
