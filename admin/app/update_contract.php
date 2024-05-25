<?php
session_start();
include '../assets/constant/config.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input
    $contract_id = $_POST['contract_id'];
    $end_date = $_POST['end_date'];
    $daily_rate = $_POST['daily_rate'];
    $designation = $_POST['designation'];
    $remarks = $_POST['remarks'];

    // Check if required fields are filled
    if (empty($contract_id) || empty($end_date) || empty($daily_rate) || empty($designation)) {
        $_SESSION['error'] = "All fields are required.";
        header("location:../admin/app/update_contract.php?id=" . $contract_id);
        exit();
    }

    // Update contract details in the database
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("UPDATE contracts SET end_date = :end_date, daily_rate = :daily_rate, designation = :designation, remarks = :remarks WHERE contract_id = :contract_id");
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':daily_rate', $daily_rate);
        $stmt->bindParam(':designation', $designation);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':contract_id', $contract_id);

        $stmt->execute();

        $_SESSION['success'] = "Contract updated successfully.";
        header("location:manage_contract.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("location:../admin/app/update_contract.php?id=" . $contract_id);
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header("location:manage_contract.php");
    exit();
}
?>
