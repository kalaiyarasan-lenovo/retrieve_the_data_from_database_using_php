<?php
require 'vendor/autoload.php'; // Include PHPSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mcq_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS mcqs (
    Q_No INT PRIMARY KEY AUTO_INCREMENT,
    Question_En TEXT,
    Statement1_En TEXT,
    Statement2_En TEXT,
    Items_En TEXT,
    Option_A_En TEXT,
    Option_B_En TEXT,
    Option_C_En TEXT,
    Option_D_En TEXT,
    Question_Ta TEXT,
    Statement1_Ta TEXT,
    Statement2_Ta TEXT,
    Items_Ta TEXT,
    Option_A_Ta TEXT,
    Option_B_Ta TEXT,
    Option_C_Ta TEXT,
    Option_D_Ta TEXT,
    Answer_Key VARCHAR(255),
    Options_En_JSON TEXT,
    Options_Ta_JSON TEXT,
    Correct_Answer_Text_En TEXT
)";
if ($conn->query($sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

// Function to escape strings (optional if using prepared statements)
function escape($conn, $str) {
    return mysqli_real_escape_string($conn, $str ?? '');
}

// Handle file upload
if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];
    $fileName = $_FILES['excel_file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['xlsx', 'xls'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        die("Error: Please upload a valid Excel file (xlsx or xls).<br>");
    }

    try {
        $spreadsheet = IOFactory::load($fileTmpPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Prepare SQL statement
        $stmt = $conn->prepare("
            INSERT IGNORE INTO mcqs (
                Question_En, Statement1_En, Statement2_En, Items_En,
                Option_A_En, Option_B_En, Option_C_En, Option_D_En,
                Question_Ta, Statement1_Ta, Statement2_Ta, Items_Ta,
                Option_A_Ta, Option_B_Ta, Option_C_Ta, Option_D_Ta,
                Answer_Key, Options_En_JSON, Options_Ta_JSON, Correct_Answer_Text_En
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $isFirstRow = true;
        foreach ($rows as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue; // Skip header row
            }

            // Map Excel row to database columns
            $stmt->bind_param(
                "ssssssssssssssssssss",
                $row[1], $row[2], $row[3], $row[4],
                $row[5], $row[6], $row[7], $row[8],
                $row[9], $row[10], $row[11], $row[12],
                $row[13], $row[14], $row[15], $row[16],
                $row[17], $row[18], $row[19], $row[20]
            );

            if ($stmt->execute()) {
                echo "Record inserted successfully<br>";
            } else {
                echo "Error inserting record: " . $stmt->error . "<br>";
            }
        }

        echo "Data import completed successfully!<br>";

    } catch (Exception $e) {
        echo "Error loading Excel file: " . $e->getMessage() . "<br>";
    }

} else {
    echo "Error: No file uploaded or upload failed.<br>";
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error']) {
        echo "Upload error code: " . $_FILES['excel_file']['error'] . "<br>";
    }
    echo "Please choose a file and try again.<br>";
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Excel File</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #a1c4fd, #c2e9fb);
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .upload-container {
            text-align: center;
        }
        input[type="file"] {
            padding: 10px;
            border: 2px dashed #d3d3d3;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <form action="excel_upload.php" method="post" enctype="multipart/form-data">
            <label for="excel_file">Choose Excel file</label><br>
            <input type="file" name="excel_file" id="excel_file"><br>
            <input type="submit" value="Upload">
        </form>
    </div>
</body>
</html>
