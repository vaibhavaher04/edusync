<?php
require_once('config.php'); // Include your database configuration file

// Validate and sanitize the `id` parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Invalid or missing admission form number.');
}

$admission_form_number = htmlspecialchars($_GET['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @page {
            margin: 10mm;
        }

        @media print {
            #printbtn {
                display: none !important;
            }

            .main-heading {
                font-size: 30px !important;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-sm-1"></div>
            <div class="col-sm-10 border border-dark p-4">
                <?php
                // Fetch admission form details
                $sql = "SELECT af.*, c.college_name 
        FROM admission_form af 
        JOIN colleges c ON af.college_id = c.college_id 
        WHERE af.admission_form_number = :admission_form_number";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':admission_form_number', $admission_form_number, PDO::PARAM_STR);

                try {
                    $stmt->execute();
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$row) {
                        echo '<p class="text-danger text-center">No admission form found for the given ID.</p>';
                    } else {
                        $college_name = $row['college_name'];

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

                        <div class="row">
                            <div class="col-2">
                                <!-- Display the college logo -->
                                <img src="<?php echo htmlspecialchars($college_logo); ?>" class="img-fluid" alt="College Logo">
                            </div>
                            <div class="col">
                                <h1 class="main-heading text-center"><?php echo htmlspecialchars($college_name); ?></h1>
                                <p class="sub-heading text-center">
                                    Approved by UGC | Accredited by NAAC<br>
                                    Email: admin@college.edu | Website: www.college.edu<br>
                                    Address: <?php echo $default_address; ?>
                                </p>
                            </div>
                        </div>

                        <hr>
                        <h3 class="text-center mb-4"><u>Admission Form 2025</u></h3>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Admission Form Number:</strong> <?php echo htmlspecialchars($row['admission_form_number']); ?></p>
                                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($row['full_name']); ?></p>
                                <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($row['mothers_name']); ?></p>
                                <p><strong>Year:</strong> <?php echo htmlspecialchars($row['year']); ?></p>
                                <p><strong>Gender:</strong> <?php echo htmlspecialchars($row['gender']); ?></p>
                                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($row['birth_date']); ?></p>
                                <p><strong>Mother Tongue:</strong> <?php echo htmlspecialchars($row['mother_tongue']); ?></p>
                                <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($row['blood_group']); ?></p>
                            </div>
                            <div class="col-6 text-end">
                                <div>
                                    <strong>Photo:</strong>
                                    <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" class="border mt-2" width="150" height="150" alt="Photo">
                                </div>
                                <div class="mt-3">
                                    <strong>Signature:</strong>
                                    <img src="uploads/<?php echo htmlspecialchars($row['signature']); ?>" class="border mt-2" width="150" height="100" alt="Signature">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                                <p><strong>Taluka:</strong> <?php echo htmlspecialchars($row['taluka']); ?></p>
                                <p><strong>District:</strong> <?php echo htmlspecialchars($row['district']); ?></p>
                            </div>
                            <div class="col-6">
                                <p><strong>State:</strong> <?php echo htmlspecialchars($row['state']); ?></p>
                                <p><strong>Country:</strong> <?php echo htmlspecialchars($row['country']); ?></p>
                                <p><strong>Pin Code:</strong> <?php echo htmlspecialchars($row['pincode']); ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($row['mobile']); ?></p>
                            </div>
                            <div class="col-6">
                                <p><strong>Parent's Mobile:</strong> <?php echo htmlspecialchars($row['parents_mobile']); ?></p>
                                <p><strong>Declaration:</strong> <?php echo $row['declaration'] ? 'Yes' : 'No'; ?></p>
                            </div>
                        </div>
                <?php
                    }
                } catch (PDOException $e) {
                    echo '<p class="text-danger text-center">Error fetching data: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
                <div class="text-center mt-4">
                    <!-- Back Button -->
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Back</button>
                </div>
                <!-- Print Form -->
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-warning" id="printbtn" onclick="window.print()">Print Form</button>
                </div>
            </div>
            <div class="col-sm-1"></div>
        </div>
    </div>
</body>

</html>