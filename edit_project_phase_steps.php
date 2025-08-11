<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$message = '';
$project_id = null;
$division_name = '';
$steps = [];
$project_name = '';

// Get project ID and division name from URL
if (isset($_GET['project_id']) && isset($_GET['division_name'])) {
    $project_id = $_GET['project_id'];
    $division_name = urldecode($_GET['division_name']);

    // Fetch project name for display
    $stmt = $conn->prepare("SELECT project_name FROM projects WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $project_name = $result->fetch_assoc()['project_name'];
    }
    $stmt->close();

    // Fetch existing steps for this project and division
    $stmt = $conn->prepare("SELECT * FROM project_phase_steps WHERE project_id = ? AND division_name = ? ORDER BY step_order ASC");
    $stmt->bind_param("is", $project_id, $division_name);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Ensure each step entry is properly structured with an array for image_path
        if (!isset($steps[$row['step_order'] - 1])) {
            $steps[$row['step_order'] - 1] = [
                'step_id' => $row['step_id'],
                'step_description' => $row['step_description'],
                'is_finished' => $row['is_finished'],
                'step_order' => $row['step_order'],
                'image_path' => [] // Initialize image_path as an empty array
            ];
        }
        // Add image path to the specific step's image_path array
        if (!empty($row['image_path'])) {
            $steps[$row['step_order'] - 1]['image_path'][] = $row['image_path'];
        }
    }
    $stmt->close();

    // If no steps exist, create 10 empty ones for initial input
    if (empty($steps)) {
        for ($i = 1; $i <= 10; $i++) {
            $steps[] = [
                'step_id' => null,
                'step_description' => '',
                'is_finished' => false,
                'step_order' => $i,
                'image_path' => [] // Also initialize image_path as an array for empty steps
            ];
        }
    } else {
        // If existing steps are loaded, ensure we always have 10 steps for the form
        while (count($steps) < 10) {
            $steps[] = [
                'step_id' => null,
                'step_description' => '',
                'is_finished' => false,
                'step_order' => count($steps) + 1,
                'image_path' => []
            ];
        }
    }

} else {
    $message = "<div class=\"alert error\"><i class=\"fa fa-times-circle\"></i> Project ID or Division Name not provided.</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['project_id']) && isset($_POST['division_name'])) {
    $project_id = $_POST['project_id'];
    $division_name = $_POST['division_name'];

    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/step_images/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Get existing steps data to preserve images and descriptions correctly
    $existing_data_for_all_steps = [];
    $stmt = $conn->prepare("SELECT * FROM project_phase_steps WHERE project_id = ? AND division_name = ? ORDER BY step_order ASC");
    $stmt->bind_param("is", $project_id, $division_name);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!isset($existing_data_for_all_steps[$row['step_order']])) {
            $existing_data_for_all_steps[$row['step_order']] = [
                'step_id' => $row['step_id'],
                'step_description' => $row['step_description'],
                'is_finished' => $row['is_finished'],
                'step_order' => $row['step_order'],
                'image_path' => []
            ];
        }
        if (!empty($row['image_path'])) {
            $existing_data_for_all_steps[$row['step_order']]['image_path'][] = $row['image_path'];
        }
    }
    $stmt->close();

    $insert_success = true;
    for ($i = 1; $i <= 10; $i++) {
        $step_description = $_POST['step_' . $i];
        $is_finished = isset($_POST['finished_' . $i]) ? 1 : 0;

        $current_step_new_image_paths = [];

        // --- Handle new file uploads for this specific step ---
        if (isset($_FILES['step_image_' . $i]) && is_array($_FILES['step_image_' . $i]['name'])) {
            foreach ($_FILES['step_image_' . $i]['name'] as $key => $filename) {
                if ($_FILES['step_image_' . $i]['error'][$key] == 0) {
                    $file = [
                        'name' => $_FILES['step_image_' . $i]['name'][$key],
                        'type' => $_FILES['step_image_' . $i]['type'][$key],
                        'tmp_name' => $_FILES['step_image_' . $i]['tmp_name'][$key],
                        'error' => $_FILES['step_image_' . $i]['error'][$key],
                        'size' => $_FILES['step_image_' . $i]['size'][$key]
                    ];

                    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = 'step_' . $project_id . '_' . $division_name . '_' . $i . '_' . time() . '_' . $key . '.' . $file_extension;
                        $target_path = $upload_dir . $new_filename;

                        if (move_uploaded_file($file['tmp_name'], $target_path)) {
                            $current_step_new_image_paths[] = $target_path;
                        }
                    }
                }
            }
        }

        // --- Determine the final set of image paths for this step (existing + new uploads) ---
        $existing_images_from_db = $existing_data_for_all_steps[$i]['image_path'] ?? [];
        $final_image_paths_for_this_step = array_merge($existing_images_from_db, $current_step_new_image_paths);

        // --- Handle images marked for deletion from the frontend ---
        $images_to_delete_from_frontend = $_POST['delete_image_path_' . $i] ?? [];
        foreach ($images_to_delete_from_frontend as $deleted_path) {
            // Remove from the final list of images to be saved
            $key_in_final_list = array_search($deleted_path, $final_image_paths_for_this_step);
            if ($key_in_final_list !== false) {
                unset($final_image_paths_for_this_step[$key_in_final_list]);
            }
            // Also delete from filesystem if it exists
            if (file_exists($deleted_path)) {
                unlink($deleted_path);
            }
        }
        $final_image_paths_for_this_step = array_values($final_image_paths_for_this_step); // Re-index array

        // --- Delete old image files from filesystem ONLY if the step is effectively being cleared ---
        // This means, if the step description is empty AND there are no images (either existing or newly uploaded)
        if (empty($step_description) && empty($final_image_paths_for_this_step)) {
            foreach ($existing_images_from_db as $old_image_path) {
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        }

        // --- Delete existing records for *this specific step* from the database ---
        $stmt_delete_current_step_records = $conn->prepare("DELETE FROM project_phase_steps WHERE project_id = ? AND division_name = ? AND step_order = ?");
        $stmt_delete_current_step_records->bind_param("isi", $project_id, $division_name, $i);
        $stmt_delete_current_step_records->execute();
        $stmt_delete_current_step_records->close();

        // --- Insert the determined records for *this specific step* ---
        if (!empty($step_description) || !empty($final_image_paths_for_this_step)) {
            if (empty($final_image_paths_for_this_step) && !empty($step_description)) {
                // Insert step with description but no images
            $stmt_insert = $conn->prepare("INSERT INTO project_phase_steps (project_id, division_name, step_description, is_finished, step_order, image_path) VALUES (?, ?, ?, ?, ?, '')");
            $stmt_insert->bind_param("issii", $project_id, $division_name, $step_description, $is_finished, $i);
                if (!$stmt_insert->execute()) {
                    $message = "<div class=\"alert error\"><i class=\"fa fa-times-circle\"></i> Error saving step " . $i . ": " . $stmt_insert->error . "</div>";
                    $insert_success = false;
                }
                $stmt_insert->close();
            } else if (!empty($final_image_paths_for_this_step)) {
                // Insert step with images (and description)
                foreach ($final_image_paths_for_this_step as $image_path) {
                    $stmt_insert = $conn->prepare("INSERT INTO project_phase_steps (project_id, division_name, step_description, is_finished, step_order, image_path) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_insert->bind_param("issiis", $project_id, $division_name, $step_description, $is_finished, $i, $image_path);
            if (!$stmt_insert->execute()) {
                $message = "<div class=\"alert error\"><i class=\"fa fa-times-circle\"></i> Error saving step " . $i . ": " . $stmt_insert->error . "</div>";
                $insert_success = false;
                break;
            }
            $stmt_insert->close();
                }
            }
        }
    }

    if ($insert_success) {
        $message = "<div class=\"alert success\"><i class=\"fa fa-check-circle\"></i> Project phase steps updated successfully!</div>";
        // Refresh data to show latest changes (re-fetch to update $steps array on the page)
        $steps = [];
        $stmt = $conn->prepare("SELECT * FROM project_phase_steps WHERE project_id = ? AND division_name = ? ORDER BY step_order ASC");
        $stmt->bind_param("is", $project_id, $division_name);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (!isset($steps[$row['step_order'] - 1])) {
                $steps[$row['step_order'] - 1] = [
                    'step_id' => $row['step_id'],
                    'step_description' => $row['step_description'],
                    'is_finished' => $row['is_finished'],
                    'step_order' => $row['step_order'],
                    'image_path' => []
                ];
            }
            if (!empty($row['image_path'])) {
                $steps[$row['step_order'] - 1]['image_path'][] = $row['image_path'];
            }
        }
        $stmt->close();

        // If less than 10 steps were saved, pad with empty ones
        while (count($steps) < 10) {
            $steps[] = [
                'step_id' => null,
                'step_description' => '',
                'is_finished' => false,
                'step_order' => count($steps) + 1,
                'image_path' => []
            ];
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Project Phase Steps - <?php echo htmlspecialchars($division_name); ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modern CSS Reset */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        /* Base Styles */
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.9);
        }

        .logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .icon {
            font-size: 1.25rem;
            color: var(--primary);
            transition: all 0.2s ease;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
        }

        .icon:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-dark);
        }

        .logout-btn {
            background: var(--primary);
            padding: 0.625rem 1.25rem;
            color: white;
            font-weight: 500;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Container */
        .container {
            display: flex;
            min-height: calc(100vh - 4rem);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--surface);
            box-shadow: var(--shadow);
            padding: 1.5rem 0;
            position: relative;
            transition: all 0.3s ease;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar li {
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: var(--text);
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0.25rem 0;
            border-radius: 0.5rem;
            margin: 0 0.75rem;
        }

        .sidebar li:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .sidebar li i {
            margin-right: 0.75rem;
            font-size: 1.125rem;
            width: 1.5rem;
            text-align: center;
        }

        .sidebar a {
            color: inherit;
            text-decoration: none;
            width: 100%;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        /* Dropdown */
        .sidebar .dropdown .arrow {
            margin-left: auto;
            transition: transform 0.2s ease;
        }

        .sidebar .dropdown.active .arrow {
            transform: rotate(180deg);
        }

        .sidebar .dropdown-menu {
            display: none;
            background-color: rgba(37, 99, 235, 0.05);
            margin: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            overflow: hidden;
            padding: 0.5rem;
        }

        .sidebar .dropdown.active .dropdown-menu {
            display: block;
        }

        .sidebar .dropdown-menu li {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            color: var(--text);
            margin: 0.25rem 0;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .sidebar .dropdown-menu li:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .sidebar .dropdown-menu li a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: inherit;
            text-decoration: none;
            width: 100%;
        }

        .sidebar .dropdown-menu li a i {
            font-size: 1rem;
            width: 1.25rem;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: var(--background);
        }

        .main-content h2 {
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        /* Form Styles */
        .form-container {
            background: var(--surface);
            border-radius: 1rem;
            box-shadow: var(--shadow);
            padding: 2rem;
            width: 90%;
            max-width: 1200px;
            margin: 2rem auto;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-group label {
            flex: 1;
            color: var(--text);
            font-weight: 500;
        }

        .form-group input[type="text"] {
            flex: 3;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 1rem;
            color: var(--text);
            background: var(--background);
            transition: border-color 0.2s ease;
        }

        .form-group input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .form-group input[type="checkbox"] {
            transform: scale(1.2);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .alert.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }

        .alert.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .alert.warning {
            background-color: #fffbeb;
            color: #9a3412;
            border: 1px solid #f59e0b;
        }

        .alert i {
            font-size: 1.25rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }

        /* Add these styles to the existing CSS */
        .image-upload {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .image-upload-btn {
            background: #87CEEB;
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            white-space: nowrap;
            margin-bottom: 1rem;
        }

        .image-upload-btn:hover {
            background: #4682B4;
            color: white;
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            min-height: 200px;
        }

        .image-preview {
            position: relative;
            width: 200px;
            height: 200px;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            overflow: hidden;
            background: #f8f9fa;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-number {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }

        /* Remove delete button styles */
        .delete-image-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(239, 68, 68, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .image-preview:hover .delete-image-btn {
            opacity: 1;
        }

        .delete-image-btn:hover {
            background: rgba(239, 68, 68, 1);
        }

        .no-image {
            color: #666;
            font-size: 0.9rem;
            text-align: center;
            padding: 1rem;
            border: 2px dashed #ccc;
            border-radius: 0.5rem;
            width: 200px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">RS BUILDERS PMS</div>
        <div class="header-right">
            <span class="icon"><i class="fa-regular fa-comments"></i></span>
            <a href="logout.php" class="logout-btn">Log-out</a>
        </div>
    </div>
    <div class="container">
        <nav class="sidebar">
            <ul>
                <li><a href="dashboard.php"><i class="fa fa-home"></i> DASHBOARD</a></li>
                <li><a href="employee_list.php"><i class="fa fa-users"></i> Employee List</a></li>
                <li><a href="project_list.php"><i class="fa fa-list"></i> Project List</a></li>
                <li><a href="user_list.php"><i class="fa fa-user"></i> Users</a></li>
                <li class="dropdown active">
                    <a href="#"><i class="fa fa-wrench"></i> Maintenance <span class="arrow">&#9662;</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="position.php"><i class="fa fa-user-tie"></i> Position</a></li>
                        <li class="active"><a href="project_division.php"><i class="fa fa-sitemap"></i> Project Division</a></li>
                        <li><a href="project_team.php"><i class="fa fa-users-cog"></i> Project Team</a></li>
                    </ul>
                </li>
                <li><a href="#"><i class="fa fa-money-bill"></i> Payroll</a></li>
            </ul>
        </nav>
        <main class="main-content">
            <div class="form-container">
                <h2>Edit Steps for <?php echo htmlspecialchars($division_name); ?> (Project: <?php echo htmlspecialchars($project_name); ?>)</h2>
                <?php echo $message; ?>
                <?php if ($project_id && $division_name): ?>
                    <form action="edit_project_phase_steps.php?project_id=<?php echo $project_id; ?>&division_name=<?php echo urlencode($division_name); ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project_id); ?>">
                        <input type="hidden" name="division_name" value="<?php echo htmlspecialchars($division_name); ?>">
                        
                        <?php for ($i = 0; $i < 10; $i++): ?>
                            <div class="form-group">
                                <label for="step_<?php echo $i + 1; ?>">Step <?php echo $i + 1; ?>:</label>
                                <input type="text" id="step_<?php echo $i + 1; ?>" name="step_<?php echo $i + 1; ?>" value="<?php echo htmlspecialchars($steps[$i]['step_description'] ?? ''); ?>">
                                <input type="checkbox" name="finished_<?php echo $i + 1; ?>" <?php echo ($steps[$i]['is_finished'] ?? false) ? 'checked' : ''; ?>> Finished
                                <div class="image-upload">
                                    <label for="step_image_<?php echo $i + 1; ?>" class="image-upload-btn">
                                        <i class="fa fa-image"></i> Upload Images for Step <?php echo $i + 1; ?>
                                    </label>
                                    <input type="file" id="step_image_<?php echo $i + 1; ?>" name="step_image_<?php echo $i + 1; ?>[]" accept="image/*" multiple style="display: none;">
                                    <div class="image-preview-container" data-step="<?php echo $i + 1; ?>">
                                        <?php if (!empty($steps[$i]['image_path'])): ?>
                                            <?php 
                                            $images = is_array($steps[$i]['image_path']) ? $steps[$i]['image_path'] : [$steps[$i]['image_path']];
                                            foreach ($images as $index => $image): 
                                            ?>
                                                <div class="image-preview" data-image="<?php echo htmlspecialchars($image); ?>">
                                                    <span class="image-number"><?php echo $index + 1; ?></span>
                                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Step <?php echo $i + 1; ?> image <?php echo $index + 1; ?>">
                                                    <button type="button" class="delete-image-btn" data-step="<?php echo $i + 1; ?>" data-index="<?php echo $index; ?>" title="Delete image">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="no-image">No images selected</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>

                        <div class="form-actions">
                            <a href="view_project.php?id=<?php echo $project_id; ?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Project</a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Steps</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p>Could not load project phase steps. Please ensure project ID and division name are provided.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <!-- Remove Carousel Modal -->
    <div class="carousel-modal">
        <div class="carousel-content">
            <button class="carousel-close"><i class="fa fa-times"></i></button>
            <button class="carousel-nav carousel-prev"><i class="fa fa-chevron-left"></i></button>
            <img class="carousel-image" src="" alt="Carousel image">
            <button class="carousel-nav carousel-next"><i class="fa fa-chevron-right"></i></button>
            <div class="carousel-counter"></div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.sidebar .dropdown');
            
            dropdowns.forEach(dropdown => {
                const dropdownLink = dropdown.querySelector('a');
                
                dropdownLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    dropdowns.forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.classList.remove('active');
                        }
                    });
                    
                    dropdown.classList.toggle('active');
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('active');
                    });
                }
            });

            // Image upload handling
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const step = input.id.split('_')[2];
                    const container = document.querySelector(`.image-preview-container[data-step="${step}"]`);
                    
                    if (this.files && this.files.length > 0) {
                        // Remove "No images selected" message if it exists
                        const noImage = container.querySelector('.no-image');
                        if (noImage) {
                            noImage.remove();
                        }
                        
                        // Get current number of images in this step
                        const currentImageCount = container.querySelectorAll('.image-preview').length;
                        
                        // Clear existing images if this is a new upload
                        if (currentImageCount === 0) {
                        container.innerHTML = '';
                        }
                        
                        Array.from(this.files).forEach((file, index) => {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const preview = document.createElement('div');
                                preview.className = 'image-preview';
                                preview.setAttribute('data-step', step);
                                preview.setAttribute('data-index', currentImageCount + index);
                                
                                // Add image number
                                const imageNumber = document.createElement('span');
                                imageNumber.className = 'image-number';
                                imageNumber.textContent = currentImageCount + index + 1;
                                preview.appendChild(imageNumber);
                                
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                preview.appendChild(img);
                                
                                const deleteBtn = document.createElement('button');
                                deleteBtn.className = 'delete-image-btn';
                                deleteBtn.innerHTML = '<i class="fa fa-times"></i>';
                                deleteBtn.setAttribute('data-step', step);
                                deleteBtn.setAttribute('data-index', currentImageCount + index);
                                preview.appendChild(deleteBtn);
                                
                                container.appendChild(preview);
                            }
                            reader.readAsDataURL(file);
                        });
                    }
                });
            });

            // Delete image functionality
            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-image-btn')) {
                    const btn = e.target.closest('.delete-image-btn');
                    const step = btn.getAttribute('data-step');
                    const imagePath = btn.closest('.image-preview').getAttribute('data-image'); // Get the current image path
                    const container = document.querySelector(`.image-preview-container[data-step="${step}"]`);
                    
                    // Remove the image preview from the DOM
                    btn.closest('.image-preview').remove();
                    
                    // Update image numbers for this step only
                    container.querySelectorAll('.image-preview').forEach((preview, idx) => {
                        preview.querySelector('.image-number').textContent = idx + 1;
                        // Update data-index if needed (for consistency, though not strictly used by PHP for existing images)
                        // preview.setAttribute('data-index', idx);
                    });
                    
                    // If no images left, show "No images selected"
                    if (container.children.length === 0) {
                        container.innerHTML = '<div class="no-image">No images selected</div>';
                    }
                    
                    // Add a hidden input to mark the image for deletion on form submission
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `delete_image_path_${step}[]`; // Use an array to store multiple deleted paths per step
                    hiddenInput.value = imagePath; // Store the actual image path
                    container.appendChild(hiddenInput); // Append to the container or form
                }
            });
        });
    </script>
</body>
</html> 