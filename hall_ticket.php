<?php
require_once('config.php'); // Include your database configuration file

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student details
$student_sql = "SELECT u.id, u.name, u.email, af.college_id, c.college_name 
                FROM users u 
                JOIN admission_form af ON u.id = af.user_id 
                JOIN colleges c ON af.college_id = c.college_id
                WHERE u.id = :student_id";
$student_stmt = $db->prepare($student_sql);
$student_stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$student_stmt->execute();
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

$college_logos = [
    "New Arts Commerce And Science College, Shevgaon" => "./images/NewArtsCollege.jpg",
    "Dr. Balasaheb Vikhe Patil College, Shevgaon" => "./images/BalasahebVKCollege.jpg",
    "Nirmalatai Kakade Arts, Commerce and Science College, Shevgaon" => "./images/NirmalataiKakdeCollege.jpg",
    "Dada Patil Rajale Arts and Science College, Pathardi" => "./images/DPRCCollege.jpg"
];

$college_addresses = [
    "New Arts Commerce And Science College, Shevgaon" => "A/P Shevgaon, Taluka Shevgaon, District Ahmednagar, Maharashtra 414502, India.",
    "Dr. Balasaheb Vikhe Patil College, Shevgaon" => "A/P Shevgaon, Taluka Shevgaon, District Ahmednagar, Maharashtra 414502, India.",
    "Nirmalatai Kakade Arts, Commerce and Science College, Shevgaon" => "A/P Shevgaon, Taluka Shevgaon, District Ahmednagar, Maharashtra 414502, India.",
    "Dada Patil Rajale Arts and Science College, Pathardi" => "A/P Pathardi, Taluka Pathardi, District Ahmednagar, Maharashtra 414102, India."
];

$college_logo = $college_logos[$student['college_name']] ?? '';
$default_address = $college_addresses[$student['college_name']] ?? '';

// Fetch exam form details
$exam_form_sql = "SELECT * FROM exam_form WHERE user_id = :student_id";
$exam_form_stmt = $db->prepare($exam_form_sql);
$exam_form_stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$exam_form_stmt->execute();
$exam_form = $exam_form_stmt->fetch(PDO::FETCH_ASSOC);

if (!$exam_form) {
    die("No exam form found. Please fill out the exam form first.");
}

$semester = $exam_form['semester'];
$submission_date = new DateTime($exam_form['submission_date']);

// Fetch subjects based on semester
$subject_sql = "SELECT subject_code, subject_name FROM subjects WHERE semester = :semester";
$subject_stmt = $db->prepare($subject_sql);
$subject_stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
$subject_stmt->execute();
$subjects = $subject_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate exam dates
$exam_dates = [];
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { #printbtn { display: none; } }
        .college-logo { max-width: 100px; height: auto; }
        .hall-ticket { border: 2px solid black; padding: 20px; border-radius: 10px; }
        .table th, .table td { text-align: center; }
    </style>
</head>
<body>
    <div class="container mt-4 hall-ticket">
        <div class="row mb-4 text-center">
            <div class="col-2">
                <img src="<?php echo htmlspecialchars($college_logo); ?>" class="college-logo" alt="College Logo">
            </div>
            <div class="col">
                <h2><?php echo htmlspecialchars($student['college_name']); ?></h2>
                <p><?php echo $default_address; ?></p>
            </div>
        </div>

        <h3 class="text-center">Hall Ticket</h3>
        <table class="table table-bordered">
            <tr><th>Student Name</th><td><?php echo htmlspecialchars($student['name']); ?></td></tr>
            <tr><th>Email</th><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
            <tr><th>Semester</th><td><?php echo htmlspecialchars($semester); ?></td></tr>
        </table>

        <h4>Exam Schedule</h4>
        <table class="table table-bordered">
            <tr><th>Subject Code</th><th>Subject Name</th><th>Exam Date</th></tr>
            <?php foreach ($exam_dates as $exam) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($exam['subject_code']); ?></td>
                    <td><?php echo htmlspecialchars($exam['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
                </tr>
            <?php } ?>
        </table>

        <p><strong>DECLARATION:</strong><br>
        I hereby declare that I have gone through the Syllabus and the list of books prescribed for the examination for which I am appearing. I SHALL BE RESPONSIBLE for any errors and wrong or incomplete entries made by me in the Examination form. I shall not request for special concession such as change in the time and/or day fixed for the University examination etc. on religious or any other grounds.<br>
        Yours faithfully.</p>

        <div class="text-center">
            <button class="btn btn-primary" id="printbtn" onclick="window.print()">Print Hall Ticket</button>
        </div>
    </div>
</body>
</html>