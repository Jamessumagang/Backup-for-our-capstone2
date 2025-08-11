<?php
include 'db.php';

$project_id = null;
$project_details = null;

// Check if project_id is provided via GET (for displaying the form)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $project_id = $_GET['id'];

    // Fetch project details
    $sql = "SELECT * FROM projects WHERE project_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $project_details = $result->fetch_assoc();
        } else {
            echo "Error: Project not found.";
        }
        $stmt->close();
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission for updating project
    $project_id = $_POST['project_id'];
    $project_name = $_POST['project_name'];
    $start_date = $_POST['start_date'];
    $deadline = $_POST['deadline'];
    $location = $_POST['location'];
    $project_cost = $_POST['project_cost'];
    $foreman = $_POST['foreman'];
    $project_type = $_POST['project_type'];
    $project_status = $_POST['project_status'];
    $project_divisions = $_POST['project_divisions'];

    // Handle image upload (similar logic as process_add_project.php)
    $image_path = $_POST['existing_image'] ?? null; // Keep existing image if no new one is uploaded
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
        $allowed_types = array('jpg' => 'image/jpg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png');
        $file_name = $_FILES['project_image']['name'];
        $file_type = $_FILES['project_image']['type'];
        $file_size = $_FILES['project_image']['size'];

        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (array_key_exists($ext, $allowed_types)) {
            $maxsize = 5 * 1024 * 1024;
            if ($file_size <= $maxsize && in_array($file_type, $allowed_types)) {
                 $new_file_name = uniqid() . '.' . $ext;
                 $upload_directory = 'uploads/';
                 $destination = $upload_directory . $new_file_name;

                 if (move_uploaded_file($_FILES['project_image']['tmp_name'], $destination)) {
                     $image_path = $destination;
                     // Optional: Delete old image if it exists
                     // if (!empty($_POST['existing_image']) && file_exists($_POST['existing_image'])) {
                     //     unlink($_POST['existing_image']);
                     // }
                 } else {
                     echo "Error uploading new image.";
                 }
            } else {
                echo "Error: File size or type is invalid.";
            }
        } else {
            echo "Error: Invalid file extension.";
        }
    }

    // Prepare an update statement
    $sql = "UPDATE projects SET project_name=?, start_date=?, deadline=?, location=?, project_cost=?, foreman=?, project_type=?, project_status=?, project_divisions=?, image_path=? WHERE project_id=?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("ssssdsssssi", $project_name, $start_date, $deadline, $location, $project_cost, $foreman, $project_type, $project_status, $project_divisions, $image_path, $project_id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Redirect back to project list or view page
            header("location: project_list.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Project</title>
    <!-- Include your CSS links and styles here -->
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: url('images/background.webp') no-repeat center center fixed, linear-gradient(135deg, #e0e7ff 0%, #f7fafc 100%);
            background-blend-mode: overlay;
            background-size: cover;
            margin: 0;
            min-height: 100vh;
        }
        .container {
            margin: 40px auto;
            max-width: 540px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(37,99,235,0.10), 0 1.5px 8px rgba(0,0,0,0.04);
            padding: 40px 32px 32px 32px;
            transition: box-shadow 0.2s;
        }
        .container:hover {
            box-shadow: 0 12px 40px rgba(37,99,235,0.13), 0 2px 12px rgba(0,0,0,0.06);
        }
        .header {
            font-size: 2.1em;
            font-weight: 700;
            margin-bottom: 28px;
            color: #2563eb;
            letter-spacing: 0.5px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 22px;
        }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            color: #2563eb;
            font-size: 1.08em;
        }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="file"],
        .form-group select {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e0e7ef;
            border-radius: 12px;
            box-sizing: border-box;
            font-size: 1.08em;
            background: #f7fafd;
            transition: border 0.2s, background 0.2s;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="file"]:focus,
        .form-group select:focus {
            border: 2px solid #2563eb;
            background: #fff;
        }
        .form-group img {
            margin-top: 10px;
            max-width: 220px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
        }
        .submit-btn {
            background: linear-gradient(90deg, #2563eb 0%, #4db3ff 100%);
            color: #fff;
            border: none;
            padding: 14px 36px;
            font-size: 1.1em;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
            display: block;
            margin: 0 auto;
        }
        .submit-btn:hover {
            background: linear-gradient(90deg, #1746a0 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.12);
        }
        @media (max-width: 700px) {
            .container {
                padding: 18px 5px 32px 5px;
            }
            .header {
                font-size: 1.4em;
            }
            .form-group label {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Edit Project</div>
        <?php if ($project_details): ?>
        <form action="edit_project.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?php echo $project_details['project_id']; ?>">
             <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($project_details['image_path']); ?>">
            <div class="form-group">
                <label for="project_name">Project Name:</label>
                <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($project_details['project_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($project_details['start_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="deadline">Deadline:</label>
                <input type="date" id="deadline" name="deadline" value="<?php echo htmlspecialchars($project_details['deadline']); ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($project_details['location']); ?>" required>
            </div>
            <div class="form-group">
                <label for="project_cost">Project Cost:</label>
                <input type="text" id="project_cost" name="project_cost" value="<?php echo htmlspecialchars($project_details['project_cost']); ?>" required>
            </div>
            <div class="form-group">
                <label for="foreman">Foreman:</label>
                <input type="text" id="foreman" name="foreman" value="<?php echo htmlspecialchars($project_details['foreman']); ?>" required>
            </div>
            <div class="form-group">
                <label for="project_type">Project Type:</label>
                <input type="text" id="project_type" name="project_type" value="<?php echo htmlspecialchars($project_details['project_type']); ?>" required>
            </div>
            <div class="form-group">
                <label for="project_status">Project Status:</label>
                <select id="project_status" name="project_status" required>
                    <option value="Ongoing" <?php if ($project_details['project_status'] === 'Ongoing') echo 'selected'; ?>>Ongoing</option>
                    <option value="Finished" <?php if ($project_details['project_status'] === 'Finished') echo 'selected'; ?>>Finished</option>
                    <option value="Cancelled" <?php if ($project_details['project_status'] === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <label for="project_divisions">Project Divisions:</label>
                <input type="text" id="project_divisions" name="project_divisions" value="<?php echo htmlspecialchars($project_details['project_divisions']); ?>" required>
            </div>
             <div class="form-group">
                <label for="project_image">Project Image:</label>
                <input type="file" id="project_image" name="project_image" accept="image/*">
                <?php if (isset($project_details['image_path']) && !empty($project_details['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($project_details['image_path']); ?>" alt="Project Image">
                <?php endif; ?>
            </div>
            <button type="submit" class="submit-btn">Update Project</button>
        </form>
        <?php else: ?>
            <p>Project not found.</p>
        <?php endif; ?>
    </div>
</body>
</html> 