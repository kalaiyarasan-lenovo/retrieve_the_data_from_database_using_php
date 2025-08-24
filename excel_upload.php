<?php
session_start();

// Database connection
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
            if($count > 0) // Skip header row
            {
                $q_no                  = mysqli_real_escape_string($con, $row[0]);
                $English_Q             = mysqli_real_escape_string($con, $row[1]);
                $E_List_1              = mysqli_real_escape_string($con, $row[2]);
                $E_List_2              = mysqli_real_escape_string($con, $row[3]);
                $E_items               = mysqli_real_escape_string($con, $row[4]);
                $E_optA                = mysqli_real_escape_string($con, $row[5]);
                $E_optB                = mysqli_real_escape_string($con, $row[6]);
                $E_optC                = mysqli_real_escape_string($con, $row[7]);
                $E_optD                = mysqli_real_escape_string($con, $row[8]);

                $Tamil_Q               = mysqli_real_escape_string($con, $row[9]);
                $T_list_1              = mysqli_real_escape_string($con, $row[10]);
                $T_list_2              = mysqli_real_escape_string($con, $row[11]);
                $T_items               = mysqli_real_escape_string($con, $row[12]);
                $T_optA                = mysqli_real_escape_string($con, $row[13]);
                $T_optB                = mysqli_real_escape_string($con, $row[14]);
                $T_optC                = mysqli_real_escape_string($con, $row[15]);
                $T_optD                = mysqli_real_escape_string($con, $row[16]);

                $E_options_json        = mysqli_real_escape_string($con, $row[17]);
                $T_options_json        = mysqli_real_escape_string($con, $row[18]);
                $answer_key            = mysqli_real_escape_string($con, $row[19]);
                $correct_answer_text_en= mysqli_real_escape_string($con, $row[20]);

                $query = "INSERT INTO mcq_questions 
                (q_no, question_en, statement1_en, statement2_en, items_en, 
                 option_a_en, option_b_en, option_c_en, option_d_en,
                 question_ta, statement1_ta, statement2_ta, items_ta,
                 option_a_ta, option_b_ta, option_c_ta, option_d_ta,
                 options_en_json, options_ta_json, answer_key, correct_answer_text_en)
                VALUES 
                ('$q_no', '$English_Q', '$E_List_1', '$E_List_2', '$E_items',
                 '$E_optA', '$E_optB', '$E_optC', '$E_optD',
                 '$Tamil_Q', '$T_list_1', '$T_list_2', '$T_items',
                 '$T_optA', '$T_optB', '$T_optC', '$T_optD',
                 '$E_options_json', '$T_options_json', '$answer_key', '$correct_answer_text_en')";

                mysqli_query($con, $query);
                $msg = true;
            }
            else
            {
                $count = 1; // mark header processed
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
