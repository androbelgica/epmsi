<?php
session_start();
include '../../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];

        // Prepare and execute the query to fetch the stored hashed password
        $stmt = $conn->prepare("SELECT id, password, username, role FROM login WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['success'] = "Login successful.";
                header("location:../dashboard.php"); // Redirect to the dashboard or home page
                exit();
            } else {
                $_SESSION['error'] = "Invalid password.";
            }
        } else {
            $_SESSION['error'] = "No user found with that email.";
        }
    } else {
        $_SESSION['error'] = "Invalid request method.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Connection failed: " . $e->getMessage();
}

header("location:../index.php"); // Redirect back to the login page on error
exit();
?>
