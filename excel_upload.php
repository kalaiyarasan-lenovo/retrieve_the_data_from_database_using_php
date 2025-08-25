<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db   = "questionsdb";

$con = mysqli_connect($host, $user, $pass, $db);

if(!$con){
    die("Database connection failed: " . mysqli_connect_error());
}

require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_POST['submit']))
{
    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $allowed_ext = ['xls','csv','xlsx'];

    if(in_array($file_ext, $allowed_ext))
    {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];
        $spreadsheet = IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();

        $count = 0;
        foreach($data as $row)
        {
            if($count > 0) // Skip header
            {
                $q_no             = mysqli_real_escape_string($con, $row[0]);
                $English_Q        = mysqli_real_escape_string($con, $row[1]);
                $E_List_1         = mysqli_real_escape_string($con, $row[2]);
                $E_List_2         = mysqli_real_escape_string($con, $row[3]);
                $E_options        = mysqli_real_escape_string($con, $row[4]);
                $E_correct_option = mysqli_real_escape_string($con, $row[5]);
                $Tamil_Q          = mysqli_real_escape_string($con, $row[6]);
                $T_list_1         = mysqli_real_escape_string($con, $row[7]);
                $T_list_2         = mysqli_real_escape_string($con, $row[8]);
                $T_options        = mysqli_real_escape_string($con, $row[9]);
                $T_correct_option = mysqli_real_escape_string($con, $row[10]);

                $query = "INSERT INTO questions 
                    (q_no, English_Q, E_List_1, E_List_2, E_options, E_correct_option, 
                    Tamil_Q, T_list_1, T_list_2, T_options, T_correct_option)
                    VALUES 
                    ('$q_no', '$English_Q', '$E_List_1', '$E_List_2', '$E_options', '$E_correct_option',
                     '$Tamil_Q', '$T_list_1', '$T_list_2', '$T_options', '$T_correct_option')";

                mysqli_query($con, $query);
                $msg = true;
            }
            else
            {
                $count = 1; 
            }
        }

        $_SESSION['message'] = isset($msg) 
            ? "Questions Imported Successfully" 
            : "No Data Imported";

        header('Location: upload_file.php');
        exit(0);
    }
    else
    {
        $_SESSION['message'] = "Invalid File Format";
        header('Location: upload_file.php');
        exit(0);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File</title>
    <link rel="stylesheet" href="upload_style.css">
</head>
<body>
    <?php if(isset($_SESSION['message'])): ?>
        <p style="color: green; font-weight: bold;">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']); 
            ?>
        </p>
    <?php endif; ?>

    <form action="excel_upload.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="upload">Choose a file</label>
            <input type="file" name="import_file" required>
        </div>
        <div>
            <button type="submit" name="submit">Upload</button>
        </div>
    </form>
</body>
</html>
