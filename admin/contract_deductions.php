<?php
session_start();
include '../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "Invalid contract ID.";
        header("location:manage_contract.php");
        exit();
    }

    $contract_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM contracts WHERE contract_id = ?");
    $stmt->execute([$contract_id]);
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        $_SESSION['error'] = "Contract not found.";
        header("location:manage_contract.php");
        exit();
    }

    // Fetch values for other_deductions
    $stmt = $conn->prepare("SELECT * FROM other_deductions WHERE contract_id = ?");
    $stmt->execute([$contract_id]);
    $other_deductions = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch values for sss
    $stmt = $conn->prepare("SELECT * FROM sss WHERE contract_id = ?");
    $stmt->execute([$contract_id]);
    $sss = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch values for phic
    $stmt = $conn->prepare("SELECT * FROM phic WHERE contract_id = ?");
    $stmt->execute([$contract_id]);
    $phic = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch values for ec
    $stmt = $conn->prepare("SELECT * FROM ec WHERE contract_id = ?");
    $stmt->execute([$contract_id]);
    $ec = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch values for hdmf
    $stmt = $conn->prepare("SELECT * FROM hdmf WHERE contract_id = ?");
    $stmt->execute([$contract_id]);
    $hdmf = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($_POST['update'])) {
        // CSRF token validation and updating contract data...
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid CSRF token.');
        }

        // Update other_deductions table
        $stmt = $conn->prepare("UPDATE other_deductions SET lifeinsurance = ?, uniforms_ppe = ? WHERE contract_id = ?");
        $stmt->execute([$_POST['lifeinsurance'], $_POST['uniforms_ppe'], $contract_id]);

        // Update sss table
        $stmt = $conn->prepare("UPDATE sss SET ee_share = ?, er_share = ? WHERE contract_id = ?");
        $stmt->execute([$_POST['sss_ee_share'], $_POST['sss_er_share'], $contract_id]);

        // Update phic table
        $stmt = $conn->prepare("UPDATE phic SET ee_share = ?, er_share = ? WHERE contract_id = ?");
        $stmt->execute([$_POST['phic_ee_share'], $_POST['phic_er_share'], $contract_id]);

        // Update ec table
        $stmt = $conn->prepare("UPDATE ec SET ee_share = ?, er_share = ? WHERE contract_id = ?");
        $stmt->execute([$_POST['ec_ee_share'], $_POST['ec_er_share'], $contract_id]);

        // Update hdmf table
        $stmt = $conn->prepare("UPDATE hdmf SET ee_share = ?, er_share = ? WHERE contract_id = ?");
        $stmt->execute([$_POST['hdmf_ee_share'], $_POST['hdmf_er_share'], $contract_id]);

        $_SESSION['success'] = "Contract updated successfully.";
        header("location:manage_contract.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_contract.php");
    exit();
} catch (RuntimeException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_contract.php");
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
                        <h4 class="mt-0 header-title">Edit Contract Deductions</h4>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="row">
                                <!-- First column -->
                                <div class="col-lg-6">
                                    <!-- Fields for other_deductions -->
                                    <div class="form-group">
                                        <label for="lifeinsurance">Life Insurance</label>
                                        <input type="text" class="form-control" name="lifeinsurance" value="<?php echo htmlspecialchars($other_deductions['lifeinsurance']); ?>" required>
                                    </div>
                                    

                                    <!-- Fields for sss -->
                                    <div class="form-group">
                                        <label for="sss_ee_share">SSS EE Share</label>
                                        <input type="text" class="form-control" name="sss_ee_share" value="<?php echo htmlspecialchars($sss['ee_share']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phic_ee_share">PHIC EE Share</label>
                                        <input type="text" class="form-control" name="phic_ee_share" value="<?php echo htmlspecialchars($phic['ee_share']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="ec_ee_share">EC EE Share</label>
                                        <input type="text" class="form-control" name="ec_ee_share" value="<?php echo htmlspecialchars($ec['ee_share']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="hdmf_ee_share">HDMF EE Share</label>
                                        <input type="text" class="form-control" name="hdmf_ee_share" value="<?php echo htmlspecialchars($hdmf['ee_share']); ?>" required>
                                    </div>
                                </div>

                                <!-- Second column -->
                                <div class="col-lg-6">
                                   
                                    <div class="form-group">
                                        <label for="uniforms_ppe">Uniforms & PPE</label>
                                        <input type="text" class="form-control" name="uniforms_ppe" value="<?php echo htmlspecialchars($other_deductions['uniforms_ppe']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="sss_er_share">SSS ER Share</label>
                                        <input type="text" class="form-control" name="sss_er_share" value="<?php echo htmlspecialchars($sss['er_share']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phic_er_share">PHIC ER Share</label>
                                        <input type="text" class="form-control" name="phic_er_share" value="<?php echo htmlspecialchars($phic['er_share']); ?>" required>
                                    </div>
                                  
                                    <div class="form-group">
                                        <label for="ec_er_share">EC ER Share</label>
                                        <input type="text" class="form-control" name="ec_er_share" value="<?php echo htmlspecialchars($ec['er_share']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="hdmf_er_share">HDMF ER Share</label>
                                        <input type="text" class="form-control" name="hdmf_er_share" value="<?php echo htmlspecialchars($hdmf['er_share']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="update" class="btn btn-success">Update</button>
                                <a href="manage_contract.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
