<?php
require_once("config.php"); // Include your database configuration file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submit'])) {
    try {
        // Collect and sanitize form data
        $semester = trim($_POST['semester']);
        $applicant_name = trim($_POST['applicant_name']);
        $mother_name = trim($_POST['mother_name']);
        $address = trim($_POST['address']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $contact_number = trim($_POST['contact_number']);
        $gender = trim($_POST['gender']);
        $declaration = isset($_POST['declaration']) ? 1 : 0;

        // Validate mandatory fields
        if (!$email) {
            throw new Exception("Invalid email format.");
        }
        if (!preg_match('/^\d{10}$/', $contact_number)) {
            throw new Exception("Invalid contact number. Must be 10 digits.");
        }

        // Check if the user has filled the admission form
        $admission_query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($admission_query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception("No admission form found with this email. Please complete the admission process first.");
        }
        $user_id = $user['id'];

        // Generate unique exam form number
        $exam_form_number = "EXAM" . date("Y") . $user_id . rand(1000, 9999);

        // Insert data into the exam_form table
        $sql = "INSERT INTO exam_form (
                    user_id, exam_form_number, semester, applicant_name, mother_name, 
                    address, email, contact_number, gender, declaration
                ) VALUES (
                    :user_id, :exam_form_number, :semester, :applicant_name, :mother_name, 
                    :address, :email, :contact_number, :gender, :declaration
                )";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':exam_form_number', $exam_form_number, PDO::PARAM_STR);
        $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
        $stmt->bindParam(':applicant_name', $applicant_name, PDO::PARAM_STR);
        $stmt->bindParam(':mother_name', $mother_name, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':contact_number', $contact_number, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':declaration', $declaration, PDO::PARAM_INT);
        $stmt->execute();

        $last_id = $db->lastInsertId();

        // Redirect on success
        if ($last_id) {
            header("Location: exam_preview.php?id=" . $exam_form_number);
            exit;
        } else {
            throw new Exception("Something went wrong. Could not save the form data.");
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
