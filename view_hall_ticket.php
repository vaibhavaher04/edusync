<?php
require_once('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Get student ID from URL
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    die('Invalid student ID');
}
$student_id = $_GET['student_id'];

// Fetch student and exam details
try {
    // Get student and college info
    $stmt = $db->prepare("SELECT u.id, u.name, u.email, 
                         af.college_id, c.college_name, ef.semester, ef.exam_form_number,
                         ef.submission_date
                         FROM users u
                         JOIN admission_form af ON u.id = af.user_id
                         JOIN colleges c ON af.college_id = c.college_id
                         JOIN exam_form ef ON u.id = ef.user_id
                         WHERE u.id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found or exam form not submitted");
    }

    // Get subjects for the semester
    $semester = $student['semester'];
    $subjects_stmt = $db->prepare("SELECT * FROM subjects WHERE semester = ?");
    $subjects_stmt->execute([$semester]);
    $subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set college-specific details
    $college_name = $student['college_name'];
    $college_logo = '';
    $default_address = "";
    
    if ($college_name == "New Arts Commerce And Science College, Shevgaon") {
        $college_logo = './images/NewArtsCollege.jpg';
        $default_address = "A/P Shevgaon, Taluka Shevgaon, District Ahmednagar, Maharashtra 414502, India.";
    } elseif ($college_name == "Dr. Balasaheb Vikhe Patil College, Shevgaon") {
        $college_logo = './images/BalasahebVKCollege.jpg';
        $default_address = "A/P Shevgaon, Taluka Shevgaon, District Ahmednagar, Maharashtra 414502, India.";
    } elseif ($college_name == "Nirmalatai Kakade Arts, Commerce and Science College, Shevgaon") {
        $college_logo = './images/NirmalataiKakdeCollege.jpg';
        $default_address = "A/P Shevgaon, Taluka Shevgaon, District Ahmednagar, Maharashtra 414502, India.";
    } elseif ($college_name == "Dada Patil Rajale Arts and Science College, Pathardi") {
        $college_logo = './images/DPRCCollege.jpg';
        $default_address = "A/P Pathardi, Taluka Pathardi, District Ahmednagar, Maharashtra 414102, India.";
    }

    // Calculate exam dates (45 days after submission)
    $exam_dates = [];
    $submission_date = new DateTime($student['submission_date']);
    $exam_start_date = clone $submission_date;
    $exam_start_date->modify('+45 days');

    foreach ($subjects as $subject) {
        $exam_dates[] = [
            'subject_code' => $subject['subject_code'],
            'subject_name' => $subject['subject_name'],
            'exam_date' => $exam_start_date->format('Y-m-d')
        ];
        $exam_start_date->modify('+1 day');
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Hall Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
        .college-logo {
            max-width: 100px;
            height: auto;
        }
        .hall-ticket {
            border: 2px solid #000;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Hall Ticket</h2>
            <div class="no-print">
                <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <button onclick="window.print()" class="btn btn-primary">Print</button>
            </div>
        </div>

        <div class="hall-ticket">
            <div class="row mb-4 text-center">
                <div class="col-2">
                    <img src="<?php echo htmlspecialchars($college_logo); ?>" class="college-logo" alt="College Logo">
                </div>
                <div class="col">
                    <h2><?php echo htmlspecialchars($college_name); ?></h2>
                    <p><?php echo $default_address; ?></p>
                </div>
            </div>

            <h3 class="text-center mb-4"><u>Examination Hall Ticket</u></h3>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Exam Form Number:</strong> <?php echo htmlspecialchars($student['exam_form_number']); ?></p>
                    <p><strong>Semester:</strong> <?php echo htmlspecialchars($student['semester']); ?></p>
                </div>
            </div>
            
            <hr>
            <h4 class="text-center mb-3">Exam Schedule</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Exam Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exam_dates as $exam): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($exam['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars($exam['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <hr>
            <div class="mt-4">
                <h5>Instructions:</h5>
                <ol>
                    <li>This hall ticket must be presented at the examination center.</li>
                    <li>Carry a valid photo ID along with this hall ticket.</li>
                    <li>Reporting time is 30 minutes before the exam starts.</li>
                    <li>No electronic devices are allowed in the examination hall.</li>
                </ol>
            </div>
            
            <div class="text-center mt-4">
                <p><strong>Principal's Signature:</strong> _________________________</p>
            </div>
        </div>
    </div>
</body>
</html>