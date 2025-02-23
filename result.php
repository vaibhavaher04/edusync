<?php
include('config.php');

if (isset($_POST['submit'])) {
    $email_mobile = $_POST['email_mobile'];
    $mother_name = $_POST['mother_name'];

    // Check if student exists
    $stmt = $db->prepare("SELECT * FROM admission_form WHERE (email = :email OR mobile = :mobile) AND mothers_name = :mother_name");
    $stmt->bindValue(':email', $email_mobile);
    $stmt->bindValue(':mobile', $email_mobile);
    $stmt->bindValue(':mother_name', $mother_name);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $student['user_id'];
        $full_name = $student['full_name']; // Store full name

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

        if ($total_subjects > 0) { // Avoid division by zero
            $percentage = ($total_marks / ($total_subjects * 100)) * 100;
        } else {
            $percentage = 0; // Handle the case where no marks are found
        }

        // Determine result (Pass/Fail)
        $result = ($percentage >= 35) ? "Pass" : "Fail";

        // Calculate Grades
        $grades = [];
        foreach ($marks_data as $mark) {
            $grade = "";
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


    } else {
        $error = "Invalid credentials. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Check Your Result</h2>

        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="email_mobile" class="form-label">Email or Mobile</label>
                <input type="text" name="email_mobile" id="email_mobile" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="mother_name" class="form-label">Mother's Name</label>
                <input type="text" name="mother_name" id="mother_name" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">View Result</button>
        </form>

        <?php if (isset($marks_data) && count($marks_data) > 0): ?>
            <div class="mt-4">
                <h4 class="text-center">Result for <?php echo htmlspecialchars($full_name); ?></h4> 
                <table class="table table-bordered table-striped">
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

                <div class="mt-3">
                    <p><strong>Total Percentage:</strong> <?php echo number_format($percentage, 2); ?>%</p>
                    <p><strong>Result:</strong> <?php echo $result; ?></p>
                </div>

                <a href="download_result.php?user_id=<?php echo $user_id; ?>" class="btn btn-success w-100">Download Result</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>