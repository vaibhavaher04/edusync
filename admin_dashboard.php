<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Handle college selection
$selected_college = null;
$students = [];
$stats = [
    'total_students' => 0,
    'exam_form_filled' => 0,
    'results_declared' => 0
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['college_id'])) {
    $college_id = $_POST['college_id'];
    $selected_college = $college_id;
    
    try {
        // Fetch students for selected college
        $query = "SELECT u.id, u.name, ef.semester 
                  FROM users u
                  JOIN admission_form af ON u.id = af.user_id
                  LEFT JOIN exam_form ef ON u.id = ef.user_id
                  WHERE af.college_id = ?
                  GROUP BY u.id";
        $stmt = $db->prepare($query);
        $stmt->execute([$college_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $stats['total_students'] = count($students);
        
        // Count students who filled exam form
        $query = "SELECT COUNT(DISTINCT user_id) as count 
                  FROM exam_form 
                  WHERE user_id IN (SELECT user_id FROM admission_form WHERE college_id = ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$college_id]);
        $stats['exam_form_filled'] = $stmt->fetchColumn();
        
        // Count students with results declared
        $query = "SELECT COUNT(DISTINCT user_id) as count 
                  FROM student_marks 
                  WHERE college_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$college_id]);
        $stats['results_declared'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Handle teacher management
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['teacher_action'])) {
    try {
        if ($_POST['teacher_action'] == 'add') {
            // Add new teacher
            $query = "INSERT INTO teachers (college_id, college_name, username, password) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $_POST['college_id'],
                $_POST['college_name'],
                $_POST['username'],
                password_hash($_POST['password'], PASSWORD_DEFAULT)
            ]);
            $teacher_success = "Teacher added successfully!";
        } elseif ($_POST['teacher_action'] == 'remove') {
            // Remove teacher
            $query = "DELETE FROM teachers WHERE teacher_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_POST['teacher_id']]);
            $teacher_success = "Teacher removed successfully!";
        }
    } catch (PDOException $e) {
        $teacher_error = "Error: " . $e->getMessage();
    }
}

// Handle notification sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_notification'])) {
    try {
        $message = $_POST['message'];
        $target = $_POST['notification_target'];
        
        if ($target == 'all_colleges') {
            // Send to all students in all colleges
            $query = "INSERT INTO notifications (student_id, message) 
                      SELECT id, ? FROM users";
            $stmt = $db->prepare($query);
            $stmt->execute([$message]);
        } 
        elseif ($target == 'selected_college' && isset($_POST['college_id'])) {
            // Send to all students in selected college
            $college_id = $_POST['college_id'];
            $query = "INSERT INTO notifications (student_id, message) 
                      SELECT u.id, ? 
                      FROM users u
                      JOIN admission_form af ON u.id = af.user_id
                      WHERE af.college_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$message, $college_id]);
        }
        elseif ($target == 'selected_semester' && isset($_POST['college_id']) && isset($_POST['semester'])) {
            // Send to students in selected semester of selected college
            $college_id = $_POST['college_id'];
            $semester = $_POST['semester'];
            $query = "INSERT INTO notifications (student_id, message) 
                      SELECT u.id, ? 
                      FROM users u
                      JOIN admission_form af ON u.id = af.user_id
                      WHERE af.college_id = ? AND af.semester = ?";
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

// Fetch all colleges for dropdown
$colleges = $db->query("SELECT * FROM colleges")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all teachers for management
$teachers = $db->query("SELECT t.*, c.college_name 
                       FROM teachers t 
                       JOIN colleges c ON t.college_id = c.college_id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Your existing CSS styles here */
        :root {
            --color-primary: #6c63ff;
            --color-success: #00bf8e;
            --color-warning: #f7c94b;
            --color-danger: #f75842;
            --color-danger-variant: rgba(247, 88, 66, 0.4);
            --color-white: #fff;
            --color-light: rgba(255, 255, 255, 0.7);
            --color-black: #000;
            --color-bg: #1f2641;
            --color-bg1: #2e3267;
            --color-bg2: #424890;

            --container-width-lg: 76%;
            --container-width-md: 90%;
            --container-width-sm: 94%;

            --transition: all 400ms ease;
        }

        /* Rest of your CSS styles... */
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>Admin Dashboard</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($notification_success)): ?>
            <div class="alert alert-success">
                <?php echo $notification_success; ?>
            </div>
        <?php elseif (isset($notification_error)): ?>
            <div class="alert alert-danger">
                <?php echo $notification_error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($teacher_success)): ?>
            <div class="alert alert-success">
                <?php echo $teacher_success; ?>
            </div>
        <?php elseif (isset($teacher_error)): ?>
            <div class="alert alert-danger">
                <?php echo $teacher_error; ?>
            </div>
        <?php endif; ?>

        <!-- College Selection Form -->
        <div class="card">
            <div class="card-header">
                Select College
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-8">
                            <select class="form-select" name="college_id" required>
                                <option value="">-- Select College --</option>
                                <?php foreach ($colleges as $college): ?>
                                    <option value="<?php echo $college['college_id']; ?>" 
                                        <?php if ($selected_college == $college['college_id']) echo 'selected'; ?>>
                                        <?php echo $college['college_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Show Students</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($selected_college): ?>
            <!-- Statistics Card -->
            <div class="card">
                <div class="card-header">
                    College Statistics
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="chart-container">
                                <canvas id="statsChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center" 
                                    style="background-color: var(--color-bg2); color: var(--color-white);">
                                    Total Students
                                    <span class="badge bg-primary rounded-pill"><?php echo $stats['total_students']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center" 
                                    style="background-color: var(--color-bg2); color: var(--color-white);">
                                    Exam Forms Filled
                                    <span class="badge bg-success rounded-pill"><?php echo $stats['exam_form_filled']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center" 
                                    style="background-color: var(--color-bg2); color: var(--color-white);">
                                    Results Declared
                                    <span class="badge bg-warning rounded-pill"><?php echo $stats['results_declared']; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="card">
                <div class="card-header">
                    Student List
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Semester</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo $student['id']; ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo $student['semester'] ?? 'N/A'; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="view_admission.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary">View Admission</a>
                                                <a href="view_exam_form.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-success">View Exam Form</a>
                                                <a href="view_hall_ticket.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">Hall Ticket</a>
                                                <a href="view_result.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger">View Result</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Teacher Management -->
        <div class="card">
            <div class="card-header">
                Teacher Management
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Add New Teacher</h5>
                        <form method="POST" action="">
                            <input type="hidden" name="teacher_action" value="add">
                            <div class="mb-3">
                                <label class="form-label">College</label>
                                <select class="form-select" name="college_id" required>
                                    <option value="">-- Select College --</option>
                                    <?php foreach ($colleges as $college): ?>
                                        <option value="<?php echo $college['college_id']; ?>">
                                            <?php echo $college['college_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Teacher</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h5>Existing Teachers</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>College</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['college_name']); ?></td>
                                            <td>
                                                <form method="POST" action="" style="display:inline;">
                                                    <input type="hidden" name="teacher_action" value="remove">
                                                    <input type="hidden" name="teacher_id" value="<?php echo $teacher['teacher_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Form -->
        <div class="card">
            <div class="card-header">
                Send Notifications
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="college_id" value="<?php echo $selected_college ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Notification Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Send To:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="notification_target" id="allColleges" value="all_colleges" checked>
                            <label class="form-check-label" for="allColleges">
                                All Students (All Colleges)
                            </label>
                        </div>
                        
                        <?php if ($selected_college): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="notification_target" id="selectedCollege" value="selected_college">
                                <label class="form-check-label" for="selectedCollege">
                                    All Students in Selected College
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="notification_target" id="selectedSemester" value="selected_semester">
                                <label class="form-check-label" for="selectedSemester">
                                    Students in Selected Semester
                                </label>
                                <select class="form-select mt-2" name="semester" id="semesterSelect" disabled>
                                    <option value="">-- Select Semester --</option>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                    <option value="3">Semester 3</option>
                                    <option value="4">Semester 4</option>
                                    <option value="5">Semester 5</option>
                                    <option value="6">Semester 6</option>
                                </select>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="notification_target" id="selectedStudent" value="selected_student">
                                <label class="form-check-label" for="selectedStudent">
                                    Specific Student
                                </label>
                                <select class="form-select mt-2" name="student_id" id="studentSelect" disabled>
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['name']); ?> (ID: <?php echo $student['id']; ?>)
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
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enable/disable semester and student selects based on radio selection
        document.querySelectorAll('input[name="notification_target"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('semesterSelect').disabled = this.value !== 'selected_semester';
                document.getElementById('studentSelect').disabled = this.value !== 'selected_student';
            });
        });

        // Chart.js implementation for statistics
        <?php if ($selected_college): ?>
            const ctx = document.getElementById('statsChart').getContext('2d');
            const statsChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Total Students', 'Exam Forms Filled', 'Results Declared'],
                    datasets: [{
                        data: [
                            <?php echo $stats['total_students']; ?>,
                            <?php echo $stats['exam_form_filled']; ?>,
                            <?php echo $stats['results_declared']; ?>
                        ],
                        backgroundColor: [
                            'rgba(108, 99, 255, 0.7)',
                            'rgba(0, 191, 142, 0.7)',
                            'rgba(247, 201, 75, 0.7)'
                        ],
                        borderColor: [
                            'rgba(108, 99, 255, 1)',
                            'rgba(0, 191, 142, 1)',
                            'rgba(247, 201, 75, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: 'white'
                            }
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>