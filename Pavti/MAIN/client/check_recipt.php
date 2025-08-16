<?php
// Start session to track user login status
session_start();

// Check if the user is logged in and their role is 'user', otherwise redirect to the login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");  // Redirect to the login page
    exit;  // Stop script execution after the redirect
}
else{
    $usernm = $_SESSION['username'];
    $userid=$_SESSION['userid'];
}

// Include the Database class to handle database connections
include 'database.php';

// Create an instance of the Database class
$database = new Database();
// Get the database connection
$conn = $database->getConnection();

// Initialize a variable to hold receipt data (null by default)
$receiptData = null;

// Check if the form is submitted with a receipt number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_recipt']) && !empty($_POST['receipt_number'])) {
    $receiptNumber = $_POST['receipt_number'];  // Get the receipt number from the form

    // Prepare the SQL query to fetch receipt data based on the provided receipt number
    $sql = "SELECT * FROM client_recipt_data WHERE recipt_no = :receipt_number AND user_id = $userid";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':receipt_number', $receiptNumber, PDO::PARAM_STR);  // Bind the receipt number to the SQL query

    // Execute the query and fetch the data if available
    if ($stmt->execute()) {
        $receiptData = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch the first result as an associative array
    } else {
        // If the query fails, show an error message
        echo '<div class="alert alert-danger mt-4">Error fetching receipt details. Please try again later.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Details</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        /* Styling for the heading */
        h1 {
            margin-top: 20px;
        }

        /* Styling for the receipt details container */
        #receipt-details {
            display: block;
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }

        /* Styling for the video element */
        video {
            width: 100%;
            max-width: 600px;
            margin-top: 10px;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }

        /* Add underline effect on hover for footer links */
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg" style="background-color: #ea6d06;">
        <div class="container-fluid">
            <!-- Brand Name -->
            <a class="navbar-brand" href="#" style="color: white;">Tanishi Infotech</a>
            
            <!-- Navbar Toggler for small screens -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="client_dashboard.php" style="color: white;">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="put_client_form.php" style="color: white;">Add Receipt</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="client_form.php" style="color: white;">Lost Receipt</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="check_recipt.php" style="color: white;">Search</a>
                    </li>
                </ul>
                <!-- Logout Button and Welcome msg -->
                 
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="navbar-brand" href="#" style="color: white;">Welcome  <?php echo " ". $usernm; ?></a>
                        <button class="btn btn-danger" onclick="logoutUser()">Logout</button>
                    </li>
                    <script>
                        // Logout function to redirect to logout.php
                        function logoutUser() {
                            window.location.href = 'logout.php';
                        }
                    </script>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Main Content -->
    <div class="container">
        <!-- Heading for the receipt details page -->
        <h1 class="text-center">Check Receipt Details</h1>

        <!-- Form for submitting receipt number -->
        <form id="search-form" class="d-flex flex-column align-items-center mt-4" method="POST">
            <div class="mb-3 w-100" style="max-width: 400px;">
                <label for="receipt-number" class="form-label">Enter Receipt Number:</label>
                <input type="text" id="receipt-number" name="receipt_number" class="form-control" value="<?= htmlspecialchars($_POST['receipt_number'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary" name="check_recipt">Search</button>
        </form>

        <!-- If receipt data is available, display the details -->
        <?php if ($receiptData): ?>
        <div id="receipt-details" class="mt-4 p-4 bg-light rounded">
            <h2>Receipt Details</h2>
            <p><strong>Name of Taker:</strong> <?= htmlspecialchars($receiptData['taker_name']) ?></p>
            <p><strong>Aadhar of Taker:</strong> <?= htmlspecialchars($receiptData['taker_aadhar']) ?></p>
            <p><strong>Number of Taker:</strong> <?= htmlspecialchars($receiptData['taker_number']) ?></p>
            <p><strong>Address of Taker:</strong> <?= htmlspecialchars($receiptData['taker_address']) ?></p>

            <p><strong>Name of Profer:</strong> <?= htmlspecialchars($receiptData['profer_name']) ?></p>
            <p><strong>Aadhar of Profer:</strong> <?= htmlspecialchars($receiptData['profer_aadhar']) ?></p>
            <p><strong>Number of Profer:</strong> <?= htmlspecialchars($receiptData['profer_number']) ?></p>
            <p><strong>Address of Profer:</strong> <?= htmlspecialchars($receiptData['profer_address']) ?></p>

            <p><strong>Year of Keep:</strong> <?= htmlspecialchars($receiptData['year_keep']) ?></p>
            <p><strong>Year of Take:</strong> <?= htmlspecialchars($receiptData['year_take']) ?></p>
            <p><strong>Day-Date-Time Returned:</strong> <?= htmlspecialchars($receiptData['date']) . ' ' . htmlspecialchars($receiptData['time']) ?></p>

            <p><strong>Video Proof:</strong></p>
            <?php if (!empty($receiptData['video_path'])): ?>
                <!-- Display video if a video path exists -->
                <video controls>
                    <source src="<?= htmlspecialchars($receiptData['video_path']) ?>" type="video/webm">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <p>No video proof available.</p>
            <?php endif; ?>
        </div>
        <!-- If no receipt data is found, display an error message -->
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-danger mt-4">No receipt found with the provided number.</div>
        <?php endif; ?>
    </div>

    <br><br>
    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.
                <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a> | <a href="#">Contact Us</a>
            </p>
        </div>
    </footer>

    <!-- Include Bootstrap Bundle with Popper for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
