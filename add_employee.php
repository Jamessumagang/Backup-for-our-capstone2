<?php
$conn = new mysqli("localhost", "root", "", "capstone_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $status = $_POST['status'];
    $position = $_POST['position'];

    // Handle photo upload
    $photo = 'uploads/default.png'; // Default photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
            $photo = $targetFile;
        }
    }

    $stmt = $conn->prepare("INSERT INTO employees (photo, lastname, firstname, middlename, birthday, gender, address, contact_no, status, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $photo, $lastname, $firstname, $middlename, $birthday, $gender, $address, $contact, $status, $position);
    $stmt->execute();
    header("Location: employee_list.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Employee</title>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', 'Montserrat', 'Inter', Arial, sans-serif;
            background: url('images/background.webp') no-repeat center center fixed, linear-gradient(135deg, #e0e7ff 0%, #f7fafc 100%);
            background-size: cover;
            position: relative;
        }
        .background-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw; height: 100vh;
            background: rgba(255,255,255,0.75);
            z-index: 0;
        }
        .form-outer {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        .form-container {
            max-width: 800px;
            width: 100%;
            margin: 120px auto 0 auto;
            border: none;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 32px rgba(37,99,235,0.10), 0 1.5px 8px rgba(0,0,0,0.04);
            padding: 40px 48px 80px 48px;
            box-sizing: border-box;
            position: relative;
        }
        .form-container h2 {
            margin-top: 0;
            font-size: 2em;
            margin-bottom: 30px;
            color: #2563eb;
            text-align: center;
            letter-spacing: 1px;
        }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 28px;
        }
        .form-group label {
            flex: 0 0 180px;
            font-size: 1.15em;
            margin-right: 10px;
            color: #2563eb;
            font-weight: 600;
        }
        .form-group input[type="text"],
        .form-group input[type="date"] {
            flex: 1;
            padding: 14px 18px;
            font-size: 1.1em;
            border: 2px solid #e0e7ef;
            border-radius: 12px;
            outline: none;
            transition: border 0.2s;
            background: #f7fafd;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus {
            border: 2px solid #2563eb;
            background: #fff;
        }
        .form-group input[type="file"] {
            flex: 1;
            font-size: 1.1em;
        }
        .form-actions {
            position: absolute;
            bottom: 30px;
            right: 40px;
            display: flex;
            gap: 20px;
        }
        .form-actions button,
        .form-actions a {
            background: linear-gradient(90deg, #2563eb 0%, #4db3ff 100%);
            color: white;
            padding: 12px 50px;
            border: none;
            border-radius: 6px;
            font-size: 1.2em;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, box-shadow 0.2s;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
        }
        .form-actions button:hover,
        .form-actions a:hover {
            background: linear-gradient(90deg, #1746a0 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.12);
        }
        @media (max-width: 700px) {
            .form-container {
                padding: 10px 5px 70px 5px;
            }
            .form-group label {
                font-size: 1em;
                flex: 0 0 100px;
            }
            .form-actions {
                right: 10px;
                bottom: 10px;
            }
            .form-actions button,
            .form-actions a {
                padding: 10px 20px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="background-overlay"></div>
    <div class="form-outer">
        <form class="form-container" method="POST" action="add_employee.php" enctype="multipart/form-data">
            <h2>New Employee</h2>
            <div class="form-group">
                <label for="lastname">Lastname :</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>
            <div class="form-group">
                <label for="firstname">Firstname :</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>
            <div class="form-group">
                <label for="middlename">Middlename :</label>
                <input type="text" id="middlename" name="middlename">
            </div>
            <div class="form-group">
                <label for="birthday">Birthday :</label>
                <input type="date" id="birthday" name="birthday" required onchange="calculateAge()">
            </div>
            <div class="form-group">
                <label for="age">Age :</label>
                <input type="text" id="age" name="age">
            </div>
            <div class="form-group">
                <label for="gender">Gender :</label>
                <input type="text" id="gender" name="gender" required>
            </div>
            <div class="form-group">
                <label for="address">Address :</label>
                <input type="text" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact no :</label>
                <input type="text" id="contact" name="contact" required>
            </div>
            <div class="form-group">
                <label for="status">Status :</label>
                <select id="status" name="status" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="position">Position :</label>
                <input type="text" id="position" name="position" required>
            </div>
            <div class="form-group">
                <label for="photo">Photo :</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>
            <div class="form-actions">
                <button type="submit">Save</button>
                <a href="employee_list.php">Close</a>
            </div>
        </form>
    </div>
    <script>
        function calculateAge() {
            var birthday = document.getElementById('birthday').value;
            if (birthday) {
                var birthDate = new Date(birthday);
                var today = new Date();
                var age = today.getFullYear() - birthDate.getFullYear();
                var m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                document.getElementById('age').value = age;
            } else {
                document.getElementById('age').value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            calculateAge();
            document.getElementById('birthday').addEventListener('change', calculateAge);
        });
    </script>
</body>
</html>
