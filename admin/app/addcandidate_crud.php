<?php
session_start();
include '../../assets/constant/config.php';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Establish database connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    // Retrieve form data
    $first_name = strtoupper($_POST['first_name']);
    $middle_name = strtoupper($_POST['middle_name']);
    $last_name = strtoupper($_POST['last_name']);
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $skills = strtoupper($_POST['skills']);
    $highest_educ_attainment = $_POST['highest_educ_attainment'];
    $sex = $_POST['sex'];
    $age = $_POST['age'];
    $emp_status = $_POST['emp_status'];
    $resume = $_FILES["resume"]["name"];
    $image = $_FILES["image"]["name"];
    
    $target_dir = "../../assets/images/";
    $target_file = $target_dir . basename($_FILES["resume"]["name"]);
    move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file);

    $target_dir = "../../assets/images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    try {
        // Prepare SQL statement to insert candidate data into the database
        $stmt = $conn->prepare("INSERT INTO candidates (first_name, middle_name, last_name, email, phone, resume, skills, highest_educ_attainment, sex, age, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bindParam(1, $first_name);
        $stmt->bindParam(2, $middle_name);
        $stmt->bindParam(3, $last_name);
        $stmt->bindParam(4, $email);
        $stmt->bindParam(5, $phone);
        $stmt->bindParam(6, $resume);
        $stmt->bindParam(7, $skills);
        $stmt->bindParam(8, $highest_educ_attainment);
        $stmt->bindParam(9, $sex);
        $stmt->bindParam(10, $age);
        $stmt->bindParam(11, $emp_status);
        $stmt->bindParam(12, $image); // Assuming $resume should be used here instead of $image

        // Execute the statement
        $stmt->execute();

        // Set success message
        $_SESSION['success'] = "Candidate added successfully!";
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Redirect to the page where you want to display the success message
    header("Location: ../manage_candidate.php");
    exit();
}
?>
