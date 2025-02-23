<?php
require_once('config.php'); // Include your database configuration file

// Validate and sanitize the `user_id` parameter
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    die('Invalid or missing user ID.');
}

$user_id = htmlspecialchars($_GET['user_id']);

// Fetch student details
$sql = "SELECT af.full_name, af.mothers_name, af.email, af.mobile, c.college_name 
        FROM admission_form af
        JOIN colleges c ON af.college_id = c.college_id
        WHERE af.user_id = :user_id";

$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

try {
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die('No student found for the given ID.');
    }

    $college_name = $student['college_name'];

    // Fetch marks
    $marks_query = "SELECT sm.*, s.subject_name
                    FROM student_marks sm
                    JOIN subjects s ON sm.subject_code = s.subject_code
                    WHERE sm.user_id = :user_id";
    $marks_stmt = $db->prepare($marks_query);
    $marks_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $marks_stmt->execute();

    $marks_data = $marks_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total marks and percentage
    $total_marks = 0;
    $total_subjects = count($marks_data);
    foreach ($marks_data as $mark) {
        $total_marks += $mark['marks_obtained'];
    }

    $percentage = ($total_subjects > 0) ? ($total_marks / ($total_subjects * 100)) * 100 : 0;
    $result = ($percentage >= 35) ? "Pass" : "Fail";

    // Calculate Grades
    $grades = [];
    foreach ($marks_data as $mark) {
        $marks = $mark['marks_obtained'];
        if ($marks >= 90) {
            $grade = "A+";
        } elseif ($marks >= 80) {
            $grade = "A";
        } elseif ($marks >= 70) {
            $grade = "B";
        } elseif ($marks >= 60) {
            $grade = "C";
        } elseif ($marks >= 50) {
            $grade = "D";
        } elseif ($marks >= 40) {
            $grade = "E";
        } else {
            $grade = "F";
        }
        $grades[] = $grade;
    }

    // Set the college logo path
    $college_logo = '';
    if ($college_name == "New Arts Commerce And Science College, Shevgaon") {
        $college_logo = './images/NewArtsCollege.jpg';
    } elseif ($college_name == "Dr. Balasaheb Vikhe Patil College, Shevgaon") {
        $college_logo = './images/BalasahebVKCollege.jpg';
    } elseif ($college_name == "Nirmalatai Kakade Arts, Commerce and Science College, Shevgaon") {
        $college_logo = './images/NirmalataiKakdeCollege.jpg';
    } elseif ($college_name == "Dada Patil Rajale Arts and Science College, Pathardi") {
        $college_logo = './images/DPRCCollege.jpg';
    }

} catch (PDOException $e) {
    die('Error fetching data: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { 
            #printbtn { display: none; } 
        }
        .college-logo {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="text-center mb-4">
            <img src="<?php echo htmlspecialchars($college_logo); ?>" class="college-logo" alt="College Logo">
            <h1><?php echo htmlspecialchars($college_name); ?></h1>
        </div>

        <h3 class="text-center">Student Result</h3>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
        <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($student['mothers_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($student['mobile']); ?></p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Semester</th>
                    <th>Marks Obtained</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0; foreach ($marks_data as $mark): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mark['subject_code']); ?></td>
                        <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($mark['semester']); ?></td>
                        <td><?php echo htmlspecialchars($mark['marks_obtained']); ?></td>
                        <td><?php echo htmlspecialchars($grades[$i]); ?></td>
                    </tr>
                <?php $i++; endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total Marks:</strong> <?php echo $total_marks; ?></p>
        <p><strong>Percentage:</strong> <?php echo number_format($percentage, 2); ?>%</p>
        <p><strong>Result:</strong> <?php echo $result; ?></p>

        <div class="text-center">
            <button class="btn btn-primary" id="printbtn" onclick="window.print()">Download Result</button>
        </div>
    </div>
</body>
</html>