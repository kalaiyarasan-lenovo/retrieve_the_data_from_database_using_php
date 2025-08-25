<?php
// Database connection details
$host = "localhost";
$user = "root";
$pass = "";
$db   = "questionsdb";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all questions
$sql = "SELECT * FROM mcq_questions";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MCQ Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .question-box { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        h3 { margin: 0 0 10px; }
    </style>
</head>
<body>
    <h2>Biology Test</h2>
    <form action="submit_test.php" method="post">
        <?php
        if ($result->num_rows > 0) {
            $q_no = 1;
            while($row = $result->fetch_assoc()) {
                echo "<div class='question-box'>";
                echo "<h3>Q" . $q_no . ". " . $row['Tamil_q'] . "</h3>";

                // Decode options
                $options = json_decode($row['t_options'], true);
                if (!is_array($options)) {
                    // fallback if plain text
                    $options = preg_split("/\r\n|\n|\r|,/", $row['t_options']);
                }

                // Print radio buttons
                foreach ($options as $option) {
                    echo "<label><input type='radio' name='answer[".$row['q_no']."]' value='$option'> $option</label><br>";
                }
                echo "</div>";
                $q_no++;
            }
        } else {
            echo "No questions found.";
        }
        $conn->close();
        ?>
        <input type="submit" value="Submit Test">
    </form>
</body>
</html>
