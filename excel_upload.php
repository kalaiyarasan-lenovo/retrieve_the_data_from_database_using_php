<?php
require 'vendor/autoload.php'; // Include PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection settings
$servername = "localhost"; // Change to your server name
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "mcq_db"; // Change to your database name

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($dbname);

// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS mcqs (
    Q_No INT PRIMARY KEY,
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

if ($conn->query($sql) === TRUE) {
    echo "Table created successfully or already exists<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Function to escape strings
function escape($conn, $str) {
    return mysqli_real_escape_string($conn, $str ?? '');
}

// Handle file upload
if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['excel_file']['tmp_name'];
    $fileName = $_FILES['excel_file']['name'];
    $fileSize = $_FILES['excel_file']['size'];
    $fileType = $_FILES['excel_file']['type'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Check if the file is an Excel file
    $allowedExtensions = ['xlsx', 'xls'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        die("Error: Please upload a valid Excel file (xlsx or xls).<br>");
    }

    try {
        // Load the Excel file
        $spreadsheet = IOFactory::load($fileTmpPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip the header row (row 1)
        $isFirstRow = true;
        foreach ($rows as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue; // Skip header row
            }

            // Map Excel row to database columns
            $data = [
                'Q_No' => (int)($row[0] ?? 0),
                'Question_En' => escape($conn, $row[1] ?? ''),
                'Statement1_En' => escape($conn, $row[2] ?? ''),
                'Statement2_En' => escape($conn, $row[3] ?? ''),
                'Items_En' => escape($conn, $row[4] ?? ''),
                'Option_A_En' => escape($conn, $row[5] ?? ''),
                'Option_B_En' => escape($conn, $row[6] ?? ''),
                'Option_C_En' => escape($conn, $row[7] ?? ''),
                'Option_D_En' => escape($conn, $row[8] ?? ''),
                'Question_Ta' => escape($conn, $row[9] ?? ''),
                'Statement1_Ta' => escape($conn, $row[10] ?? ''),
                'Statement2_Ta' => escape($conn, $row[11] ?? ''),
                'Items_Ta' => escape($conn, $row[12] ?? ''),
                'Option_A_Ta' => escape($conn, $row[13] ?? ''),
                'Option_B_Ta' => escape($conn, $row[14] ?? ''),
                'Option_C_Ta' => escape($conn, $row[15] ?? ''),
                'Option_D_Ta' => escape($conn, $row[16] ?? ''),
                'Answer_Key' => escape($conn, $row[17] ?? ''),
                'Options_En_JSON' => escape($conn, $row[18] ?? ''),
                'Options_Ta_JSON' => escape($conn, $row[19] ?? ''),
                'Correct_Answer_Text_En' => escape($conn, $row[20] ?? '')
            ];

            // Prepare SQL INSERT statement
            $sql = "INSERT IGNORE INTO mcqs (
                Q_No, Question_En, Statement1_En, Statement2_En, Items_En, 
                Option_A_En, Option_B_En, Option_C_En, Option_D_En, 
                Question_Ta, Statement1_Ta, Statement2_Ta, Items_Ta, 
                Option_A_Ta, Option_B_Ta, Option_C_Ta, Option_D_Ta, 
                Answer_Key, Options_En_JSON, Options_Ta_JSON, Correct_Answer_Text_En
            ) VALUES (
                {$data['Q_No']}, '{$data['Question_En']}', '{$data['Statement1_En']}', '{$data['Statement2_En']}', '{$data['Items_En']}',
                '{$data['Option_A_En']}', '{$data['Option_B_En']}', '{$data['Option_C_En']}', '{$data['Option_D_En']}',
                '{$data['Question_Ta']}', '{$data['Statement1_Ta']}', '{$data['Statement2_Ta']}', '{$data['Items_Ta']}',
                '{$data['Option_A_Ta']}', '{$data['Option_B_Ta']}', '{$data['Option_C_Ta']}', '{$data['Option_D_Ta']}',
                '{$data['Answer_Key']}', '{$data['Options_En_JSON']}', '{$data['Options_Ta_JSON']}', '{$data['Correct_Answer_Text_En']}'
            )";

            if ($conn->query($sql) === TRUE) {
                echo "Record for Q_No {$data['Q_No']} inserted successfully<br>";
            } else {
                echo "Error inserting record for Q_No {$data['Q_No']}: " . $conn->error . "<br>";
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
            <label for="excel_file">Choose a file location</label><br>
            <input type="file" name="excel_file" id="excel_file"><br>
            <input type="submit" value="Upload">
        </form>
    </div>
</body>
</html>