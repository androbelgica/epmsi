<?php
session_start();
include '../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "Invalid client ID.";
        header("location:manage_client.php");
        exit();
    }

    $client_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM clients WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        $_SESSION['error'] = "Client not found.";
        header("location:manage_client.php");
        exit();
    }

    if (isset($_POST['update'])) {
        // CSRF token validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid CSRF token.');
        }

        $stmt = $conn->prepare("UPDATE clients SET company_name = ?, contact_name = ?, email = ?, phone = ?, address = ?, status = ?, logo = ? WHERE client_id = ?");

        $company_name = htmlspecialchars($_POST['company_name'], ENT_QUOTES, 'UTF-8');
        $contact_name = htmlspecialchars($_POST['contact_name'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($_POST['address'], ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');
        $logo = $_FILES['logo'];

        // Handle logo upload
        if ($logo['error'] === UPLOAD_ERR_OK) {
            $logo_dir = "../assets/images/";
            $logo_name = basename($logo['name']);
            $target_file = $logo_dir . $logo_name;
            move_uploaded_file($logo['tmp_name'], $target_file);
        } else {
            // Use existing logo if no new logo uploaded
            $logo_name = $client['logo'];
        }

        $stmt->execute([$company_name, $contact_name, $email, $phone, $address, $status, $logo_name, $client_id]);

        $_SESSION['success'] = "Client updated successfully.";
        header("location:manage_client.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_client.php");
    exit();
} catch (RuntimeException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_client.php");
    exit();
}
?>
<?php include('include/sidebar.php'); ?>
<?php include('include/header.php'); ?>
<div class="page-content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mt-0 header-title">Edit Client</h4>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="company_name">Company Name</label>
                                <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($client['company_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="contact_name">Contact Name</label>
                                <input type="text" class="form-control" name="contact_name" value="<?php echo htmlspecialchars($client['contact_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" name="address" required><?php echo htmlspecialchars($client['address']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <input type="text" class="form-control" name="status" value="<?php echo htmlspecialchars($client['status']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="logo">Logo</label>
                                <input type="file" class="form-control-file" name="logo">
                            </div>
                            <button type="submit" name="update" class="btn btn-success">Update</button>
                            <a href="manage_client.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
