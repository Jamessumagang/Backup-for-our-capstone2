<?php
include 'db.php';

$project_id = null;
$project_details = null;
$divisions_display = []; // This will hold the consolidated divisions for display
$division_progress = [];

// Get project ID from URL
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];

    // Fetch project details
    $sql = "SELECT project_id, project_name, project_divisions FROM projects WHERE project_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $project_details = $result->fetch_assoc();
            
            // Process project_divisions string to consolidate and standardize division names for display
            $raw_divisions = array_map('trim', explode(',', $project_details['project_divisions']));
            $processed_divisions_temp = [];

            foreach ($raw_divisions as $div) {
                // Normalize "Phase" and "1" (if standalone) or "Phase 1" into "Phase 1"
                if (trim($div) === 'Phase' || trim($div) === '1' || trim($div) === 'Phase 1') {
                    $processed_divisions_temp[] = 'Phase 1';
                } else {
                    $processed_divisions_temp[] = $div;
                }
            }
            $divisions_display = array_values(array_unique($processed_divisions_temp)); // Ensure uniqueness and re-index
            sort($divisions_display); // Keep sorted order (e.g., 'Phase 1', 'Phase 2', etc.)

            // Initialize division_progress with default values for display divisions
            foreach ($divisions_display as $div) {
                $division_progress[$div] = ['progress' => 0, 'date_updated' => ''];
            }
        }
        $stmt->close();
    } else {
        echo "Error: Could not prepare statement: " . $conn->error;
    }

    // Fetch existing progress for divisions (raw, as they are in DB)
    $raw_db_progress_data = [];
    $sql = "SELECT division_name, progress_percentage, date_updated FROM project_progress WHERE project_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $raw_db_progress_data[trim($row['division_name'])] = [
                'progress' => $row['progress_percentage'],
                'date_updated' => $row['date_updated']
            ];
        }
        $stmt->close();
    } else {
        echo "Error: Could not prepare statement: " . $conn->error;
    }

    // Map raw progress data to consolidated display divisions
    foreach ($divisions_display as $canonical_div_name) {
        if ($canonical_div_name === 'Phase 1') {
            // Prioritize: check 'Phase 1' first, then 'Phase', then '1'
            if (isset($raw_db_progress_data['Phase 1'])) {
                $division_progress['Phase 1'] = $raw_db_progress_data['Phase 1'];
            } elseif (isset($raw_db_progress_data['Phase'])) {
                $division_progress['Phase 1'] = $raw_db_progress_data['Phase'];
            } elseif (isset($raw_db_progress_data['1'])) {
                $division_progress['Phase 1'] = $raw_db_progress_data['1'];
            }
        } else {
            if (isset($raw_db_progress_data[$canonical_div_name])) {
                $division_progress[$canonical_div_name] = $raw_db_progress_data[$canonical_div_name];
            }
        }
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['project_id'])) {
    $project_id = $_POST['project_id'];
    $divisions_posted = $_POST['division'];
    $progress_posted = $_POST['progress'];
    $date_updated_posted = $_POST['date_updated'];

    foreach ($divisions_posted as $index => $division_name) {
        $progress = $progress_posted[$index];
        $date_updated = $date_updated_posted[$index];

        // Check if progress for this division already exists
        $sql_check = "SELECT * FROM project_progress WHERE project_id = ? AND division_name = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("is", $project_id, $division_name);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Update existing record
                $sql_update = "UPDATE project_progress SET progress_percentage = ?, date_updated = ? WHERE project_id = ? AND division_name = ?";
                if ($stmt_update = $conn->prepare($sql_update)) {
                    $stmt_update->bind_param("isss", $progress, $date_updated, $project_id, $division_name);
                    $stmt_update->execute();
                    $stmt_update->close();
                } else {
                    echo "Error updating record: " . $conn->error;
                }
            } else {
                // Insert new record
                $sql_insert = "INSERT INTO project_progress (project_id, division_name, progress_percentage, date_updated) VALUES (?, ?, ?, ?)";
                if ($stmt_insert = $conn->prepare($sql_insert)) {
                    $stmt_insert->bind_param("isss", $project_id, $division_name, $progress, $date_updated);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                } else {
                    echo "Error inserting record: " . $conn->error;
                }
            }
            $stmt_check->close();
        } else {
            echo "Error preparing check statement: " . $conn->error;
        }
    }
    header("Location: view_project.php?id=" . $project_id); // Redirect back to project details
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Project Progress</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f7fafd;
            margin: 0;
            padding: 20px;
        }
        .container {
            margin: 40px auto;
            max-width: 800px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 32px 24px;
        }
        .header {
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 24px;
            color: #2563eb;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input[type="number"],
        .form-group input[type="date"] {
            width: calc(100% - 22px);
            padding: 12px 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            box-sizing: border-box;
        }
        .form-group input[type="number"]:focus,
        .form-group input[type="date"]:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        .division-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            gap: 15px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .division-item label {
            flex: 1;
            margin-bottom: 0;
            font-weight: 600;
            color: #555;
        }
        .division-item input {
            flex: 0 0 120px; /* Fixed width for input fields */
        }
        .button-container {
            margin-top: 30px;
            text-align: right;
        }
        .action-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            font-size: 1em;
            transition: background-color 0.2s;
            display: inline-block;
            margin-left: 10px;
        }
        .save-btn {
            background-color: #28a745;
        }
        .save-btn:hover {
            background-color: #218838;
        }
        .cancel-btn {
            background-color: #6c757d;
        }
        .cancel-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($project_details): ?>
        <div class="header">Update Progress for <?php echo htmlspecialchars($project_details['project_name']); ?></div>

        <form method="POST" action="update_progress.php">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">

            <?php foreach ($divisions_display as $division_name_to_display): ?>
                <div class="division-item">
                    <label><?php echo htmlspecialchars($division_name_to_display); ?> Progress:</label>
                    <input type="hidden" name="division[]" value="<?php echo htmlspecialchars($division_name_to_display); ?>">
                    <input type="number" name="progress[]" min="0" max="100" value="<?php echo htmlspecialchars($division_progress[$division_name_to_display]['progress']); ?>" required>
                    <input type="date" name="date_updated[]" value="<?php echo htmlspecialchars($division_progress[$division_name_to_display]['date_updated']); ?>" required>
                </div>
            <?php endforeach; ?>

            <div class="button-container">
                <a href="view_project.php?id=<?php echo $project_id; ?>" class="action-btn cancel-btn">Cancel</a>
                <button type="submit" class="action-btn save-btn">Save Progress</button>
            </div>
        </form>

        <?php else: ?>
            <p>Project not found.</p>
        <?php endif; ?>
    </div>
</body>
</html> 