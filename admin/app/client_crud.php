<?php
session_start();
include '../../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['submit'])) {
        // Start transaction
        $conn->beginTransaction();

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

        // Execute the client insertion
        $stmt->execute([$company_name, $contact_name, $email, $phone, $address, $status, $logo]);

        // Get the last inserted client ID
        $client_id = $conn->lastInsertId();

        // Prepare statement for inserting default values into client_salary_constants
        $stmt_constants = $conn->prepare("
            INSERT INTO `client_salary_constants` (
                `client_id`, `b_ot`, `nsd`, `nsd_ot`, `rdd`, `rdd_ot`, 
                `rdnsd`, `rdnsd_ot`, `sh`, `sh_ot`, `shnsd`, `shnsd_ot`, 
                `lh`, `lh_ot`, `lhnsd`, `lhnsd_ot`, `shrd`, `shrd_ot`, 
                `13th`, `sil`, `datecreated`, `user_id`
            ) VALUES (
                ?, 0, 0, 0, 0, 0, 
                0, 0, 0, 0, 0, 0, 
                0, 0, 0, 0, 0, 0, 
                0, 0, CURRENT_TIMESTAMP, ?
            )
        ");

        // Assuming you have a user ID available from the session or other means
        $user_id = $_SESSION['user_id'] ?? 1; // Replace '1' with a valid user ID or get it from the session

        // Execute the client_salary_constants insertion
        $stmt_constants->execute([$client_id, $user_id]);

        // Commit the transaction
        $conn->commit();

        $_SESSION['success'] = "Client added successfully.";
        header("location:../add_client.php");
        exit();
    }
} catch (PDOException $e) {
    // Rollback the transaction if an error occurs
    $conn->rollBack();
    echo "Connection failed: " . $e->getMessage();
}
?>
