<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $project_name = $_POST['project_name'];
    $start_date = $_POST['start_date'];
    $deadline = $_POST['deadline'];
    $location = $_POST['location'];
    $project_cost = $_POST['project_cost'];
    $foreman = $_POST['foreman'];
    $project_type = $_POST['project_type'];
    $project_status = $_POST['project_status'];
    $project_divisions = $_POST['project_divisions'];

    // Handle file upload
    $image_path = null;
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
        $allowed_types = array('jpg' => 'image/jpg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png');
        $file_name = $_FILES['project_image']['name'];
        $file_type = $_FILES['project_image']['type'];
        $file_size = $_FILES['project_image']['size'];

        // Verify file extension
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed_types)) die("Error: Please select a valid file format.");

        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($file_size > $maxsize) die("Error: File size is larger than the allowed limit.");

        // Verify MIME type of the file
        if (in_array($file_type, $allowed_types)) {
            // Upload file to server
            // Using a unique name to prevent overwriting files
            $new_file_name = uniqid() . '.' . $ext;
            $upload_directory = 'uploads/'; // Make sure this directory exists and is writable
            $destination = $upload_directory . $new_file_name;

            if (move_uploaded_file($_FILES['project_image']['tmp_name'], $destination)) {
                $image_path = $destination;
            } else {
                echo "Error: There was a problem uploading your file. Please try again.";
            }
        } else {
            echo "Error: Invalid file type.";
        }
    }

    // Prepare and bind SQL statement
    // Make sure the column names match your database table
    $sql = "INSERT INTO projects (project_name, start_date, deadline, location, project_cost, foreman, project_type, project_status, project_divisions, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssdsssss", $project_name, $start_date, $deadline, $location, $project_cost, $foreman, $project_type, $project_status, $project_divisions, $image_path);

        if ($stmt->execute()) {
            // Redirect back to project list page after successful insertion
            header("Location: project_list.php");
            exit();
        } else {
            echo "Error: Could not execute query: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Could not prepare statement: " . $conn->error;
    }

    $conn->close();
}
?> 