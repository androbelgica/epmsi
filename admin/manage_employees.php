<?php
session_start();
include '../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Base query for fetching contracts
    $base_query = "
        SELECT c.contract_id, c.start_date, c.end_date, c.designation, c.daily_rate, c.emp_status, c.remarks,
               ca.first_name, ca.last_name, ca.middle_name, ca.image,
               j.title, cl.company_name
        FROM contracts c
        LEFT JOIN candidates ca ON c.candidate_id = ca.candidate_id
        LEFT JOIN jobs j ON c.job_id = j.job_id
        LEFT JOIN clients cl ON j.client_id = cl.client_id
        WHERE c.emp_status = 'Employed'
    ";

    // Initialize query parameters
    $params = [];

    // Search functionality
    if (isset($_POST['search'])) {
        $search = htmlspecialchars($_POST['search'], ENT_QUOTES, 'UTF-8');
        $base_query .= " AND (ca.first_name LIKE :search OR ca.last_name LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    // Sorting functionality
    $sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort'], ENT_QUOTES, 'UTF-8') : 'ca.last_name';
    $order = isset($_GET['order']) && ($_GET['order'] == 'asc' || $_GET['order'] == 'desc') ? htmlspecialchars($_GET['order'], ENT_QUOTES, 'UTF-8') : 'asc';
    $base_query .= " ORDER BY $sort $order";

    $stmt = $conn->prepare($base_query);
    $stmt->execute($params);
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$contracts) {
        $_SESSION['error'] = "No employees found.";
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
                        <h4 class="mt-0 header-title">Manage Employees</h4>
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
                            <form method="POST" action="">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="search" placeholder="Search by first name or last name">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th><a href="?sort=ca.last_name&order=<?php echo ($sort == 'ca.last_name' && $order == 'asc') ? 'desc' : 'asc'; ?>">Last Name</a></th>
                                        <th><a href="?sort=ca.first_name&order=<?php echo ($sort == 'ca.first_name' && $order == 'asc') ? 'desc' : 'asc'; ?>">First Name</a></th>
                                        <th>Image</th>
                                        <th>Company</th>
                                        <th>Job Title</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Designation</th>
                                        <th>Daily Rate</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contracts as $contract): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($contract['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['first_name']); ?></td>
                                            <td>
                                                <?php if ($contract['image']): ?>
                                                    <img src="../assets/images/<?php echo htmlspecialchars($contract['image']); ?>" alt="Employee Image" style="width:50px; height:50px;">
                                                <?php else: ?>
                                                    <img src="../assets/images/default.png" alt="Employee Image" style="width:50px; height:50px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($contract['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['title']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['start_date']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['end_date']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['designation']); ?></td>
                                            <td><?php echo number_format($contract['daily_rate'], 2, '.', ''); ?></td>
                                            <td><?php echo htmlspecialchars($contract['emp_status']); ?></td>
                                            <td><?php echo htmlspecialchars($contract['remarks']); ?></td>
                                            <td>
                                                <a href="modify_contract.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-primary">Edit Contract</a>
                                                <a href="create_payroll.php?id=<?php echo $contract['contract_id']; ?>" class="btn btn-sm btn-success">Generate Payroll</a>    
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
