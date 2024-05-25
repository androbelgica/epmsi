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

        $stmt = $conn->prepare("DELETE FROM clients WHERE client_id = ?");
        $stmt->execute([$_POST['client_id']]);

        $_SESSION['success'] = "Client deleted successfully.";
        header("location:../manage_client.php");
        exit();
    }

    // if (isset($_POST['update'])) {
    //     // CSRF token validation
    //     if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    //         throw new RuntimeException('Invalid CSRF token.');
    //     }
    
    //     $stmt = $conn->prepare("UPDATE clients SET company_name = ?, contact_name = ?, email = ?, phone = ?, address = ?, status = ?, logo = ? WHERE client_id = ?");
    
    //     $company_name = htmlspecialchars($_POST['company_name'], ENT_QUOTES, 'UTF-8');
    //     $contact_name = htmlspecialchars($_POST['contact_name'], ENT_QUOTES, 'UTF-8');
    //     $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    //     $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    //     $address = htmlspecialchars($_POST['address'], ENT_QUOTES, 'UTF-8');
    //     $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');
    //     $client_id = $_POST['client_id'];
    
    //     // Handle logo upload
    //     $logo = null;
    //     if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
    //         $logo_dir = "../../assets/logos/";
    //         $logo = basename($_FILES['logo']['name']);
    //         $target_file = $logo_dir . $logo;
    //         move_uploaded_file($_FILES['logo']['tmp_name'], $target_file);
    //     }
    
    //     $stmt->execute([$company_name, $contact_name, $email, $phone, $address, $status, $logo, $client_id]);
    
    //     $_SESSION['success'] = "Client updated successfully.";
    //     header("location:../manage_client.php");
    //     exit();
    // }
} catch (PDOException $e) {
    // Check if the error code is 23000 (integrity constraint violation)
    if ($e->getCode() == '23000') {
        $_SESSION['error'] = "Cannot delete selected client. Please remove dependent records first.";
    } else {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("location:../manage_client.php");
    exit();
} catch (RuntimeException $e) {
    $_SESSION['error'] = $e->getMessage();
    header("location:../manage_client.php");
    exit();
}
?>