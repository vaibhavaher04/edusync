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

// Fetch student details
try {
    // Get student info
    $stmt = $db->prepare("SELECT u.id, u.name, u.email, 
                         af.admission_form_number, af.full_name, af.mothers_name,
                         c.college_name, ef.semester
                         FROM users u
                         JOIN admission_form af ON u.id = af.user_id
                         JOIN colleges c ON af.college_id = c.college_id
                         JOIN exam_form ef ON u.id = ef.user_id
                         WHERE u.id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die("Student not found");
    }

    // Get marks
    $marks_stmt = $db->prepare("SELECT sm.*, s.subject_name
                               FROM student_marks sm
                               JOIN subjects s ON sm.subject_code = s.subject_code
                               WHERE sm.user_id = ?");
    $marks_stmt->execute([$student_id]);
    $marks = $marks_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total and percentage
    $total_marks = 0;
    $total_subjects = count($marks);
    foreach ($marks as $mark) {
        $total_marks += $mark['marks_obtained'];
    }

    $percentage = ($total_subjects > 0) ? ($total_marks / ($total_subjects * 100)) * 100 : 0;
    $result = ($percentage >= 35) ? "Pass" : "Fail";

    // Calculate grades
    $grades = [];
    foreach ($marks as $mark) {
        $marks_obtained = $mark['marks_obtained'];
        if ($marks_obtained >= 90) $grade = "A+";
        elseif ($marks_obtained >= 80) $grade = "A";
        elseif ($marks_obtained >= 70) $grade = "B";
        elseif ($marks_obtained >= 60) $grade = "C";
        elseif ($marks_obtained >= 50) $grade = "D";
        elseif ($marks_obtained >= 40) $grade = "E";
        else $grade = "F";
        $grades[] = $grade;
    }

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

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
        .college-logo {
            max-width: 100px;
            height: auto;
        }
        .result-container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
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
            <h2>Student Result</h2>
            <div class="no-print">
                <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <button onclick="window.print()" class="btn btn-primary">Print</button>
            </div>
        </div>

        <div class="result-container">
            <div class="row mb-4">
                <div class="col-2">
                    <img src="<?php echo htmlspecialchars($college_logo); ?>" class="college-logo img-fluid" alt="College Logo">
                </div>
                <div class="col">
                    <h1 class="text-center"><?php echo htmlspecialchars($college_name); ?></h1>
                    <p class="text-center">
                        Approved by UGC | Accredited by NAAC<br>
                        Email: admin@college.edu | Website: www.college.edu<br>
                        Address: <?php echo $default_address; ?>
                    </p>
                </div>
            </div>

            <h3 class="text-center mb-4"><u>Semester Examination Result</u></h3>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
                    <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($student['mothers_name']); ?></p>
                    <p><strong>Admission Form Number:</strong> <?php echo htmlspecialchars($student['admission_form_number']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Semester:</strong> <?php echo htmlspecialchars($student['semester']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                </div>
            </div>
            
            <hr>
            <h4 class="text-center mb-3">Subject-wise Marks</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Marks Obtained</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($marks as $index => $mark): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mark['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($mark['marks_obtained']); ?></td>
                            <td><?php echo htmlspecialchars($grades[$index]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <p><strong>Total Marks:</strong> <?php echo $total_marks; ?> out of <?php echo $total_subjects * 100; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Percentage:</strong> <?php echo number_format($percentage, 2); ?>%</p>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <h4>Result: <span class="<?php echo ($result == 'Pass') ? 'text-success' : 'text-danger'; ?>">
                    <?php echo $result; ?>
                </span></h4>
            </div>
            
            <div class="text-center mt-4">
                <p>_________________________<br>Principal's Signature</p>
            </div>
        </div>
    </div>
</body>
</html>