<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B.Sc. Computer Science Exam Form</title>
    <style>
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

        body {
            background-color: var(--color-bg);
            color: var(--color-white);
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            padding: 20px;
        }

        .form-container {
            background-color: var(--color-bg1);
            padding: 30px;
            border-radius: 15px;
            max-width: 720px;
            margin: auto;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: var(--color-primary);
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            color: var(--color-light);
        }

        input,
        select,
        button {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }

        input,
        select {
            background-color: var(--color-bg2);
            color: var(--color-white);
        }

        input:focus,
        select:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--color-primary);
        }

        button {
            cursor: pointer;
            font-weight: bold;
        }

        button[type="submit"] {
            background-color: var(--color-primary);
            color: var(--color-white);
        }

        button[type="reset"] {
            background-color: var(--color-warning);
            color: var(--color-black);
        }

        button.back-btn {
            background-color: var(--color-danger);
            color: var(--color-white);
        }

        button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .checkbox-container input {
            width: auto;
            margin-right: 10px;
        }

        .checkbox-container label {
            margin: 0;
            color: var(--color-light);
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            h2 {
                font-size: 22px;
            }

            input,
            select,
            button {
                padding: 10px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .form-container {
                padding: 15px;
            }

            h2 {
                font-size: 20px;
            }

            input,
            select,
            button {
                padding: 8px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>B.Sc. Computer Science Exam Form</h2>
        <form action="examform_action.php" method="POST">
            <label for="semester">Select Semester:</label>
            <select name="semester" id="semester" required>
                <option value="">Select Semester</option>
                <option value="1">1st Semester</option>
                <option value="2">2nd Semester</option>
                <option value="3">3rd Semester</option>
                <option value="4">4th Semester</option>
                <option value="5">5th Semester</option>
                <option value="6">6th Semester</option>
            </select>

            <label for="applicant_name">Name of the Applicant:</label>
            <input type="text" name="applicant_name" id="applicant_name" required>

            <label for="mothers_name">Mother's Name:</label>
            <input type="text" name="mother_name" id="mother_name" required>

            <label for="address">Address for Communication:</label>
            <input type="text" name="address" id="address" required>

            <label for="email">Email ID:</label>
            <input type="email" name="email" id="email" required>

            <label for="contact_number">Contact Number:</label>
            <input type="text" name="contact_number" id="contact_number" required>

            <label for="gender">Gender:</label>
            <select name="gender" id="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <div class="checkbox-container">
                <input type="checkbox" name="declaration" id="declaration" required>
                <label for="declaration">I declare that the above information is true and correct to the best of my knowledge.</label>
            </div>

            <button type="submit" name="form_submit">Submit</button>
            <button type="reset">Clear</button>
            <button type="button" class="back-btn" onclick="history.back()">Back</button>
        </form>
    </div>
</body>

</html>