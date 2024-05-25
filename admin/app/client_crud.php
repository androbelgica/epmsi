<?php
session_start();
include '../../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['submit'])) {
        $stmt = $conn->prepare("INSERT INTO `clients`(`company_name`, `contact_name`, `email`, `phone`, `address`, `status`, `logo`) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $company_name = htmlspecialchars($_POST['company_name'], ENT_QUOTES, 'UTF-8');
        $contact_name = htmlspecialchars($_POST['contact_name'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($_POST['address'], ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');
        $logo = $_FILES['logo']['name'];

        // Move uploaded logo file to desired location
        $target_dir = "../../assets/images/";
        $target_file = $target_dir . basename($_FILES["logo"]["name"]);
        move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file);

        $stmt->execute([$company_name, $contact_name, $email, $phone, $address, $status, $logo]);

        $_SESSION['success'] = "Client added successfully.";
        header("location:../add_client.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
