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
$sql = "SELECT * FROM questions";  
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// ----------------- PAGE STYLES & SCRIPT -----------------
echo "<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Quiz App</title>

<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        background: linear-gradient(135deg, #eef2f7, #f9fbfc);
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }
    .container {
        width: 100%;
        max-width: 1000px;
        background: #fff;
        padding: 40px 50px;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.08);
        min-height: 100vh;
    }
    .header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #eee;
    }
    .header h1 {
        font-size: 28px;
        margin: 0;
        color: #333;
    }
    .question-card {
        margin-bottom: 40px;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #eee;
        background: #fafafa;
        transition: transform 0.2s ease;
    }
    .question-card:hover {
        transform: scale(1.01);
        box-shadow: 0px 4px 12px rgba(0,0,0,0.08);
    }
    .question-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
        margin-bottom: 12px;
        text-align: justify;
    }
    pre {
        background: #f4f4f4;
        padding: 10px;
        border-radius: 8px;
        font-size: 14px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    h4 {
        margin-top: 18px;
        margin-bottom: 8px;
        font-size: 16px;
        color: #555;
    }
    label {
        display: flex;
        align-items: center;
        padding: 12px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: justify;
        white-space: normal;
        word-wrap: break-word;
    }
    label:hover {
        background: #f1f7ff;
        border-color: #007bff;
    }
    input[type='radio'] {
        margin-right: 12px;
        flex-shrink: 0;
    }
    .correct {
        background: #d4edda !important;
        border-color: #28a745 !important;
        color: #155724 !important;
        font-weight: bold;
    }
    .wrong {
        background: #f8d7da !important;
        border-color: #dc3545 !important;
        color: #721c24 !important;
    }
    button {
        margin-top: 15px;
        background: #007bff;
        color: white;
        padding: 10px 16px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    button:hover {
        background: #0056b3;
    }
    .answer-box {
        display: none;
        margin-top: 12px;
        padding: 12px;
        border-radius: 8px;
        background: #eafbea;
        color: #155724;
        font-weight: bold;
        border: 1px solid #28a745;
        text-align: justify;
    }
</style>

<script>
    // Highlight correct & wrong answers
    function showAnswer(qid, eCorrect, tCorrect) {
        document.getElementById('ans' + qid).style.display = 'block';

        if (eCorrect !== 'Not Available') {
            let eOptions = document.getElementsByName('E_q' + qid);
            eOptions.forEach(opt => {
                let parent = opt.parentElement;
                if (opt.value === eCorrect) parent.classList.add('correct');
                else parent.classList.add('wrong');
            });
        }

        if (tCorrect !== 'Not Available') {
            let tOptions = document.getElementsByName('T_q' + qid);
            tOptions.forEach(opt => {
                let parent = opt.parentElement;
                if (opt.value === tCorrect) parent.classList.add('correct');
                else parent.classList.add('wrong');
            });
        }
    }

    // Align option text automatically
    window.onload = function() {
        let labels = document.querySelectorAll('label');
        labels.forEach(label => {
            label.style.textAlign = 'justify';
            label.style.lineHeight = '1.5em';
        });

        let titles = document.querySelectorAll('.question-title');
        titles.forEach(title => {
            title.style.textAlign = 'justify';
        });
    };
</script>
</head>
<body>

<div class='container'>
    <div class='header'>
        <h1>📘 Quiz Questions</h1>
    </div>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $qno = htmlspecialchars($row['q_no']);

        echo "<div class='question-card'>";

        // English Question
        $engQ = htmlspecialchars($row['English_Q'] ?? '');
        echo "<p class='question-title'>$qno. $engQ</p>";

        if (!empty($row['E_List_1'])) echo "<pre>" . htmlspecialchars($row['E_List_1']) . "</pre>";
        if (!empty($row['E_List_2'])) echo "<pre>" . htmlspecialchars($row['E_List_2']) . "</pre>";

        // Tamil Question
        $tamilQ = htmlspecialchars($row['Tamil_Q'] ?? '');
        echo "<p class='question-title'>$qno. $tamilQ</p>";

        if (!empty($row['T_list_1'])) echo "<pre>" . htmlspecialchars($row['T_list_1']) . "</pre>";
        if (!empty($row['T_list_2'])) echo "<pre>" . htmlspecialchars($row['T_list_2']) . "</pre>";

        // English Options
        if (!empty($row['E_options'])) {
            echo "<h4>English Options:</h4>";
            $options = preg_split('/\r\n|\n|\r/', $row['E_options']);
            foreach ($options as $i => $opt) {
                $id = "E_{$qno}_$i";
                $optVal = htmlspecialchars(trim($opt));
                echo "<label for='$id'><input type='radio' id='$id' name='E_q{$qno}' value='$optVal'> $optVal</label>";
            }
        }

        // Tamil Options
        if (!empty($row['T_options'])) {
            echo "<h4>தமிழ் விருப்பங்கள்:</h4>";
            $toptions = preg_split('/\r\n|\n|\r/', $row['T_options']);
            foreach ($toptions as $i => $topt) {
                $id = "T_{$qno}_$i";
                $optVal = htmlspecialchars(trim($topt));
                echo "<label for='$id'><input type='radio' id='$id' name='T_q{$qno}' value='$optVal'> $optVal</label>";
            }
        }

        // Correct answers
        $englishAnswer = !empty($row['E_correct_option']) ? htmlspecialchars($row['E_correct_option']) : "Not Available";
        $tamilAnswer   = !empty($row['T_correct_option']) ? htmlspecialchars($row['T_correct_option']) : "Not Available";

        echo "<button onclick=\"showAnswer('$qno', '$englishAnswer', '$tamilAnswer')\">View Correct Answer</button>";

        echo "<div id='ans$qno' class='answer-box'>
                ✅ English Correct Answer: $englishAnswer <br>
                ✅ Tamil Correct Answer: $tamilAnswer
              </div>";

        echo "</div>";
    }
} else {
    echo "<p>No questions found.</p>";
}

echo "</div></body></html>";
$conn->close();
?>
