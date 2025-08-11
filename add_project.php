<!DOCTYPE html>
<html>
<head>
    <title>Add New Project</title>
    <!-- Add necessary CSS links here -->
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .form-container h1 {
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
        .form-group img {
            margin-top: 10px;
            max-width: 200px;
            height: auto;
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
            .form-group {
                flex-direction: column;
                align-items: flex-start;
            }
            .form-group label {
                font-size: 1em;
                flex: none;
                margin-right: 0;
                margin-bottom: 5px;
            }
            .form-group input[type="text"],
            .form-group input[type="date"],
            .form-group input[type="file"] {
                width: 100%;
                padding: 8px 10px;
            }
            .form-actions {
                right: 10px;
                bottom: 10px;
                gap: 10px;
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
        <form class="form-container" action="process_add_project.php" method="POST" enctype="multipart/form-data">
            <h1>Add New Project</h1>
            <div class="form-group">
                <label for="project_name">Project Name:</label>
                <input type="text" id="project_name" name="project_name" required>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="deadline">Deadline:</label>
                <input type="date" id="deadline" name="deadline" required>
            </div>
             <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="project_cost">Project Cost:</label>
                <input type="text" id="project_cost" name="project_cost" required>
            </div>
            <div class="form-group">
                <label for="foreman">Foreman:</label>
                <input type="text" id="foreman" name="foreman" required>
            </div>
            <div class="form-group">
                <label for="project_type">Project Type:</label>
                <input type="text" id="project_type" name="project_type" required>
            </div>
            <div class="form-group">
                <label for="project_status">Project Status:</label>
                <input type="text" id="project_status" name="project_status" required>
            </div>
            <div class="form-group">
                <label for="project_divisions">Project Divisions:</label>
                <input type="text" id="project_divisions" name="project_divisions" required>
            </div>
             <div class="form-group">
                <label for="project_image">Project Image:</label>
                <input type="file" id="project_image" name="project_image" accept="image/*">
            </div>
            <div class="form-actions">
                <button type="submit">Add Project</button>
                <a href="project_list.php">Close</a>
            </div>
        </form>
    </div>
</body>
</html> 