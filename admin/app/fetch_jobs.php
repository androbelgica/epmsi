<?php
include '../assets/constant/config.php';

// Check if client_id is provided in the request
if(isset($_POST['client_id'])) {
    $client_id = $_POST['client_id'];

    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare and execute the SQL statement to fetch jobs for the selected client
        $stmt = $conn->prepare("SELECT job_id, title FROM jobs WHERE client_id = ?");
        $stmt->execute([$client_id]);

        // Fetch the jobs and return as JSON
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($jobs);
    } catch(PDOException $e) {
        // Handle database connection error
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}
?>
