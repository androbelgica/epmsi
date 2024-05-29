<?php
session_start();
include '../assets/constant/config.php';

// Check if the contract ID is set in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid contract ID.";
    header("location:manage_employees.php");
    exit();
}

// Get the contract ID from the URL
$contract_id = $_GET['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Base query for fetching contract details
    $query = "
        SELECT c.contract_id, c.start_date, c.end_date, c.designation, c.daily_rate, c.emp_status, c.remarks,
               ca.first_name, ca.last_name, ca.middle_name, ca.image,
               j.title, cl.company_name
        FROM contracts c
        LEFT JOIN candidates ca ON c.candidate_id = ca.candidate_id
        LEFT JOIN jobs j ON c.job_id = j.job_id
        LEFT JOIN clients cl ON j.client_id = cl.client_id
        WHERE c.contract_id = :contract_id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
    $stmt->execute();
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        $_SESSION['error'] = "Contract not found.";
        header("location:manage_employees.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:../dashboard.php");
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
                        <h4 class="mt-0 header-title">Contract Details</h4>
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

                        <div class="mb-4">
                            <a href="manage_employees.php" class="btn btn-primary">Back to Employees</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th>Full Name</th>
                                        <td><?php echo htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Image</th>
                                        <td>
                                            <?php if ($contract['image']): ?>
                                                <img src="../assets/images/<?php echo htmlspecialchars($contract['image']); ?>" alt="Employee Image" style="width:50px; height:50px;">
                                            <?php else: ?>
                                                <img src="../assets/images/default.png" alt="Employee Image" style="width:50px; height:50px;">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Company</th>
                                        <td><?php echo htmlspecialchars($contract['company_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Job Title</th>
                                        <td><?php echo htmlspecialchars($contract['title']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Start Date</th>
                                        <td><?php echo htmlspecialchars($contract['start_date']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>End Date</th>
                                        <td><?php echo htmlspecialchars($contract['end_date']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Designation</th>
                                        <td><?php echo htmlspecialchars($contract['designation']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Daily Rate</th>
                                        <td><?php echo number_format($contract['daily_rate'], 2, '.', ''); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><?php echo htmlspecialchars($contract['emp_status']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Remarks</th>
                                        <td><?php echo htmlspecialchars($contract['remarks']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Actions</th>
                                        <td>
                                            <a href="modify_contract.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-primary">Edit Contract</a>
                                            <a href="edit_deduction.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-primary">Edit Deductions</a>
                                            <a href="create_payroll.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-success">Generate Payroll</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div><!-- container-fluid -->
</div><!-- page-content-wrapper -->

<?php include('include/footer.php'); ?>
