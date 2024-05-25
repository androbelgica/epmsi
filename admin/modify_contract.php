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

    $stmt = $conn->prepare("SELECT contracts.*, candidates.first_name, candidates.last_name, candidates.middle_name, jobs.title, clients.company_name FROM contracts
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

// Update contract details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input
    $end_date = $_POST['end_date'];
    $daily_rate = $_POST['daily_rate'];
    $designation = $_POST['designation'];
    $remarks = $_POST['remarks'];

    // Check if required fields are filled
    if (empty($end_date) || empty($daily_rate) || empty($designation)) {
        $_SESSION['error'] = "All fields are required.";
        header("location:modify_contract.php?id=" . $contract_id);
        exit();
    }

    // Update contract details in the database
    try {
        $stmt = $conn->prepare("UPDATE contracts SET end_date = :end_date, daily_rate = :daily_rate, designation = :designation, remarks = :remarks WHERE contract_id = :contract_id");
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':daily_rate', $daily_rate);
        $stmt->bindParam(':designation', $designation);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':contract_id', $contract_id);

        $stmt->execute();

        $_SESSION['success'] = "Contract updated successfully.";
        header("location:manage_contract.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("location:modify_contract.php?id=" . $contract_id);
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
                        <h4 class="mt-0 header-title">Modify Contract for <?php echo htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']); ?>, assigned at <?php echo htmlspecialchars($contract['company_name']); ?> as <?php echo htmlspecialchars($contract['title']); ?></h4>
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
                            <input type="hidden" name="contract_id" value="<?php echo htmlspecialchars($contract['contract_id']); ?>">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($contract['end_date']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="daily_rate">Daily Rate</label>
                                <input type="text" class="form-control" id="daily_rate" name="daily_rate" value="<?php echo htmlspecialchars($contract['daily_rate']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="designation">Designation</label>
                                <input type="text" class="form-control" id="designation" name="designation" value="<?php echo htmlspecialchars($contract['designation']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks"><?php echo htmlspecialchars($contract['remarks']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Contract</button>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
