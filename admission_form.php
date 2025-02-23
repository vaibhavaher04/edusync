<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B.Sc. Computer Science Admission Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6c63ff;
            --success: #00bf8e;
            --light-bg: #2e3267;
            --dark-bg: #1f2641;
            --accent: #424890;
            --text-light: #ffffff;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Arial', sans-serif;
        }

        .form-container {
            background-color: var(--light-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px var(--accent);
            max-width: 800px;
            margin: auto;
        }

        .form-control,
        .form-select {
            background-color: var(--accent);
            color: var(--text-light);
            border: none;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            box-shadow: 0 0 10px var(--success);
        }

        .btn-primary {
            background-color: var(--primary);
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--success);
        }

        .btn-secondary {
            background-color: var(--accent);
            border: none;
        }

        .btn-secondary:hover {
            background-color: var(--primary);
        }

        h1 {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .form-control,
            .form-select {
                font-size: 14px;
                padding: 10px;
            }

            h1 {
                font-size: 22px;
            }

            .mb-3 label {
                font-size: 14px;
            }

            button.btn-primary {
                width: 100%;
                font-size: 16px;
                padding: 12px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 20px;
            }

            .form-control,
            .form-select {
                font-size: 13px;
                padding: 8px;
            }

            button.btn-primary {
                width: 100%;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="text-center mb-4">
            <h1>Admission Form 2025 - B.Sc. Computer Science</h1>
        </div>

        <div class="form-container">
            <form action="admissionform_action.php" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="college" class="form-label">Select College</label>
                        <select name="college" class="form-select" required>
                            <option value="">Select College</option>
                            <option value="New Arts Commerce And Science College, Shevgaon">New Arts Commerce And Science College, Shevgaon</option>
                            <option value="Nirmalatai Kakade Arts, Commerce and Science College, Shevgaon">Nirmalatai Kakade Arts, Commerce and Science College, Shevgaon</option>
                            <option value="Dr. Balasaheb Vikhe Patil College, Shevgaon">Dr. Balasaheb Vikhe Patil College, Shevgaon</option>
                            <option value="Dada Patil Rajale Arts and Science College, Pathardi">Dada Patil Rajale Arts and Science College, Pathardi</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="year" class="form-label">Select Year</label>
                        <select name="year" class="form-select" required>
                            <option value="">Select Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mother's Name</label>
                        <input type="text" name="mothers_name" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="location" class="form-control mb-2" placeholder="Location" required>
                        <input type="text" name="taluka" class="form-control mb-2" placeholder="Taluka" required>
                        <input type="text" name="district" class="form-control mb-2" placeholder="District" required>
                        <input type="text" name="state" class="form-control mb-2" placeholder="State" required>
                        <input type="text" name="country" class="form-control mb-2" placeholder="Country" required>
                        <input type="text" name="pincode" class="form-control" placeholder="Pin Code" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email ID</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" name="mobile" class="form-control" maxlength="10" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Parent's Mobile Number</label>
                        <input type="text" name="parents_mobile" class="form-control" maxlength="10" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mother Tongue</label>
                        <input type="text" name="mother_tongue" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Blood Group</label>
                        <input type="text" name="blood_group" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Upload Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Upload Signature</label>
                        <input type="file" name="signature" class="form-control" accept="image/*" required>
                    </div>

                    <div class="col-12 form-check">
                        <input type="checkbox" class="form-check-input" name="declaration" required>
                        <label class="form-check-label">I declare that the above information is true and correct to the best of my knowledge.</label>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-secondary mt-3" onclick="window.history.back();">Back</button>
                        <button type="reset" class="btn btn-secondary mt-3">Clear</button>
                        <button type="submit" class="btn btn-primary mt-3" name="form_submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>

</html>