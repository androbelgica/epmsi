<?php
session_start();
include '../assets/constant/config.php';

// Check if contract ID is provided in the query string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid contract ID.";
    header("location:manage_contract.php");
    exit();
}

$contract_id = $_GET['id'];

// Fetch contract details
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT contracts.*, candidates.first_name, candidates.last_name, candidates.middle_name, jobs.title, clients.company_name, candidates.candidate_id FROM contracts
                            LEFT JOIN candidates ON contracts.candidate_id = candidates.candidate_id
                            LEFT JOIN jobs ON contracts.job_id = jobs.job_id
                            LEFT JOIN clients ON jobs.client_id = clients.client_id
                            WHERE contracts.contract_id = :contract_id");
    $stmt->bindParam(':contract_id', $contract_id);
    $stmt->execute();
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        $_SESSION['error'] = "Contract not found.";
        header("location:manage_contract.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_contract.php");
    exit();
}

// Terminate contract
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $end_date = date('Y-m-d'); // Set end date to current date
    $emp_status = "Terminated";
    $candidate_status = "Previous Contract Terminated";
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';

    // Update contract and candidate status in the database
    try {
        $conn->beginTransaction();

        // Update contract
        $stmt = $conn->prepare("UPDATE contracts SET end_date = :end_date, emp_status = :emp_status, remarks = :remarks WHERE contract_id = :contract_id");
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':emp_status', $emp_status);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':contract_id', $contract_id);
        $stmt->execute();

        // Update candidate status
        $stmt = $conn->prepare("UPDATE candidates SET status = :candidate_status WHERE candidate_id = :candidate_id");
        $stmt->bindParam(':candidate_status', $candidate_status);
        $stmt->bindParam(':candidate_id', $contract['candidate_id']);
        $stmt->execute();

        $conn->commit();

        $_SESSION['success'] = "Contract terminated successfully.";
        header("location:manage_contract.php");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("location:terminate_contract.php?id=" . $contract_id);
        exit();
    }
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
                        <h4 class="mt-0 header-title">Terminate Contract for <?php echo htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']); ?>, assigned at <?php echo htmlspecialchars($contract['company_name']); ?> as <?php echo htmlspecialchars($contract['title']); ?></h4>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="remarks">Reason for Termination</label>
                                <textarea id="remarks" name="remarks" class="form-control" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">Terminate Contract</button>
                            <a href="manage_contract.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
