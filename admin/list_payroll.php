<?php
session_start();
include '../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch payrolls with full name and company based on contract ID, sorted by the most recent date
    $query = "SELECT 
                p.payroll_id,
                p.contract_id,
                p.period_start,
                p.period_end,
                p.gross_pay,
                p.net_pay,
                CONCAT(c.last_name, ', ', c.first_name, ' ', c.middle_name) AS full_name,
                cl.company_name
            FROM 
                payroll p
            INNER JOIN 
                contracts co ON p.contract_id = co.contract_id
            INNER JOIN 
                candidates c ON co.candidate_id = c.candidate_id
            INNER JOIN 
                jobs j ON co.job_id = j.job_id
            INNER JOIN 
                clients cl ON j.client_id = cl.client_id
            ORDER BY 
                p.period_end DESC;

    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$payrolls) {
        $_SESSION['error'] = "No payrolls found for this contract.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_employees.php");
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
                        <h4 class="mt-0 header-title">Payroll List</h4>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($payrolls): ?>
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Company</th>
                                        <th>Period Start</th>
                                        <th>Period End</th>
                                        <th>Gross Pay</th>
                                        <th>Net Pay</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payrolls as $payroll): ?>
                                        <tr>
                                            <td><?php echo $payroll['full_name']; ?></td>
                                            <td><?php echo $payroll['company_name']; ?></td>
                                            <td><?php echo $payroll['period_start']; ?></td>
                                            <td><?php echo $payroll['period_start']; ?></td>
                                            <td><?php echo $payroll['period_end']; ?></td>
                                            <td><?php echo $payroll['gross_pay']; ?></td>
                                            <td><?php echo $payroll['net_pay']; ?></td>
                                            <td>
                                            <a href="../admin/view_payroll.php?id=<?php echo $payroll['payroll_id']; ?>" class="btn btn-primary">View Payroll</a>
                                                <a href="print_payslip.php?id=<?php echo $payroll['payroll_id']; ?>" class="btn btn-sm btn-success">Print Payslip</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No payrolls found.</p>
                        <?php endif; ?>
                    </div>
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div><!-- container-fluid -->
</div><!-- page-content-wrapper -->

<?php include('include/footer.php'); ?>
