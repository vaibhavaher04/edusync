<?php
require_once('config.php'); // Include your database configuration file

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student details
$student_sql = "SELECT u.id, u.name, u.email, af.photo 
                FROM users u 
                LEFT JOIN admission_form af ON u.id = af.user_id 
                WHERE u.id = :student_id";
$student_stmt = $db->prepare($student_sql);
$student_stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$student_stmt->execute();
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

$student_name = $student['name'];
$student_email = $student['email'];
$student_photo = $student['photo'] ? 'uploads/' . $student['photo'] : 'default_profile.webp';
 // Default photo if not uploaded


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <!-- GOOGLE FONTS (MONTSERRAT) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/student_dashboard.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.html">EduSync</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="student_dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#notificationModal">
                            Notifications <span id="notificationCount" class="badge bg-danger">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- End of Navbar -->

    <!-- Main Content -->
    <section class="container my-5">
        <h2 class="text-center mb-4">Our Services for Students</h2>
        <p class="text-center mb-5">We provide essential academic services to simplify student life. From admission assistance to exam support, we ensure a smooth and hassle-free process for every student.</p>

        <!-- Services Grid -->
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <div class="col">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="uil uil-graduation-cap fs-1 mb-3"></i>
                        <h5 class="card-title">Admission Form Assistance</h5>
                        <p class="card-text">Guidance and support for filling out admission forms accurately and on time.</p>
                        <a href="admission_form.php" class="btn btn-primary">Fill Form</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="uil uil-notes fs-1 mb-3"></i>
                        <h5 class="card-title">Exam Form Submission</h5>
                        <p class="card-text">Help with completing and submitting exam forms, ensuring a smooth registration process.</p>
                        <a href="exam_form.php" class="btn btn-primary">Fill Form</a>
                    </div>
                </div>
            </div>

            <!-- Hall Ticket Download Functionality -->

            <div class="col">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="uil uil-post-stamp fs-1 mb-3"></i>
                        <h5 class="card-title">Hall Ticket Download</h5>
                        <p class="card-text">Quick access to hall tickets and exam-related documents for students.</p>
                        <?php
                        // Check if the student has filled the exam form
                        $exam_form_check_sql = "SELECT * FROM exam_form WHERE user_id = :user_id";
                        $exam_form_check_stmt = $db->prepare($exam_form_check_sql);
                        $exam_form_check_stmt->bindParam(':user_id', $student_id, PDO::PARAM_INT);
                        $exam_form_check_stmt->execute();
                        $exam_form_exists = $exam_form_check_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($exam_form_exists) {
                            echo '<a href="hall_ticket.php" class="btn btn-primary">Download Hall Ticket</a>';
                        } else {
                            echo '<p class="text-danger">Please fill out the exam form to download the hall ticket.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Result Download Functionality -->

            <div class="col">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="uil uil-file-download-alt fs-1 mb-3"></i>
                        <h5 class="card-title">Result Checking</h5>
                        <p class="card-text">Easy and fast result checking with guidance on next steps after results are declared.</p>
                        <a href="result.php" class="btn btn-primary">Download</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End of Main Content -->

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img src="<?php echo $student_photo; ?>" alt="Profile Photo" class="img-fluid rounded-circle" style="width: 100px; height: 100px;">
                    </div>
                    <form id="profileForm">
                        <div class="mb-3">
                            <label for="studentName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="studentName" value="<?php echo $student_name; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="studentEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="studentEmail" value="<?php echo $student_email; ?>" disabled>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="notificationList" class="list-group">
                        <!-- Notifications will be loaded here via AJAX -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">Copyright &copy; EduSync - An Integrated College Management and Service Portal</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Fetch notifications via AJAX
        function fetchNotifications() {
            $.ajax({
                url: 'fetch_notifications.php',
                method: 'GET',
                success: function(response) {
                    $('#notificationList').html(response);
                    $('#notificationCount').text($('#notificationList li').length);
                }
            });
        }

        // Fetch notifications every 5 seconds
        setInterval(fetchNotifications, 5000);

        // Fetch notifications on page load
        $(document).ready(function() {
            fetchNotifications();
        });
    </script>
</body>

</html>