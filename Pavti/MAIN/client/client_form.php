<?php

// Start the session to track user login status
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



// Include the Database class
include 'database.php';

// Create an instance of the Database class
$database = new Database();
$conn = $database->getConnection(); // Get the database connection

// Ensure the user_table is created before any queries
try {
    $sql = "CREATE TABLE IF NOT EXISTS `client_recipt_data` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `recipt_no` VARCHAR(255) NOT NULL,
        `taker_name` VARCHAR(255) NOT NULL,
        `taker_aadhar` VARCHAR(12) NOT NULL,
        `taker_number` VARCHAR(15) NOT NULL,
        `taker_address` TEXT NOT NULL,
        `profer_name` VARCHAR(255) NOT NULL,
        `profer_aadhar` VARCHAR(12) NOT NULL,
        `profer_number` VARCHAR(15) NOT NULL,
        `profer_address` TEXT NOT NULL,
        `year_keep` INT NOT NULL,
        `year_take` INT NOT NULL,
        `date` DATE NOT NULL,
        `time` TIME NOT NULL,
        `video_path` VARCHAR(255) NOT NULL,
        `user_id` INT NOT NULL, -- Foreign key column
        CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `user_table`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
    )";

    
    $conn->exec($sql);
} catch (PDOException $e) {
    $message = "<div class='alert alert-danger'>Table creation failed: " . $e->getMessage() . "</div>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipt_no = $_POST['recipt_no'];
    $taker_name = $_POST['taker_name'];
    $taker_aadhar = $_POST['taker_aadhar'];
    $taker_number = $_POST['taker_number'];
    $taker_address = $_POST['taker_address'];
    $profer_name = $_POST['profer_name'];
    $profer_aadhar = $_POST['profer_aadhar'];
    $profer_number = $_POST['profer_number'];
    $profer_address = $_POST['profer_address'];
    $year_keep = $_POST['year_keep'];
    $year_take = $_POST['year_take'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    
    // Handle video upload
    if (!empty($_POST['videoData'])) {
        $videoData = $_POST['videoData'];
        $videoData = str_replace('data:video/webm;base64,', '', $videoData);
        $videoData = base64_decode($videoData);
        
        // Save the video file
        $videoFileName = 'client_video_record/'.$usernm.'/video_' . $recipt_no . '.webm';
        file_put_contents($videoFileName, $videoData);
    } else {
        $videoFileName = null;
    }

    // Prepare a SELECT statement to check for existing records with the same receipt number
    $sql = "SELECT COUNT(*) FROM client_recipt_data WHERE recipt_no = :recipt_no AND user_id = $userid";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':recipt_no', $recipt_no);
    $stmt->execute();

    // Fetch the count of existing records to check if the receipt number already exists
    $count = $stmt->fetchColumn();

    // Check if the record already exists
    if ($count > 0) {
        $message = "<div class='alert alert-danger'>This Receipt is already Stored.</div>";
    } else {
        try {
            // Prepare an INSERT statement to insert data into the database
            $query = "INSERT INTO client_recipt_data 
                    (recipt_no, taker_name, taker_aadhar, taker_number, taker_address, 
                    profer_name, profer_aadhar, profer_number, profer_address, 
                    year_keep, year_take, date, time, video_path,user_id)
                    VALUES (:recipt_no, :taker_name, :taker_aadhar, :taker_number, :taker_address, 
                            :profer_name, :profer_aadhar, :profer_number, :profer_address, 
                            :year_keep, :year_take, :date, :time, :video_path, :userid)";
            
            $stmt = $conn->prepare($query);

            // Bind parameters to the SQL query
            $stmt->bindParam(':recipt_no', $recipt_no);
            $stmt->bindParam(':taker_name', $taker_name);
            $stmt->bindParam(':taker_aadhar', $taker_aadhar);
            $stmt->bindParam(':taker_number', $taker_number);
            $stmt->bindParam(':taker_address', $taker_address);
            $stmt->bindParam(':profer_name', $profer_name);
            $stmt->bindParam(':profer_aadhar', $profer_aadhar);
            $stmt->bindParam(':profer_number', $profer_number);
            $stmt->bindParam(':profer_address', $profer_address);
            $stmt->bindParam(':year_keep', $year_keep);
            $stmt->bindParam(':year_take', $year_take);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':time', $time);
            $stmt->bindParam(':video_path', $videoFileName);
            $stmt->bindParam(':userid', $userid);

            // Execute the query and check for success
            if ($stmt->execute()) {
            // Success message
                $message = "<div class='alert alert-success'>Data and video uploaded successfully!</div>";  
            } else {
                $message = "<div class='alert alert-danger'>Error inserting data. </div>";
            }
        } catch (PDOException $e) {
            // Error message in case of exception
            $message = "<div class='alert alert-danger'>Error inserting data: " . $e->getMessage() . "</div>";
        }
    }

    // Output the message (either success or error)
    if (isset($message)) {
    // echo "<div class='alert-container'>" . $message . "</div>";
    }
}
    // Close the database connection
    $database->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Styling for video container */
        .video-container {
            position: relative;
            display: inline-block;
            width: 100%;
            max-width: 480px; /* Set max width for desktop */
        }
        #real-time-video {
            width: 100%;
            height: auto;
            border-radius: 10px;
            border: 2px solid #333;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
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

        .footer a {
            color: white;
            text-decoration: none;
        }



        .footer a:hover {
            text-decoration: underline;
        }

        /* Fieldset for form sections */
        fieldset {
            border: 5px solid #333 !important; /* Dark border */
            border-radius: 10px; /* Rounded corners */
            padding: 20px; /* Increase padding */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Add a subtle shadow */
        }

        legend {
            font-weight: bold;
            font-size: 1.2rem;
            color: #333; /* Darker text color */
            padding: 0 10px; /* Add some padding */
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

    <div class="container my-4">
        <div class="header text-center mb-4">
            <h1>Enter Details</h1>
        </div>

        <!-- Form for data entry -->
        <form action="" method="post" enctype="multipart/form-data" class="row g-3">
        
            <!-- Receipt Number Input -->
            <div class="col-md-12">
                <label for="recipt_no" class="form-label">Enter Receipt Number:</label>
                <input type="text" id="recipt_no" name="recipt_no" class="form-control">
            </div>

            <!-- Display the message under the receipt number -->
            <?php if (isset($message)) { ?>
                    <div class="col-12">
                        <div class="alert-container">
                            <?= $message; ?>
                        </div>
                    </div>
                <?php } ?>


            <div>
                <!-- Taker's Information -->
                <fieldset class="border rounded-5 p-3">
                    <legend class="float-none w-auto px-3">Taker's Information:</legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="taker-name" class="form-label">Name of Taker:</label>
                            <input type="text" id="taker-name" name="taker_name" placeholder="Enter name of taker" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="taker-aadhar" class="form-label">Aadhar of Taker:</label>
                            <input type="text" id="taker-aadhar" name="taker_aadhar" placeholder="Enter Aadhar number" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="taker-number" class="form-label">Number of Taker:</label>
                            <input type="tel" id="taker-number" name="taker_number" placeholder="Enter phone number" class="form-control">
                        </div>

                        <div class="col-12">
                            <label for="taker-address" class="form-label">Address of Taker:</label>
                            <textarea id="taker-address" name="taker_address" placeholder="Enter address" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                </fieldset>

                <!-- Profer's Information -->
                <fieldset class="border rounded-5 p-3">
                    <legend class="float-none w-auto px-3">Profer's Information:</legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="profer-name" class="form-label">Name of Profer:</label>
                            <input type="text" id="profer-name" name="profer_name" placeholder="Enter name of profer" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="profer-aadhar" class="form-label">Aadhar of Profer:</label>
                            <input type="text" id="profer-aadhar" name="profer_aadhar" placeholder="Enter Aadhar number" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="profer-number" class="form-label">Number of Profer:</label>
                            <input type="tel" id="profer-number" name="profer_number" placeholder="Enter phone number" class="form-control">
                        </div>

                        <div class="col-12">
                            <label for="profer-address" class="form-label">Address of Profer:</label>
                            <textarea id="profer-address" name="profer_address" placeholder="Enter address" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                </fieldset>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        document.querySelector('form').addEventListener('submit', function (event) {
                            // Get Aadhar inputs
                            let takerAadhar = document.getElementById('taker-aadhar').value.trim();
                            let proferAadhar = document.getElementById('profer-aadhar').value.trim();

                            // Regular expression to match a 12-digit numeric Aadhar number
                            let aadharPattern = /^\d{12}$/;

                            if (!aadharPattern.test(takerAadhar)) {
                                alert('Invalid Taker Aadhar Number. Please enter a 12-digit numeric value.');
                                event.preventDefault(); // Prevent form submission
                                return;
                            }

                            if (!aadharPattern.test(proferAadhar)) {
                                alert('Invalid Profer Aadhar Number. Please enter a 12-digit numeric value.');
                                event.preventDefault(); // Prevent form submission
                                return;
                            }
                        });
                    });
                </script>


                <!-- Additional Information -->
                <fieldset class="border rounded-5 p-3">
                    <legend class="float-none w-auto px-3">Additional Information:</legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="year-keep" class="form-label">Year of Keep:</label>
                            <input type="number" id="year-keep" name="year_keep" placeholder="Enter year of keep" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="year-take" class="form-label">Year of Take:</label>
                            <input type="number" id="year-take" name="year_take" placeholder="Enter year of take" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="date" class="form-label">Date:</label>
                            <input type="date" id="date" name="date" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="time" class="form-label">Time:</label>
                            <input type="time" id="time" name="time" class="form-control">
                        </div>

                        <script>
                            // Function to set the current date and time
                            function setCurrentDateTime() {
                                // Get the current date and time
                                const currentDate = new Date();

                                // Format the date to yyyy-mm-dd (required for the date input field)
                                const formattedDate = currentDate.toISOString().split('T')[0];

                                // Format the time to hh:mm (required for the time input field)
                                const formattedTime = currentDate.toTimeString().slice(0, 5);

                                // Set the current date and time into the input fields
                                document.getElementById('date').value = formattedDate;
                                document.getElementById('time').value = formattedTime;
                            }

                            // Call the function when the page loads
                            window.onload = function() {
                                setCurrentDateTime();
                            }
                        </script>
                    </div>
                </fieldset>

                <!-- Camera Section -->
                <div class="col-12 text-center my-3">
                    <p>Ensure your camera is enabled for real-time video feed.</p>
                    <div style="position: relative; display: inline-block;">

                        <form action="" method="post" enctype="multipart/form-data" class="row g-3">
                            <div class="col-12">
                                <video id="real-time-video" autoplay playsinline class="border"></video>
                                <br><br>
                                <button id="start-recording" class="btn btn-danger">
                                    <i class="fas fa-circle"></i> Start Recording
                                </button>
                                <button id="stop-recording" class="btn btn-secondary" disabled>
                                    <i class="fas fa-square"></i> Stop Recording
                                </button>
                                <button id="switch-camera" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Switch Camera
                                </button>
                                <input type="hidden" id="videoData" name="videoData">
                            </div>
                            <br><br>
                            <script>
                                let mediaRecorder;
                                let recordedChunks = [];
                                let currentStream;
                                let useFrontCamera = true; // Toggle to switch between front and back cameras

                                const startBtn = document.getElementById('start-recording');
                                const stopBtn = document.getElementById('stop-recording');
                                const switchBtn = document.getElementById('switch-camera');
                                const videoPreview = document.getElementById('real-time-video');
                                const videoInput = document.getElementById('videoData');

                                // Function to get the user's webcam stream
                                async function getCameraStream() {
                                    // Stop any existing stream
                                    if (currentStream) {
                                        currentStream.getTracks().forEach(track => track.stop());
                                    }

                                    // Set constraints for the camera
                                    const constraints = {
                                        video: {
                                            facingMode: useFrontCamera ? 'user' : 'environment' // Switch between front and back cameras
                                        },
                                        audio: false
                                    };

                                    try {
                                        const stream = await navigator.mediaDevices.getUserMedia(constraints);
                                        currentStream = stream;
                                        videoPreview.srcObject = stream;

                                        // Set up MediaRecorder
                                        mediaRecorder = new MediaRecorder(stream);

                                        mediaRecorder.ondataavailable = (event) => {
                                            if (event.data.size > 0) {
                                                recordedChunks.push(event.data);
                                            }
                                        };

                                        mediaRecorder.onstop = () => {
                                            const blob = new Blob(recordedChunks, { type: 'video/webm' });
                                            recordedChunks = [];

                                            // Convert the video blob to a base64 string
                                            const reader = new FileReader();
                                            reader.readAsDataURL(blob);
                                            reader.onloadend = () => {
                                                videoInput.value = reader.result; // Store video as base64 in the form input
                                            };
                                        };
                                    } catch (error) {
                                        console.error('Error accessing webcam:', error);
                                    }
                                }

                                // Initialize the camera with the front camera by default
                                getCameraStream();

                                startBtn.onclick = () => {
                                    recordedChunks = [];
                                    mediaRecorder.start();
                                    startBtn.disabled = true;
                                    stopBtn.disabled = false;
                                };

                                stopBtn.onclick = () => {
                                    mediaRecorder.stop();
                                    startBtn.disabled = false;
                                    stopBtn.disabled = true;
                                };

                                switchBtn.onclick = (event) => {
                                    event.preventDefault();
                                    useFrontCamera = !useFrontCamera; // Toggle the camera mode
                                    getCameraStream(); // Reinitialize the stream with the new camera
                                };
                            </script>
                        </form>                
                    </div>
                </div>
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p>&copy; 2025 Tanishi Infotech. All Rights Reserved.
                <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a> | <a href="#">Contact Us</a>
            </p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
