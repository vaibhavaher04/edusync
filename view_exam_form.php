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

// Fetch exam form details
try {
    $stmt = $db->prepare("SELECT ef.*, af.admission_form_number, c.college_name, 
                         af.full_name, af.mothers_name, af.gender, af.birth_date,
                         af.location, af.taluka, af.district, af.state, af.country, af.pincode,
                         af.email, af.mobile, af.parents_mobile
                         FROM exam_form ef
                         JOIN admission_form af ON ef.user_id = af.user_id
                         JOIN colleges c ON af.college_id = c.college_id
                         WHERE ef.user_id = ?");
    $stmt->execute([$student_id]);
    $exam_form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exam_form) {
        die("No exam form found for this student");
    }

    // Fetch subjects for the semester
    $semester = $exam_form['semester'];
    $subjects_stmt = $db->prepare("SELECT * FROM subjects WHERE semester = ?");
    $subjects_stmt->execute([$semester]);
    $subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set college-specific details
    $college_name = $exam_form['college_name'];
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
    <title>Admin - View Exam Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
        .college-logo {
            max-width: 100px;
            height: auto;
        }
        .preview-container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Exam Form Preview</h2>
            <div class="no-print">
                <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <button onclick="window.print()" class="btn btn-primary">Print</button>
            </div>
        </div>

        <div class="preview-container">
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

            <h3 class="text-center mb-4"><u>Exam Form Details</u></h3>
            
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Exam Form Number:</strong> <?php echo htmlspecialchars($exam_form['exam_form_number']); ?></p>
                    <p><strong>Admission Form Number:</strong> <?php echo htmlspecialchars($exam_form['admission_form_number']); ?></p>
                    <p><strong>Semester:</strong> <?php echo htmlspecialchars($exam_form['semester']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Applicant Name:</strong> <?php echo htmlspecialchars($exam_form['full_name']); ?></p>
                    <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($exam_form['mothers_name']); ?></p>
                </div>
            </div>
            
            <hr>
            <h4 class="mb-3">Personal Details</h4>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($exam_form['gender']); ?></p>
                    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($exam_form['birth_date']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($exam_form['location']); ?></p>
                    <p><strong>Taluka:</strong> <?php echo htmlspecialchars($exam_form['taluka']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>District:</strong> <?php echo htmlspecialchars($exam_form['district']); ?></p>
                    <p><strong>State:</strong> <?php echo htmlspecialchars($exam_form['state']); ?></p>
                    <p><strong>Country:</strong> <?php echo htmlspecialchars($exam_form['country']); ?></p>
                    <p><strong>Pin Code:</strong> <?php echo htmlspecialchars($exam_form['pincode']); ?></p>
                </div>
            </div>
            
            <hr>
            <h4 class="mb-3">Contact Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($exam_form['email']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($exam_form['mobile']); ?></p>
                </div>
            </div>
            
            <hr>
            <h4 class="mb-3">Subjects</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <hr>
            <p><strong>Declaration:</strong> <?php echo $exam_form['declaration'] ? 'Yes' : 'No'; ?></p>
            <p><strong>Submission Date:</strong> <?php echo htmlspecialchars($exam_form['submission_date']); ?></p>
        </div>
    </div>
</body>
</html>