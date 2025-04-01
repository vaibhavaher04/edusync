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

// Fetch admission details
try {
    $stmt = $db->prepare("SELECT af.*, c.college_name 
                         FROM admission_form af
                         JOIN colleges c ON af.college_id = c.college_id
                         WHERE af.user_id = ?");
    $stmt->execute([$student_id]);
    $admission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admission) {
        die("No admission record found for this student");
    }

    // Set college-specific details
    $college_name = $admission['college_name'];
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
    <title>Admin - View Admission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .main-heading { font-size: 30px !important; }
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
            <h2>Admission Preview</h2>
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
                    <h1 class="main-heading text-center"><?php echo htmlspecialchars($college_name); ?></h1>
                    <p class="text-center">
                        Approved by UGC | Accredited by NAAC<br>
                        Email: admin@college.edu | Website: www.college.edu<br>
                        Address: <?php echo $default_address; ?>
                    </p>
                </div>
            </div>

            <hr>
            <h3 class="text-center mb-4"><u>Admission Form Details</u></h3>
            
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Admission Form Number:</strong> <?php echo htmlspecialchars($admission['admission_form_number']); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($admission['full_name']); ?></p>
                    <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($admission['mothers_name']); ?></p>
                    <p><strong>Year:</strong> <?php echo htmlspecialchars($admission['year']); ?></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($admission['gender']); ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <div>
                        <strong>Photo:</strong>
                        <img src="uploads/<?php echo htmlspecialchars($admission['photo']); ?>" class="border mt-2" width="150" height="150" alt="Photo">
                    </div>
                </div>
            </div>
            
            <!-- Rest of the admission details -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($admission['birth_date']); ?></p>
                    <p><strong>Mother Tongue:</strong> <?php echo htmlspecialchars($admission['mother_tongue']); ?></p>
                    <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($admission['blood_group']); ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <div>
                        <strong>Signature:</strong>
                        <img src="uploads/<?php echo htmlspecialchars($admission['signature']); ?>" class="border mt-2" width="150" height="100" alt="Signature">
                    </div>
                </div>
            </div>
            
            <hr>
            <h4 class="mb-3">Contact Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($admission['location']); ?></p>
                    <p><strong>Taluka:</strong> <?php echo htmlspecialchars($admission['taluka']); ?></p>
                    <p><strong>District:</strong> <?php echo htmlspecialchars($admission['district']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>State:</strong> <?php echo htmlspecialchars($admission['state']); ?></p>
                    <p><strong>Country:</strong> <?php echo htmlspecialchars($admission['country']); ?></p>
                    <p><strong>Pin Code:</strong> <?php echo htmlspecialchars($admission['pincode']); ?></p>
                </div>
            </div>
            
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admission['email']); ?></p>
                    <p><strong>Mobile:</strong> <?php echo htmlspecialchars($admission['mobile']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Parent's Mobile:</strong> <?php echo htmlspecialchars($admission['parents_mobile']); ?></p>
                    <p><strong>Declaration:</strong> <?php echo $admission['declaration'] ? 'Yes' : 'No'; ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>