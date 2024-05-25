<?php
session_start();
include '../assets/constant/config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid candidate ID.";
    header("location: manage_candidate.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM candidates WHERE candidate_id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidate) {
        $_SESSION['error'] = "Candidate not found.";
        header("location: manage_candidate.php");
        exit();
    }

    $resume_path = '../assets/images/' . $candidate['resume'];

    // Check if the resume file exists
    if (!file_exists($resume_path)) {
        $_SESSION['error'] = "Resume file not found.";
        header("location: manage_candidate.php");
        exit();
    }

    // Determine the MIME type of the file
    $mime_type = mime_content_type($resume_path);

    // Output appropriate content type header
    header("Content-type: $mime_type");

    // Output the file
    readfile($resume_path);
    exit();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location: manage_candidate.php");
    exit();
}
?>
