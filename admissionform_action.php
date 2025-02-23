<?php
require_once("config.php"); // Include your database configuration file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submit'])) {
    try {
        // Collect and sanitize form data
        $college = trim($_POST['college']);
        $year = trim($_POST['year']);
        $full_name = trim($_POST['full_name']);
        $mothers_name = trim($_POST['mothers_name']);
        $location = trim($_POST['location']);
        $taluka = trim($_POST['taluka']);
        $district = trim($_POST['district']);
        $state = trim($_POST['state']);
        $country = trim($_POST['country']);
        $pincode = trim($_POST['pincode']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $mobile = trim($_POST['mobile']);
        $parents_mobile = trim($_POST['parents_mobile']);
        $gender = trim($_POST['gender']);
        $birth_date = trim($_POST['birth_date']);
        $mother_tongue = trim($_POST['mother_tongue']);
        $blood_group = trim($_POST['blood_group']);
        $declaration = isset($_POST['declaration']) ? 1 : 0;

        // Validate mandatory fields
        if (!$email) {
            throw new Exception("Invalid email format.");
        }
        if (!preg_match('/^\d{10}$/', $mobile)) {
            throw new Exception("Invalid mobile number. Must be 10 digits.");
        }
        if (!preg_match('/^\d{10}$/', $parents_mobile)) {
            throw new Exception("Invalid parent's mobile number. Must be 10 digits.");
        }

        // Directory for uploads
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Handle photo upload
        $photo = '';
        if (isset($_FILES['photo']['tmp_name']) && $_FILES['photo']['tmp_name'] !== '') {
            $photo_file = $_FILES['photo']['name'];
            $file = $_FILES['photo']['tmp_name'];
            $extension = pathinfo($photo_file, PATHINFO_EXTENSION);
            $photo = 'photo_' . uniqid() . '.' . $extension;

            if (!move_uploaded_file($file, $uploadDir . $photo)) {
                throw new Exception("Failed to upload photo.");
            }
        }

        // Handle signature upload
        $signature = '';
        if (isset($_FILES['signature']['tmp_name']) && $_FILES['signature']['tmp_name'] !== '') {
            $signature_file = $_FILES['signature']['name'];
            $sfile = $_FILES['signature']['tmp_name'];
            $extension = pathinfo($signature_file, PATHINFO_EXTENSION);
            $signature = 'sign_' . uniqid() . '.' . $extension;

            if (!move_uploaded_file($sfile, $uploadDir . $signature)) {
                throw new Exception("Failed to upload signature.");
            }
        }

        // Fetch college_id based on college name
        $college_query = "SELECT college_id FROM colleges WHERE college_name = :college";
        $stmt = $db->prepare($college_query);
        $stmt->bindParam(':college', $college, PDO::PARAM_STR);
        $stmt->execute();
        $college_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$college_row) {
            throw new Exception("College not found.");
        }
        $college_id = $college_row['college_id'];

        // Fetch user_id based on email or mobile (assuming email is unique)
        $user_query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($user_query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_row) {
            throw new Exception("User not found.");
        }
        $user_id = $user_row['id'];

        // Generate unique admission form number
        $admission_form_number = "ADM" . date("Y") . $college_id . $user_id . rand(1000, 9999);

        // Insert data into the admission_form table
        $sql = "INSERT INTO admission_form (
                    admission_form_number, user_id, college_id, year, full_name, mothers_name, 
                    location, taluka, district, state, country, pincode, email, mobile, 
                    parents_mobile, gender, birth_date, mother_tongue, blood_group, 
                    photo, signature, declaration
                ) VALUES (
                    :admission_form_number, :user_id, :college_id, :year, :full_name, :mothers_name, 
                    :location, :taluka, :district, :state, :country, :pincode, :email, :mobile, 
                    :parents_mobile, :gender, :birth_date, :mother_tongue, :blood_group, 
                    :photo, :signature, :declaration
                )";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':admission_form_number', $admission_form_number, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_STR);
        $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
        $stmt->bindParam(':mothers_name', $mothers_name, PDO::PARAM_STR);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);
        $stmt->bindParam(':taluka', $taluka, PDO::PARAM_STR);
        $stmt->bindParam(':district', $district, PDO::PARAM_STR);
        $stmt->bindParam(':state', $state, PDO::PARAM_STR);
        $stmt->bindParam(':country', $country, PDO::PARAM_STR);
        $stmt->bindParam(':pincode', $pincode, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $stmt->bindParam(':parents_mobile', $parents_mobile, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':birth_date', $birth_date, PDO::PARAM_STR);
        $stmt->bindParam(':mother_tongue', $mother_tongue, PDO::PARAM_STR);
        $stmt->bindParam(':blood_group', $blood_group, PDO::PARAM_STR);
        $stmt->bindParam(':photo', $photo, PDO::PARAM_STR);
        $stmt->bindParam(':signature', $signature, PDO::PARAM_STR);
        $stmt->bindParam(':declaration', $declaration, PDO::PARAM_INT);
        $stmt->execute();

        $last_id = $db->lastInsertId();

        // Redirect on success
        if ($last_id) {
            header("Location: admission_preview.php?id=" . $admission_form_number);
            exit;
        } else {
            throw new Exception("Something went wrong. Could not save the form data.");
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>