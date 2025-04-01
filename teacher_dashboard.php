<?php
require_once('config.php'); // Include your database configuration file

// Check if the teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacherlogin.php"); // Redirect to login if not logged in
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch teacher's college details
$teacher_sql = "SELECT college_id, college_name FROM teachers WHERE teacher_id = :teacher_id";
$teacher_stmt = $db->prepare($teacher_sql);
$teacher_stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
$teacher_stmt->execute();
$teacher = $teacher_stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    die("Teacher not found.");
}

$college_id = $teacher['college_id'];
$college_name = $teacher['college_name'];

// Fetch students for the selected semester
$semester = isset($_GET['semester']) ? $_GET['semester'] : '1'; // Default to Semester 1

// Fetch students from the exam_form and admission_form tables for the selected semester and college
$student_sql = "SELECT u.id AS user_id, u.name, u.email, u.mobile 
                FROM exam_form ef 
                JOIN users u ON ef.user_id = u.id 
                JOIN admission_form af ON ef.user_id = af.user_id 
                WHERE af.college_id = :college_id AND ef.semester = :semester";
$student_stmt = $db->prepare($student_sql);
$student_stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
$student_stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
$student_stmt->execute();
$students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subjects for the selected semester
$subject_sql = "SELECT subject_code, subject_name FROM subjects WHERE semester = :semester";
$subject_stmt = $db->prepare($subject_sql);
$subject_stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
$subject_stmt->execute();
$subjects = $subject_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle mark submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_marks'])) {
        $user_id = $_POST['user_id'];
        $subject_code = $_POST['subject_code'];
        $marks_obtained = $_POST['marks_obtained'];

        // Validate marks
        if ($marks_obtained < 0 || $marks_obtained > 100) {
            $error = "Marks must be between 0 and 100.";
        } else {
            // Insert marks into the database
            $insert_sql = "INSERT INTO student_marks (user_id, college_id, subject_code, semester, marks_obtained) 
                           VALUES (:user_id, :college_id, :subject_code, :semester, :marks_obtained)";
            $insert_stmt = $db->prepare($insert_sql);
            $insert_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $insert_stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
            $insert_stmt->bindParam(':subject_code', $subject_code, PDO::PARAM_STR);
            $insert_stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $insert_stmt->bindParam(':marks_obtained', $marks_obtained, PDO::PARAM_INT);

            if ($insert_stmt->execute()) {
                $success = "Marks added successfully!";
                
                // After inserting marks, send a notification
                $notification_sql = "INSERT INTO notifications (student_id, message) VALUES (:student_id, :message)";
                $notification_stmt = $db->prepare($notification_sql);
                $notification_stmt->bindParam(':student_id', $user_id, PDO::PARAM_INT);
                
                // Get subject name for the notification
                $subject_name = '';
                foreach ($subjects as $sub) {
                    if ($sub['subject_code'] == $subject_code) {
                        $subject_name = $sub['subject_name'];
                        break;
                    }
                }
                
                $message = "Your marks for {$subject_name} (Semester {$semester}) have been updated to {$marks_obtained}.";
                $notification_stmt->bindParam(':message', $message, PDO::PARAM_STR);
                $notification_stmt->execute();
            } else {
                $error = "Failed to add marks. Please try again.";
            }
        }
    } elseif (isset($_POST['edit_marks'])) {
        $mark_id = $_POST['mark_id'];
        $marks_obtained = $_POST['marks_obtained'];

        // Validate marks
        if ($marks_obtained < 0 || $marks_obtained > 100) {
            $error = "Marks must be between 0 and 100.";
        } else {
            // Update marks in the database
            $update_sql = "UPDATE student_marks SET marks_obtained = :marks_obtained WHERE mark_id = :mark_id";
            $update_stmt = $db->prepare($update_sql);
            $update_stmt->bindParam(':marks_obtained', $marks_obtained, PDO::PARAM_INT);
            $update_stmt->bindParam(':mark_id', $mark_id, PDO::PARAM_INT);

            if ($update_stmt->execute()) {
                $success = "Marks updated successfully!";
            } else {
                $error = "Failed to update marks. Please try again.";
            }
        }
    } elseif (isset($_POST['send_notification'])) {
        $message = $_POST['message'];
        $target = $_POST['notification_target'];
        
        try {
            if ($target == 'all_students') {
                // Send to all students in teacher's college
                $query = "INSERT INTO notifications (student_id, message) 
                          SELECT u.id, ? 
                          FROM users u
                          JOIN admission_form af ON u.id = af.user_id
                          WHERE af.college_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$message, $college_id]);
            } 
            elseif ($target == 'selected_semester') {
                // Send to students in selected semester of teacher's college
                $query = "INSERT INTO notifications (student_id, message) 
                          SELECT u.id, ? 
                          FROM users u
                          JOIN admission_form af ON u.id = af.user_id
                          JOIN exam_form ef ON u.id = ef.user_id
                          WHERE af.college_id = ? AND ef.semester = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$message, $college_id, $semester]);
            }
            elseif ($target == 'selected_student' && isset($_POST['student_id'])) {
                // Send to specific student
                $student_id = $_POST['student_id'];
                $query = "INSERT INTO notifications (student_id, message) VALUES (?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$student_id, $message]);
            }
            
            $notification_success = "Notification sent successfully!";
        } catch (PDOException $e) {
            $notification_error = "Error sending notification: " . $e->getMessage();
        }
    }
}

// Fetch marks for each student
$student_marks = [];
foreach ($students as $student) {
    $marks_sql = "SELECT sm.mark_id, sm.subject_code, sm.marks_obtained, s.subject_name 
                  FROM student_marks sm 
                  JOIN subjects s ON sm.subject_code = s.subject_code 
                  WHERE sm.user_id = :user_id AND sm.semester = :semester";
    $marks_stmt = $db->prepare($marks_sql);
    $marks_stmt->bindParam(':user_id', $student['user_id'], PDO::PARAM_INT);
    $marks_stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
    $marks_stmt->execute();
    $student_marks[$student['user_id']] = $marks_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #6c63ff;
            color: white;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #6c63ff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #5a52e0;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .notification-form {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Logout Button -->
        <div class="text-end mb-3">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <h1 class="text-center mb-4">Teacher Dashboard</h1>
        <h3 class="text-center mb-4"><?php echo htmlspecialchars($college_name); ?></h3>

        <!-- Display Success/Error Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success text-center"><?php echo $success; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($notification_success)): ?>
            <div class="alert alert-success text-center"><?php echo $notification_success; ?></div>
        <?php elseif (isset($notification_error)): ?>
            <div class="alert alert-danger text-center"><?php echo $notification_error; ?></div>
        <?php endif; ?>

        <!-- Notification System -->
        <div class="card">
            <div class="card-header">
                Send Notifications
            </div>
            <div class="card-body">
                <form method="POST" action="teacher_dashboard.php?semester=<?php echo $semester; ?>">
                    <div class="mb-3">
                        <label for="message" class="form-label">Notification Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Send To:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="notification_target" id="allStudents" value="all_students" checked>
                            <label class="form-check-label" for="allStudents">
                                All Students in My College
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="notification_target" id="selectedSemester" value="selected_semester">
                            <label class="form-check-label" for="selectedSemester">
                                Current Semester Students (Semester <?php echo $semester; ?>)
                            </label>
                        </div>
                        
                        <?php if (!empty($students)): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="notification_target" id="selectedStudent" value="selected_student">
                                <label class="form-check-label" for="selectedStudent">
                                    Specific Student
                                </label>
                                <select class="form-select mt-2" name="student_id" id="studentSelect" disabled>
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['user_id']; ?>">
                                            <?php echo htmlspecialchars($student['name']); ?> (ID: <?php echo $student['user_id']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="send_notification" class="btn btn-primary">Send Notification</button>
                </form>
            </div>
        </div>

        <!-- Semester Selection -->
        <div class="card">
            <div class="card-header">
                Select Semester
            </div>
            <div class="card-body">
                <form method="GET" action="teacher_dashboard.php">
                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <select name="semester" id="semester" class="form-control" required>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                                    <?php echo $i . ($i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th'))) . ' Semester'; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">View Students</button>
                </form>
            </div>
        </div>

        <!-- Student List -->
        <div class="card">
            <div class="card-header">
                Student List (Semester <?php echo htmlspecialchars($semester); ?>)
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <p class="text-center">No students found for this semester.</p>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMarksModal<?php echo $student['user_id']; ?>">Add Marks</button>
                                    </td>
                                </tr>

                                <!-- Add Marks Modal -->
                                <div class="modal fade" id="addMarksModal<?php echo $student['user_id']; ?>" tabindex="-1" aria-labelledby="addMarksModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="addMarksModalLabel">Add Marks for <?php echo htmlspecialchars($student['name']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="teacher_dashboard.php?semester=<?php echo $semester; ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo $student['user_id']; ?>">
                                                    <div class="form-group">
                                                        <label for="subject_code">Subject:</label>
                                                        <select name="subject_code" id="subject_code" class="form-control" required>
                                                            <?php foreach ($subjects as $subject): ?>
                                                                <option value="<?php echo htmlspecialchars($subject['subject_code']); ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="marks_obtained">Marks Obtained:</label>
                                                        <input type="number" name="marks_obtained" id="marks_obtained" class="form-control" min="0" max="100" required>
                                                    </div>
                                                    <button type="submit" name="submit_marks" class="btn btn-primary mt-3">Submit Marks</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Display Student Marks -->
        <?php if (!empty($students)): ?>
            <div class="card">
                <div class="card-header">
                    Student Marks (Semester <?php echo htmlspecialchars($semester); ?>)
                </div>
                <div class="card-body">
                    <?php foreach ($students as $student): ?>
                        <h5><?php echo htmlspecialchars($student['name']); ?></h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks Obtained</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($student_marks[$student['user_id']])): ?>
                                    <?php foreach ($student_marks[$student['user_id']] as $mark): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($mark['marks_obtained']); ?></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editMarksModal<?php echo $mark['mark_id']; ?>">Edit</button>
                                            </td>
                                        </tr>

                                        <!-- Edit Marks Modal -->
                                        <div class="modal fade" id="editMarksModal<?php echo $mark['mark_id']; ?>" tabindex="-1" aria-labelledby="editMarksModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editMarksModalLabel">Edit Marks for <?php echo htmlspecialchars($student['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" action="teacher_dashboard.php?semester=<?php echo $semester; ?>">
                                                            <input type="hidden" name="mark_id" value="<?php echo $mark['mark_id']; ?>">
                                                            <div class="form-group">
                                                                <label for="marks_obtained">Marks Obtained:</label>
                                                                <input type="number" name="marks_obtained" id="marks_obtained" class="form-control" value="<?php echo htmlspecialchars($mark['marks_obtained']); ?>" min="0" max="100" required>
                                                            </div>
                                                            <button type="submit" name="edit_marks" class="btn btn-primary mt-3">Update Marks</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No marks found for this student.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable/disable student select based on radio selection
        document.querySelectorAll('input[name="notification_target"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('studentSelect').disabled = this.value !== 'selected_student';
            });
        });
    </script>
</body>
</html>