<?php
$conn = new mysqli("localhost", "root", "", "capstone_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    // Optionally, delete the photo file from the server here
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: employee_list.php");
exit();
?>
