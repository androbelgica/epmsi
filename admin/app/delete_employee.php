<?php
session_start();
include '../../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['delete'])) {
        // Validate CSRF token
        if (isset($_POST['delete'])) {
            // CSRF token validation
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                throw new RuntimeException('Invalid CSRF token.');
            }
    
            $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
            $stmt->execute([$_POST['employee_id']]);
    
            $_SESSION['success'] = "Employee deleted successfully.";
            header("location:../manage_employees.php");
            exit();
        }
    
    } else {
        // If the delete button was not clicked, redirect back to the manage_jobs.php page
        header("location: ../manage_employees.php");
        exit();
    }
} catch (PDOException $e) {

    if ($e->getCode() == '23000') {
        $_SESSION['error'] = "Cannot delete selected Employee. Please remove dependent records first.";
    } else {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location: ../manage_employees.php");
    exit();
} catch (RuntimeException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location: ../manage_employees.php");
    exit();
}
?>
