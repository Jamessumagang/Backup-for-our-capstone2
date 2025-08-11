<?php
include 'db.php';

$project_id = null;
$project_details = null;
$division_progress_data = []; // Will store progress mapped to standardized division names
$display_divisions_array = []; // Will store standardized division names for iteration

// Get project ID from URL
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];

    // Prepare and bind SQL statement to fetch project details
    $sql = "SELECT * FROM projects WHERE project_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $project_details = $result->fetch_assoc();

            // Fetch existing progress for divisions (raw, as they are in DB)
            $raw_db_progress_data = [];
            $sql_progress = "SELECT division_name, progress_percentage, date_updated FROM project_progress WHERE project_id = ?";
            if ($stmt_progress = $conn->prepare($sql_progress)) {
                $stmt_progress->bind_param("i", $project_id);
                $stmt_progress->execute();
                $result_progress = $stmt_progress->get_result();
                while ($row_progress = $result_progress->fetch_assoc()) {
                    $raw_db_progress_data[trim($row_progress['division_name'])] = [
                        'progress' => $row_progress['progress_percentage'],
                        'date_updated' => $row_progress['date_updated']
                    ];
                }
                $stmt_progress->close();
            } else {
                echo "Error fetching raw progress data: " . $conn->error;
            }

            // Process project_divisions string to consolidate and standardize division names for display
            $raw_divisions_from_project_string = $project_details['project_divisions'];
            $temp_divisions = array_map('trim', explode(',', $raw_divisions_from_project_string));

            $processed_divisions_temp = [];
            foreach ($temp_divisions as $div) {
                // Normalize "Phase" and "1" (if standalone) or "Phase 1" into "Phase 1"
                if (trim($div) === 'Phase' || trim($div) === '1' || trim($div) === 'Phase 1') {
                    $processed_divisions_temp[] = 'Phase 1';
                } else {
                    $processed_divisions_temp[] = $div;
                }
            }
            $display_divisions_array = array_values(array_unique($processed_divisions_temp)); // Ensure uniqueness and re-index
            // Sort to ensure order (e.g., 'Phase 1', 'Phase 2'...)
            // A custom sort might be needed if phase order isn't strictly alphabetical after consolidation
            // For now, simple sort should work if names are consistent (Phase 1, Phase 2, etc.)
            sort($display_divisions_array);

            // Now map progress data to the consolidated display_divisions_array
            foreach ($display_divisions_array as $canonical_div_name) {
                if ($canonical_div_name === 'Phase 1') {
                    // Prioritize: check 'Phase 1' first, then 'Phase', then '1'
                    if (isset($raw_db_progress_data['Phase 1'])) {
                        $division_progress_data['Phase 1'] = $raw_db_progress_data['Phase 1'];
                    } elseif (isset($raw_db_progress_data['Phase'])) {
                        $division_progress_data['Phase 1'] = $raw_db_progress_data['Phase'];
                    } elseif (isset($raw_db_progress_data['1'])) {
                        $division_progress_data['Phase 1'] = $raw_db_progress_data['1'];
                    } else {
                        $division_progress_data['Phase 1'] = ['progress' => 0, 'date_updated' => 'N/A'];
                    }
                } else {
                    $division_progress_data[$canonical_div_name] = isset($raw_db_progress_data[$canonical_div_name]) ?
                                                                       $raw_db_progress_data[$canonical_div_name] :
                                                                       ['progress' => 0, 'date_updated' => 'N/A'];
                }
            }

        } else {
            echo "Project not found.";
        }

        $stmt->close();
    } else {
        echo "Error: Could not prepare statement: " . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Project Details</title>
    <!-- Add necessary CSS links here to match your other pages -->
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f7fafd;
            margin: 0;
        }
        .container {
            margin: 40px auto;
            max-width: 1200px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 32px 24px 24px 24px;
        }
        .header {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 24px;
            color: #2563eb;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .project-overview {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .project-image-container {
            flex: 0 0 250px;
        }
        .project-image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .project-details-container {
            flex: 1;
        }
         .detail-item {
             margin-bottom: 10px;
             padding-bottom: 5px;
             border-bottom: none;
         }
        .detail-item label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            margin-right: 10px;
            width: 120px;
        }
        .detail-item span {
            color: #222;
            font-size: 1em;
        }
        .project-extra-details {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 30px;
        }
         .project-extra-details div {
             flex: 1;
         }
        .graph-container {
            margin-top: 30px;
            border: 1px solid #eee;
            padding: 20px;
            text-align: center;
        }
        .graph-container canvas {
             max-width: 100%;
             height: 400px; /* Adjust height as needed */
        }
        .button-container {
            margin-top: 30px;
            text-align: center;
        }
         .action-btn {
             padding: 10px 20px;
             border: none;
             border-radius: 6px;
             cursor: pointer;
             text-decoration: none;
             color: #fff;
             font-size: 1em;
             transition: background-color 0.2s;
             display: inline-block;
             margin: 0 10px;
         }
        .edit-btn {
            background-color: #007bff;
        }
        .edit-btn:hover {
            background-color: #0056b3;
        }
         .back-btn {
             background-color: #6c757d;
         }
         .back-btn:hover {
             background-color: #5a6268;
         }
         .update-progress-btn {
             background-color: #28a745;
         }
         .update-progress-btn:hover {
             background-color: #218838;
         }

        .progress-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            table-layout: fixed;
        }
        .progress-table th,
        .progress-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .progress-table th:nth-child(1), .progress-table td:nth-child(1) {
            width: 20%;
        }
        .progress-table th:nth-child(2), .progress-table td:nth-child(2) {
            width: 15%;
        }
        .progress-table th:nth-child(3), .progress-table td:nth-child(3) {
            width: 20%;
        }
        .progress-table th {
            background-color: #f2f2f2;
            font-weight: 600;
            color: #333;
        }
        .progress-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .progress-table tr:hover {
            background-color: #f1f1f1;
        }
        .progress-table .action-col {
            text-align: center;
            width: 50;
            white-space: nowrap;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
        }
        .progress-table .action-col a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        .progress-table .action-col a:hover {
            text-decoration: none;
            background-color: #e0e9fa;
        }

         @media (max-width: 700px) {
             .project-overview {
                 flex-direction: column;
                 gap: 20px;
             }
             .project-image-container {
                 flex: none;
                 width: 100%;
                 text-align: center;
             }
             .project-extra-details {
                 flex-direction: column;
                 gap: 20px;
             }
             .detail-item label {
                 width: auto;
                 margin-right: 0;
                 margin-bottom: 5px;
             }
         }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($project_details): ?>
        <div class="header">PROJECT NAME: <?php echo htmlspecialchars($project_details['project_name']); ?></div>

        <div class="project-overview">
            <div class="project-image-container">
                <?php if (isset($project_details['image_path']) && !empty($project_details['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($project_details['image_path']); ?>" alt="Project Image">
                <?php else: ?>
                    <img src="placeholder.png" alt="No Project Image">
                <?php endif; ?>
            </div>
            <div class="project-details-container">
                <div class="detail-item"><label>start date:</label><span><?php echo htmlspecialchars($project_details['start_date']); ?></span></div>
                <div class="detail-item"><label>deadline:</label><span><?php echo htmlspecialchars($project_details['deadline']); ?></span></div>
                <div class="detail-item"><label>location:</label><span><?php echo htmlspecialchars($project_details['location']); ?></span></div>
                <div class="detail-item"><label>Project cost:</label><span><?php echo htmlspecialchars($project_details['project_cost']); ?></span></div>
                <div class="detail-item"><label>Foreman:</label><span><?php echo htmlspecialchars($project_details['foreman']); ?></span></div>
            </div>
        </div>

        <div class="project-extra-details">
             <div>
                 <div class="detail-item"><label>Project Type:</label><span><?php echo htmlspecialchars($project_details['project_type']); ?></span></div>
                 <div class="detail-item"><label>Project Status:</label><span><?php echo htmlspecialchars($project_details['project_status']); ?></span></div>
                 <div class="detail-item"><label>Project Division:</label><span><?php echo htmlspecialchars($project_details['project_divisions']); ?></span></div>
             </div>
        </div>

        <div class="graph-container">
            <canvas id="projectProgressChart"></canvas>
        </div>

        <table class="progress-table">
            <thead>
                <tr>
                    <th>Division</th>
                    <th>Progress</th>
                    <th>Date Updated</th>
                    <th class="action-col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($display_divisions_array as $division_name_display): ?>
                <?php
                    $progress = isset($division_progress_data[$division_name_display]['progress']) ? $division_progress_data[$division_name_display]['progress'] : 0;
                    $date_updated = isset($division_progress_data[$division_name_display]['date_updated']) ? $division_progress_data[$division_name_display]['date_updated'] : 'N/A';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($division_name_display); ?></td>
                    <td><?php echo htmlspecialchars($progress); ?>%</td>
                    <td><?php echo htmlspecialchars($date_updated); ?></td>
                    <td class="action-col">
                        <a href="update_progress.php?id=<?php echo $project_id; ?>">Update Progress</a> | 
                        <a href="edit_project_phase_steps.php?project_id=<?php echo $project_id; ?>&division_name=<?php echo urlencode($division_name_display); ?>">Edit Steps</a> | 
                        <a href="view_project_steps.php?project_id=<?php echo $project_id; ?>&division_name=<?php echo urlencode($division_name_display); ?>">View Steps</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="button-container">
            <a href="edit_project.php?id=<?php echo $project_details['project_id']; ?>" class="action-btn edit-btn">Edit Project Details</a>
            <a href="project_list.php" class="action-btn back-btn">Back to Project List</a>
            <a href="update_progress.php?id=<?php echo $project_details['project_id']; ?>" class="action-btn update-progress-btn">Update Progress</a>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const divisionData = <?php echo json_encode($division_progress_data); ?>;
                const projectDivisions = <?php echo json_encode($display_divisions_array); ?>; // Use the processed array

                const labels = [];
                const progressPercentages = [];
                let totalProgress = 0;
                let divisionsCount = projectDivisions.length;

                projectDivisions.forEach(division => {
                    labels.push(division.replace('Phase ', 'P'));
                    const progress = divisionData[division] ? parseInt(divisionData[division].progress) : 0;
                    progressPercentages.push(progress);
                    totalProgress += progress;
                });

                // Add 'Total' to labels and calculate average total progress
                labels.push('Total');
                const averageTotalProgress = divisionsCount > 0 ? (totalProgress / divisionsCount) : 0;
                progressPercentages.push(averageTotalProgress.toFixed(2));

                const ctx = document.getElementById('projectProgressChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Progress Percentage',
                            data: progressPercentages,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(153, 102, 255, 0.6)',
                                'rgba(255, 159, 64, 0.6)' // Color for Total
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Progress (%)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Division'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>

        <?php else: ?>
            <p>Project not found.</p>
        <?php endif; ?>
    </div>
</body>
</html> 