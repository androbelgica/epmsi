<?php
session_start();
include '../assets/constant/config.php';

$filters = [];
$query_parts = [];

// Get filter inputs
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['month']) && !empty($_GET['year'])) {
        $filters['month'] = $_GET['month'];
        $filters['year'] = $_GET['year'];
        $query_parts[] = "MONTH(contracts.start_date) = :month AND YEAR(contracts.start_date) = :year";
    }

    if (!empty($_GET['min_rate'])) {
        $filters['min_rate'] = $_GET['min_rate'];
        $query_parts[] = "contracts.daily_rate >= :min_rate";
    }

    if (!empty($_GET['max_rate'])) {
        $filters['max_rate'] = $_GET['max_rate'];
        $query_parts[] = "contracts.daily_rate <= :max_rate";
    }

    if (!empty($_GET['company'])) {
        $filters['company'] = $_GET['company'];
        $query_parts[] = "clients.company_name LIKE :company";
    }

    if (!empty($_GET['emp_status'])) {
        $filters['emp_status'] = $_GET['emp_status'];
        $query_parts[] = "contracts.emp_status = :emp_status";
    }
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT contracts.*, candidates.first_name, candidates.last_name, candidates.middle_name, jobs.title, clients.company_name 
            FROM contracts
            LEFT JOIN candidates ON contracts.candidate_id = candidates.candidate_id
            LEFT JOIN jobs ON contracts.job_id = jobs.job_id
            LEFT JOIN clients ON jobs.client_id = clients.client_id";

    if (!empty($query_parts)) {
        $sql .= " WHERE " . implode(' AND ', $query_parts);
    }

    $stmt = $conn->prepare($sql);

    foreach ($filters as $key => $value) {
        if ($key == 'company') {
            $stmt->bindValue(':' . $key, '%' . $value . '%');
        } else {
            $stmt->bindValue(':' . $key, $value);
        }
    }

    $stmt->execute();
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch companies for dropdown
    $stmt = $conn->query("SELECT company_name FROM clients");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:../admin/dashboard.php");
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
                        <h4 class="mt-0 header-title">Manage Contracts</h4>
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

                        <!-- Filter Form -->
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="month">Month</label>
                                    <select name="month" id="month" class="form-control">
                                        <option value="">Select Month</option>
                                        <?php for ($m=1; $m<=12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php if(isset($_GET['month']) && $_GET['month'] == $m) echo 'selected'; ?>>
                                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="year">Year</label>
                                    <select name="year" id="year" class="form-control">
                                        <option value="">Select Year</option>
                                        <?php for ($y=date('Y'); $y>=2000; $y--): ?>
                                            <option value="<?php echo $y; ?>" <?php if(isset($_GET['year']) && $_GET['year'] == $y) echo 'selected'; ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="min_rate">Min Daily Rate</label>
                                    <input type="number" name="min_rate" id="min_rate" class="form-control" value="<?php echo $_GET['min_rate'] ?? ''; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="max_rate">Max Daily Rate</label>
                                    <input type="number" name="max_rate" id="max_rate" class="form-control" value="<?php echo $_GET['max_rate'] ?? ''; ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="company">Company</label>
                                    <select name="company" id="company" class="form-control">
                                        <option value="">Select Company</option>
                                        <?php foreach ($companies as $company): ?>
                                            <option value="<?php echo $company['company_name']; ?>" <?php if(isset($_GET['company']) && $_GET['company'] == $company['company_name']) echo 'selected'; ?>>
                                                <?php echo $company['company_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="emp_status">Employee Status</label>
                                    <select name="emp_status" id="emp_status" class="form-control">
                                        <option value="">Select Status</option>
                                        <option value="Employed" <?php if(isset($_GET['emp_status']) && $_GET['emp_status'] == 'Employed') echo 'selected'; ?>>Employed</option>
                                        <option value="Terminated" <?php if(isset($_GET['emp_status']) && $_GET['emp_status'] == 'Terminated') echo 'selected'; ?>>Terminated</option>
                                        <option value="Previous Contract Terminated" <?php if(isset($_GET['emp_status']) && $_GET['emp_status'] == 'Previous Contract Terminated') echo 'selected'; ?>>Previous Contract Terminated</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="manage_contract.php" class="btn btn-secondary">Reset Filter</a>
                                </div>
                            </div>
                        </form>
                        <br>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Candidate Name</th>
                                        <th>Company</th>
                                        <th>Job Title</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Designation</th>
                                        <th>Daily Rate</th>
                                        <th>Employee Status</th>
                                        <th>Remarks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($contracts as $contract): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($contract['last_name'] . ', ' . $contract['first_name']. ' ' . $contract['middle_name']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['title']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['start_date']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['end_date']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['designation']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['daily_rate']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['emp_status']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['remarks']); ?></td>
                                            <td>
                                            <?php if ($contract['emp_status'] != 'Terminated' && $contract['emp_status'] != 'Contract Ended'): ?>
                                                    <a href="modify_contract.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-success">Extend/Amend</a>
                                                    <a href="terminate_contract.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-danger">Terminate</a>
                                                    <a href="end_contract.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-secondary">End Contract</a>
                                                    <a href="contract_deductions.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-info">Payroll Deductions</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
