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
            // Show the answer section
            document.getElementById('ans' + qid).style.display = 'block';

            // Highlight English correct answer
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

            // Highlight Tamil correct answer
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

// 2. Query for all questions
$sql = "SELECT * FROM questions";  
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div style='margin-bottom:30px;'>";

        // ================= ENGLISH QUESTION =================
        echo "<p><strong>" . $row['q_no'] . ". " . htmlspecialchars($row['English_Q']) . "</strong></p>";

        // English List 1
        if (!empty($row['E_List_1'])) {
            echo "<h4>List 1:</h4>";
            echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>" . htmlspecialchars($row['E_List_1']) . "</pre>";
        }

        // English List 2
        if (!empty($row['E_List_2'])) {
            echo "<h4>List 2:</h4>";
            echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>" . htmlspecialchars($row['E_List_2']) . "</pre>";
        }

        // ================= TAMIL QUESTION =================
        echo "<p style='margin-top:15px;'><strong>" . $row['q_no'] . ". " . htmlspecialchars($row['Tamil_Q']) . "</strong></p>";

        // Tamil List 1
        if (!empty($row['T_list_1'])) {
            echo "<h4>பட்டியல் 1:</h4>";
            echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>" . htmlspecialchars($row['T_list_1']) . "</pre>";
        }

        // Tamil List 2
        if (!empty($row['T_list_2'])) {
            echo "<h4>பட்டியல் 2:</h4>";
            echo "<pre style='background:#f4f4f4; padding:10px; border-radius:5px;'>" . htmlspecialchars($row['T_list_2']) . "</pre>";
        }

        // ================= ENGLISH OPTIONS =================
        if (!empty($row['E_options'])) {
            echo "<h4>English Options:</h4>";
            $options = explode("\n", $row['E_options']);
            foreach ($options as $i => $opt) {
                $id = "E_{$row['q_no']}_$i";
                $optionValue = htmlspecialchars(trim($opt));
                echo "<label for='$id'>
                        <input type='radio' id='$id' name='E_q{$row['q_no']}' value='$optionValue'>
                        $optionValue
                      </label>";
            }
        }

        // ================= TAMIL OPTIONS =================
        if (!empty($row['T_options'])) {
            echo "<h4>தமிழ் விருப்பங்கள்:</h4>";
            $toptions = explode("\n", $row['T_options']);
            foreach ($toptions as $i => $topt) {
                $id = "T_{$row['q_no']}_$i";
                $optionValue = htmlspecialchars(trim($topt));
                echo "<label for='$id'>
                        <input type='radio' id='$id' name='T_q{$row['q_no']}' value='$optionValue'>
                        $optionValue
                      </label>";
            }
        }

        // ================= BOTH CORRECT ANSWERS =================
        $englishAnswer = !empty($row['E_correct_option']) ? htmlspecialchars($row['E_correct_option']) : "Not Available";
        $tamilAnswer   = !empty($row['T_correct_option']) ? htmlspecialchars($row['T_correct_option']) : "Not Available";

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

echo "</div>"; // close container
$conn->close();
?>
