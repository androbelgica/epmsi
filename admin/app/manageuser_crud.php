<?php
session_start();
include '../../assets/constant/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (isset($_POST['delete'])) {
            $id = $_POST['id'];

            $stmt = $conn->prepare("DELETE FROM login WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['success'] = 'User deleted successfully';
            header('Location: ../manage_users.php');
            exit;
        }

        if (isset($_POST['update'])) {
            $id = $_POST['id'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $role = $_POST['role'];
            $mobileno = $_POST['mobileno'];

            $stmt = $conn->prepare("UPDATE login SET email = :email, username = :username, role = :role, mobileno = :mobileno WHERE id = :id");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['success'] = 'User updated successfully';
            header('Location: ../manage_users.php');
            exit;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
