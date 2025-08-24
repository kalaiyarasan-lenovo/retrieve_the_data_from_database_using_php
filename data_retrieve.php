<?php
// 1. Connect to the database
$servername = "localhost";
$username   = "root";       
$password   = "";           
$dbname     = "questionsdb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add page styles + script
echo "<style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 80%;
            max-width: 800px;
            background: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }
        hr {
            margin: 30px 0;
        }
        label {
            display: block;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #f9f9f9;
            cursor: pointer;
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
      </style>

      <script>
        function showAnswer(qid, eCorrect, tCorrect) {
            document.getElementById('ans' + qid).style.display = 'block';

            // Highlight English
            if (eCorrect !== 'Not Available') {
                let eOptions = document.getElementsByName('E_q' + qid);
                eOptions.forEach(opt => {
                    let parent = opt.parentElement;
                    if (opt.value === eCorrect) {
                        parent.classList.add('correct');
                    } else {
                        parent.classList.add('wrong');
                    }
                });
            }

            // Highlight Tamil
            if (tCorrect !== 'Not Available') {
                let tOptions = document.getElementsByName('T_q' + qid);
                tOptions.forEach(opt => {
                    let parent = opt.parentElement;
                    if (opt.value === tCorrect) {
                        parent.classList.add('correct');
                    } else {
                        parent.classList.add('wrong');
                    }
                });
            }
        }
      </script>";

echo "<div class='container'>";

// 2. Query for all questions from new table
$sql = "SELECT * FROM mcq_questions";  
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div style='margin-bottom:30px;'>";

        // ================= ENGLISH QUESTION =================
        echo "<p><strong>" . $row['q_no'] . ". " . htmlspecialchars($row['question_en']) . "</strong></p>";

        if (!empty($row['statement1_en'])) {
            echo "<h4>List 1:</h4>";
            echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>" . htmlspecialchars($row['statement1_en']) . "</pre>";
        }
        if (!empty($row['statement2_en'])) {
            echo "<h4>List 2:</h4>";
            echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>" . htmlspecialchars($row['statement2_en']) . "</pre>";
        }

        // ================= TAMIL QUESTION =================
        echo "<p style='margin-top:15px;'><strong>" . $row['q_no'] . ". " . htmlspecialchars($row['question_ta']) . "</strong></p>";

        if (!empty($row['statement1_ta'])) {
            echo "<h4>பட்டியல் 1:</h4>";
            echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>" . htmlspecialchars($row['statement1_ta']) . "</pre>";
        }
        if (!empty($row['statement2_ta'])) {
            echo "<h4>பட்டியல் 2:</h4>";
            echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>" . htmlspecialchars($row['statement2_ta']) . "</pre>";
        }

        // ================= ENGLISH OPTIONS =================
        echo "<h4>English Options:</h4>";
        foreach (['option_a_en','option_b_en','option_c_en','option_d_en'] as $col) {
            if (!empty($row[$col])) {
                $id = "E_{$row['q_no']}_$col";
                $optionValue = htmlspecialchars(trim($row[$col]));
                echo "<label for='$id'>
                        <input type='radio' id='$id' name='E_q{$row['q_no']}' value='$optionValue'>
                        $optionValue
                      </label>";
            }
        }

        // ================= TAMIL OPTIONS =================
        echo "<h4>தமிழ் விருப்பங்கள்:</h4>";
        foreach (['option_a_ta','option_b_ta','option_c_ta','option_d_ta'] as $col) {
            if (!empty($row[$col])) {
                $id = "T_{$row['q_no']}_$col";
                $optionValue = htmlspecialchars(trim($row[$col]));
                echo "<label for='$id'>
                        <input type='radio' id='$id' name='T_q{$row['q_no']}' value='$optionValue'>
                        $optionValue
                      </label>";
            }
        }

        // ================= CORRECT ANSWERS =================
        $englishAnswer = !empty($row['correct_answer_text_en']) ? htmlspecialchars($row['correct_answer_text_en']) : "Not Available";
        $tamilAnswer   = !empty($row['answer_key']) ? htmlspecialchars($row['answer_key']) : "Not Available";  

        echo "<button onclick=\"showAnswer('{$row['q_no']}', '$englishAnswer', '$tamilAnswer')\" 
                style='margin-top:15px; background:#f0ad4e; color:white; padding:8px 12px; border:none; border-radius:5px;'>
                View Correct Answer</button>";

        echo "<div id='ans{$row['q_no']}' style='display:none; margin-top:10px; color:green; font-weight:bold;'>
                <p>✅ English Correct Answer: $englishAnswer</p>
                <p>✅ Tamil Correct Answer: $tamilAnswer</p>
              </div>";

        echo "</div><hr>";
    }
} else {
    echo "No questions found.";
}

echo "</div>"; 
$conn->close();
?>
