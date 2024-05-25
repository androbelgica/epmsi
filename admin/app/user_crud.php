<?php
session_start();
include '../../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['submit'])) {
        $stmt = $conn->prepare("INSERT INTO `login`(`email`, `password`, `username`, `role`, `mobileno`, `image`, `delete_status`) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $password = $_POST['password']; // Raw password
        $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
        $role = htmlspecialchars($_POST['role'], ENT_QUOTES, 'UTF-8');
        $mobileno = htmlspecialchars($_POST['mobileno'], ENT_QUOTES, 'UTF-8');
        $image = $_FILES['image']['name']; // Get the name of the uploaded image file
        $delete_status = 0; // Assuming 0 means not deleted

        // Move uploaded image file to desired location
        $target_dir = "../../assets/images/"; // Change this directory as per your requirement
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

        $stmt->execute([$email, $password, $username, $role, $mobileno, $image, $delete_status]);

        $_SESSION['success'] = "User added successfully.";
        header("location:../add_user.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
