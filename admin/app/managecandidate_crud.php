<?php
session_start();
include '../../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['delete'])) {
        // CSRF token validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid CSRF token.');
        }

        $stmt = $conn->prepare("DELETE FROM candidates WHERE candidate_id = ?");
        $stmt->execute([$_POST['candidate_id']]);

        $_SESSION['success'] = "Candidate deleted successfully.";
        header("location:../manage_candidate.php");
        exit();
    }

    if (isset($_POST['update'])) {
        // CSRF token validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid CSRF token.');
        }

        $stmt = $conn->prepare("UPDATE candidates SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, resume = ?, skills = ?, highest_educ_attainment = ?, sex = ?, age = ? , status = ? WHERE candidate_id = ?");

        $first_name = htmlspecialchars(strtoupper($_POST['first_name']), ENT_QUOTES, 'UTF-8');
        $middle_name = htmlspecialchars(strtoupper($_POST['middle_name']), ENT_QUOTES, 'UTF-8');
        $last_name = htmlspecialchars(strtoupper($_POST['last_name']), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
        $skills = htmlspecialchars(strtoupper($_POST['skills']), ENT_QUOTES, 'UTF-8');
        $highest_educ_attainment = htmlspecialchars($_POST['highest_educ_attainment'], ENT_QUOTES, 'UTF-8');
        $sex = htmlspecialchars($_POST['sex'], ENT_QUOTES, 'UTF-8');
        $age = htmlspecialchars($_POST['age'], ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');
        $candidate_id = $_POST['candidate_id'];

        // Handle resume upload
        $resume = null;
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
            $resume_dir = "../../images/";
            $resume = basename($_FILES['resume']['name']);
            $target_file = $resume_dir . $resume;
            move_uploaded_file($_FILES['resume']['name'], $target_file);
        }

        $stmt->execute([$first_name, $middle_name, $last_name, $email, $phone, $resume, $skills, $highest_educ_attainment, $sex, $age, $status, $candidate_id]);

        $_SESSION['success'] = "Candidate updated successfully.";
        header("location:../manage_candidate.php");
        exit();
    }
} catch (PDOException $e) {
    // Check if the error code is 23000 (integrity constraint violation)
    if ($e->getCode() == '23000') {
        $_SESSION['error'] = "Cannot delete selected candidate. Please remove dependent records first.";
    } else {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("location:../manage_candidate.php");
    exit();
} catch (RuntimeException $e) {
    $_SESSION['error'] = $e->getMessage();
    header("location:../manage_candidate.php");
    exit();
}
?>
