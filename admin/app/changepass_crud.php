<?php 
session_start();
include '../../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['submit'])) {
        $oldpassword = $_POST['oldpassword'];
        $newpassword = $_POST['newpassword'];
        $confirmpassword = $_POST['confirmpassword'];

        if ($newpassword == $confirmpassword) {
            $stmt = $conn->prepare("UPDATE `login` SET `password`=:password WHERE id=:id");
            $stmt->bindParam(':password', $newpassword);
            $stmt->bindParam(':id', $_SESSION['id']);
            $stmt->execute();

            header("location:../dashboard.php");
            exit();
        } else {
            echo "New password and confirm password do not match.";
        }
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
