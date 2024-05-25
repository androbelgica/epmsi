<?php
session_start();
include '../assets/constant/config.php';

if (!isset($_GET['contract_id'])) {
    $_SESSION['error'] = "Invalid contract ID.";
    header("location:manage_employees.php");
    exit();
}

try {
    // Connect to the database using PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the SQL query
    $contract_id = $_GET['contract_id'];
    $sql = "SELECT c.contract_id, CONCAT(ca.first_name, ' ', ca.last_name) AS fullname, c.daily_rate
            FROM contracts c
            INNER JOIN candidates ca ON c.candidate_id = ca.candidate_id
            WHERE c.contract_id = :contract_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':contract_id', $contract_id);
    $stmt->execute();

    // Check if the query was successful
    if ($stmt->rowCount() > 0) {
        // Fetch the data
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $fullname = $row['fullname'];
        $daily_rate = $row['daily_rate'];
    } else {
        // Contract ID not found
        $_SESSION['error'] = "Contract ID not found.";
        header("location:manage_employees.php");
        exit();
    }
} catch(PDOException $e) {
    // Handle database connection errors
    $_SESSION['error'] = "Database connection failed: " . $e->getMessage();
    header("location:manage_employees.php");
    exit();
} finally {
    // Close the database connection
    $conn = null;
}
?>


<?php include('include/sidebar.php'); ?>
<?php include('include/header.php'); ?>

<div class="page-content-wrapper">
    <div class="row tittle">
        <div class="top col-md-5 align-self-center">
            <h5>Add Payroll</h5>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Add Payroll</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8" style="margin-left: 10%;">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active p-3" id="home" role="tabpanel">
                                <form id="add_payroll" method="POST" action="payroll_crud.php" enctype="multipart/form-data">
                                    <!-- CSRF token field -->
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    
                                    <div class="form-group">
                                        <h4>BASIC INFO</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="employee">Employee:</label>
                                                <input type="text" id="employee" name="employee" class="form-control" value="<?php /* echo the fullname here */ ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="period_start">Period Start:</label>
                                                <input type="date" id="period_start" name="period_start" class="form-control" required>
                                            </div>
                                        </div>
                                        <!-- Continue with the rest of the form -->
                                    </div>

                                    <!-- Other form groups follow the same pattern -->

                                    <button type="submit" name="add_payroll" class="btn btn-primary">Add Payroll</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- Page content Wrapper -->
</div> <!-- content -->

<?php include('include/footer.php'); ?>
