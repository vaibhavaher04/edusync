<?php
require_once('config.php'); // Include your database configuration file

// Validate and sanitize the `id` parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Invalid or missing exam form number.');
}

$exam_form_number = htmlspecialchars($_GET['id']);

// Fetch exam form details
$sql = "SELECT ef.*, af.admission_form_number, c.college_name, af.full_name, af.mothers_name, af.year, af.gender, af.birth_date, af.mother_tongue, af.blood_group, af.location, af.taluka, af.district, af.state, af.country, af.pincode, af.email, af.mobile, af.parents_mobile, af.photo, af.signature 
        FROM exam_form ef 
        JOIN admission_form af ON ef.user_id = af.user_id 
        JOIN colleges c ON af.college_id = c.college_id
        WHERE ef.exam_form_number = :exam_form_number";

$stmt = $db->prepare($sql);
$stmt->bindParam(':exam_form_number', $exam_form_number, PDO::PARAM_STR);

try {
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die('No exam form found for the given ID.');
    }

    $college_name = $row['college_name'];
    $semester = $row['semester'];

    // Fetch subjects based on semester
    $subject_sql = "SELECT subject_code, subject_name FROM subjects WHERE semester = :semester";
    $subject_stmt = $db->prepare($subject_sql);
    $subject_stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
    $subject_stmt->execute();
    $subjects = $subject_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Error fetching data: ' . htmlspecialchars($e->getMessage()));
}

// Set the logo path and address based on the college name
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Form Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { 
            #printbtn { display: none; } 
            .main-heading { font-size: 30px !important; }
        }
        .college-logo {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- College Logo and Name -->
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

        <p>To,<br>Director,<br>Board of Examination & Evaluation,<br>Savitribai Phule Pune University, Pune-411 007.<br>Sir/Madam,<br>I request permission to present myself at the examination courses, mentioned below.</p>

        <h3 class="text-center">Exam Form Details</h3>
        <table class="table table-bordered">
            <tr><th>Course Name</th><td>BSC (Computer Science)</td></tr>
            <tr><th>Exam Form Number</th><td><?php echo htmlspecialchars($row['exam_form_number']); ?></td></tr>
            <tr><th>Admission Form Number</th><td><?php echo htmlspecialchars($row['admission_form_number']); ?></td></tr>
        </table>

        <h4>Personal Details</h4>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($row['full_name']); ?></p>
        <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($row['mothers_name']); ?></p>
        <p><strong>Gender:</strong> <?php echo htmlspecialchars($row['gender']); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($row['birth_date']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($row['location'] . ', ' . $row['taluka'] . ', ' . $row['district'] . ', ' . $row['state'] . ', ' . $row['country'] . ' - ' . $row['pincode']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($row['mobile']); ?></p>
        <p><strong>Parent's Mobile:</strong> <?php echo htmlspecialchars($row['parents_mobile']); ?></p>

        <h4>Subjects</h4>
        <table class="table table-bordered">
            <tr><th>Subject Code</th><th>Subject Name</th></tr>
            <?php foreach ($subjects as $subject) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                    <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                </tr>
            <?php } ?>
        </table>

        <p><strong>DECLARATION:</strong><br>
        I hereby declare that I have gone through the Syllabus and the list of books prescribed for the examination for which I am appearing. I SHALL BE RESPONSIBLE for any errors and wrong or incomplete entries made by me in the Examination form. I shall not request for special concession such as change in the time and/or day fixed for the University examination etc. on religious or any other grounds.<br>
        Yours faithfully.</p>

        <div class="text-center">
            <button class="btn btn-primary" id="printbtn" onclick="window.print()">Print Form</button>
        </div>
    </div>
</body>
</html>