<?php
include 'db.php';

// Fetch users from the database
// Assuming your user table is named 'users' and has columns 'username' and 'is_logged_in'
$sql = "SELECT username, is_logged_in FROM users"; // Selecting username and is_logged_in
$result = $conn->query($sql);

// Close the database connection (should be done after fetching results)
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <!-- Include your CSS links and styles here -->
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
        .container {
            margin: 120px auto 0 auto;
            max-width: 900px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(37,99,235,0.10), 0 1.5px 8px rgba(0,0,0,0.04);
            padding: 40px 48px 32px 48px;
            position: relative;
            z-index: 1;
        }
        .header {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 24px;
            color: #2563eb;
            text-align: center;
            letter-spacing: 1px;
        }
         .top-buttons {
             display: flex;
             justify-content: flex-start;
             align-items: center;
             margin-bottom: 18px;
         }
        .home-btn {
            background: linear-gradient(90deg, #2563eb 0%, #4db3ff 100%);
            color: #fff;
            border: none;
            padding: 12px 28px;
            font-size: 1em;
            border-radius: 999px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
        }
        .home-btn:hover {
            background: linear-gradient(90deg, #1746a0 0%, #2563eb 100%);
            box-shadow: 0 4px 16px rgba(37,99,235,0.12);
        }
         table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(37,99,235,0.04);
            margin-bottom: 20px;
        }
        th, td {
            border: 1.5px solid #e5e7eb;
            padding: 14px 8px;
            text-align: left;
            font-size: 1em;
        }
        th {
            background: #e0e7ff;
            color: #2563eb;
            font-weight: 700;
        }
        tr:hover {
            background: #f1f5fd;
        }
         .status-online {
             color: #28a745;
             font-weight: 600;
         }
         .status-offline {
             color: #6c757d;
             font-weight: 600;
         }
         @media (max-width: 700px) {
            .container {
                margin: 8px;
                padding: 6px;
            }
            .header {
                font-size: 1.5em;
            }
            th, td {
                padding: 8px 2px;
                font-size: 0.95em;
            }
             .home-btn {
                 padding: 8px 12px;
                 font-size: 0.95em;
             }
        }
     </style>
</head>
<body>
    <div class="background-overlay"></div>
    <div class="container">
        <div class="top-buttons">
            <a href="dashboard.php" class="home-btn">Home</a>
        </div>
        <div class="header">User List</div>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Determine login status based on is_logged_in column
                        $status_class = 'status-offline';
                        $status_text = 'Logged Out';

                        // Check if the key 'is_logged_in' exists in the fetched row
                        // This is a safeguard in case the column hasn't been added yet
                         if (isset($row['is_logged_in']) && $row['is_logged_in']) {
                            $status_class = 'status-online';
                            $status_text = 'Logged In';
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>"; // Display username
                        echo "<td><span class=\"" . $status_class . "\">" . $status_text . "</span></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan=\"2\">No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html> 