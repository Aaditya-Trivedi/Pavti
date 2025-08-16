<?php

// Start the session to track user login status
session_start();

// Check if the user is logged in and their role is 'user', otherwise redirect to the login page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");  // Redirect to the login page
    exit;  // Stop script execution after the redirect
} else {
    $usernm = $_SESSION['username'];
    $userid = $_SESSION['userid'];
}

require "database.php";

$db = new Database();
$conn = $db->getConnection();

// Create table if not exists
$table_query = "CREATE TABLE IF NOT EXISTS new_client_receipt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    party_name VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    contact_number VARCHAR(10) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    amount1 DECIMAL(10,2) NOT NULL,
    date1 DATE NOT NULL,
    amount2 DECIMAL(10,2),
    date2 DATE,
    amount3 DECIMAL(10,2),
    date3 DATE,
    amount4 DECIMAL(10,2),
    date4 DATE,
    amount5 DECIMAL(10,2),
    date5 DATE,
    amount6 DECIMAL(10,2),
    date6 DATE,
    amount7 DECIMAL(10,2),
    date7 DATE,
    amount8 DECIMAL(10,2),
    date8 DATE,
    amount9 DECIMAL(10,2),
    date9 DATE,
    amount10 DECIMAL(10,2),
    date10 DATE,
    video VARCHAR(255) NOT NULL
)";
$conn->exec($table_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $party_name = $_POST['partyName'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $item_name = $_POST['itemName'];
    $user_id = $_SESSION['userid'];
    
    // Handling file upload
    $videoFileName = '';

    if (!empty($_FILES['video']['name'])) {
        // Allowed video formats
        $allowedFormats = ['video/mp4', 'video/webm','video/mov', 'video/mkv'];
        
        // Get the uploaded file's MIME type
        $fileType = mime_content_type($_FILES['video']['tmp_name']);

        // Check if the uploaded file is a valid video format
        if (in_array($fileType, $allowedFormats)) {
            // Ensure the directory exists
            $uploadDir = 'put_video_record/' . $usernm . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique video file name (optional use of a receipt number or timestamp)
            $videoFileName = $uploadDir . 'video_' . time() . '.' . pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
            
            // Save the uploaded video file
            if (move_uploaded_file($_FILES['video']['tmp_name'], $videoFileName)) {
                // Successfully uploaded video, set video path
            } else {
                $_SESSION['message'] = "Failed to upload video.";
                header("Location: put_client_form.php");
                exit;
            }
        } else {
            // Invalid file type
            $_SESSION['message'] = "Only video files (MP4, WebM, MOV, MKV) are allowed!";
            header("Location: put_client_form.php"); // Redirect back to form
            exit;
        }
    } else {
        // No video uploaded
        $videoFileName = null;
    }


    try {
        // Insert data into the database
        $stmt = $conn->prepare("INSERT INTO party_details 
        (user_id, party_name, city, address, contact_number, item_name, 
        amount1, date1, amount2, date2, amount3, date3, amount4, date4, 
        amount5, date5, amount6, date6, amount7, date7, amount8, date8, 
        amount9, date9, amount10, date10, video) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Binding parameters one by one
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $party_name, PDO::PARAM_STR);
        $stmt->bindParam(3, $city, PDO::PARAM_STR);
        $stmt->bindParam(4, $address, PDO::PARAM_STR);
        $stmt->bindParam(5, $contact_number, PDO::PARAM_STR);
        $stmt->bindParam(6, $item_name, PDO::PARAM_STR);

        $stmt->bindParam(7, $_POST['amount1'], PDO::PARAM_STR);
        $stmt->bindParam(8, $_POST['date1'], PDO::PARAM_STR);
        $stmt->bindParam(9, $_POST['amount2'], PDO::PARAM_STR);
        $stmt->bindParam(10, $_POST['date2'], PDO::PARAM_STR);
        $stmt->bindParam(11, $_POST['amount3'], PDO::PARAM_STR);
        $stmt->bindParam(12, $_POST['date3'], PDO::PARAM_STR);
        $stmt->bindParam(13, $_POST['amount4'], PDO::PARAM_STR);
        $stmt->bindParam(14, $_POST['date4'], PDO::PARAM_STR);
        $stmt->bindParam(15, $_POST['amount5'], PDO::PARAM_STR);
        $stmt->bindParam(16, $_POST['date5'], PDO::PARAM_STR);
        $stmt->bindParam(17, $_POST['amount6'], PDO::PARAM_STR);
        $stmt->bindParam(18, $_POST['date6'], PDO::PARAM_STR);
        $stmt->bindParam(19, $_POST['amount7'], PDO::PARAM_STR);
        $stmt->bindParam(20, $_POST['date7'], PDO::PARAM_STR);
        $stmt->bindParam(21, $_POST['amount8'], PDO::PARAM_STR);
        $stmt->bindParam(22, $_POST['date8'], PDO::PARAM_STR);
        $stmt->bindParam(23, $_POST['amount9'], PDO::PARAM_STR);
        $stmt->bindParam(24, $_POST['date9'], PDO::PARAM_STR);
        $stmt->bindParam(25, $_POST['amount10'], PDO::PARAM_STR);
        $stmt->bindParam(26, $_POST['date10'], PDO::PARAM_STR);

        $stmt->bindParam(27, $videoFileName, PDO::PARAM_STR);  // Video file path

        // Execute the query and check for success
        if ($stmt->execute()) {
            // Success message
            $_SESSION['message'] =  "Data uploaded successfully...!";  
        } else {
           $_SESSION['message'] = "Error inserting data...!";
        }
    } catch (PDOException $e) {
        // Error message in case of exception
        $_SESSION['message'] = "Error inserting data: " . $e->getMessage() . "";
    }
        
    $_SESSION['message'] = "Data saved successfully!";
    header("Location: put_client_form.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Details Form</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .content {
            flex-grow: 1;
        }

        .footer {
            background-color: #ea6d06;
            color: white;
            padding: 15px 0;
            text-align: center;
            width: 100%;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .nav-link.active {
            font-weight: bold;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .btn-danger {
            margin-left: 10px;
        }

        /* Alert message styling */
        #message-container {
            margin-top: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .invalid-feedback {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg" style="background-color: #ea6d06;">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">Tanishi Infotech</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="navbar-brand text-white">Welcome <?php echo " ".$usernm; ?></a>
                        <button class="btn btn-danger" onclick="logoutUser()">Logout</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="text-center mb-4">
            <h1>Party Details</h1>
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

                <!-- Party Information -->
                <div class="col-md-6">
                    <label for="partyName" class="form-label">Party Name:</label>
                    <input type="text" id="partyName" name="partyName" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="city" class="form-label">City:</label>
                    <input type="text" id="city" name="city" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="address" class="form-label">Address:</label>
                    <input type="text" id="address" name="address" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="contact_number" class="form-label">Contact Number:</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" pattern="\d{10}" maxlength="10" required title="Contact number must be 10 digits.">
                    <div class="invalid-feedback">Please enter a valid 10-digit contact number.</div>
                </div>
                <div class="col-md-12">
                    <label for="itemName" class="form-label">Item Name:</label>
                    <input type="text" id="itemName" name="itemName" class="form-control" required>
                </div>

                <!-- Amount & Date Fields -->
                <div class="col-md-6">
                    <label for="amount1" class="form-label">Amount 1:</label>
                    <input type="number" id="amount1" name="amount1" class="form-control" min="1" require>
                </div>
                <div class="col-md-6">
                    <label for="date1" class="form-label">Date 1:</label>
                    <input type="date" id="date1" name="date1" class="form-control" require>
                </div>

                <!-- Repeat above amount and date fields for amounts 2 to 10 -->
                <?php for ($i = 2; $i <= 10; $i++): ?>
                    <div class="col-md-6">
                        <label for="amount<?php echo $i; ?>" class="form-label">Amount <?php echo $i; ?>:</label>
                        <input type="number" id="amount<?php echo $i; ?>" name="amount<?php echo $i; ?>" class="form-control" min="1">
                    </div>
                    <div class="col-md-6">
                        <label for="date<?php echo $i; ?>" class="form-label">Date <?php echo $i; ?>:</label>
                        <input type="date" id="date<?php echo $i; ?>" name="date<?php echo $i; ?>" class="form-control">
                    </div>
                <?php endfor; ?>

                <!-- Upload Video -->
                <div class="col-md-12">
                    <label for="video" class="form-label">Upload Video of Item:</label>
                    <input type="file" id="video" name="video" class="form-control" accept="video/*">
                </div>
                
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function logoutUser() {
            window.location.href = 'logout.php';
        }

        const contactField = document.getElementById('contact_number');
        contactField.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 10) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    </script>

    <!-- Footer -->
    <footer class="footer text-center py-3">
        <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
