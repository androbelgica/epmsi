<?php
session_start();
include '../../assets/constant/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("location: ../manage_candidate.php");
        exit();
    }

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO employees (client_id, start_date, end_date, position, daily_rate, skills, highest_education, sex, age, remarks) VALUES (:client_id, :start_date, :end_date, :position, :daily_rate, :skills, :highest_education, :sex, :age, :remarks)");

        $stmt->bindParam(':client_id', $_POST['client_id'], PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $_POST['start_date']);
        $stmt->bindParam(':end_date', $_POST['end_date']);
        $stmt->bindParam(':position', $_POST['position']);
        $stmt->bindParam(':daily_rate', $_POST['daily_rate']);
        $stmt->bindParam(':skills', $_POST['skills']);
        $stmt->bindParam(':highest_education', $_POST['highest_education']);
        $stmt->bindParam(':sex', $_POST['sex']);
        $stmt->bindParam(':age', $_POST['age'], PDO::PARAM_INT);
        $stmt->bindParam(':remarks', $_POST['remarks']);

        $stmt->execute();

        $_SESSION['success'] = "Candidate successfully hired.";
        header("location: ../manage_candidate.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("location: ../manage_candidate.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("location: ../manage_candidate.php");
    exit();
}
