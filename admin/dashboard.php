<?php
session_start();
include "../assets/constant/config.php";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log the error and show a user-friendly message
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}

// Function to get counts from different tables
function getCount($conn, $table, $condition = '1') {
    try {
        $stmt = $conn->prepare("SELECT count(*) as cnt FROM $table WHERE $condition");
        $stmt->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        return $record['cnt'];
    } catch (PDOException $e) {
        error_log("Error fetching count from $table: " . $e->getMessage());
        return 0;
    }
}

$employeecount = getCount($conn, 'candidates', 'status = "Employed"');
$clientsCount = getCount($conn, 'clients');
$jobsCount = getCount($conn, 'jobs', "status='Active'");
$invoicesCount = getCount($conn, 'invoices');
?>
<?php include('include/sidebar.php'); ?>
<?php include('include/header.php'); ?>

<div class="page-content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="btn-group float-right">
                        <ol class="breadcrumb hide-phone p-0 m-0">
                            <li class="breadcrumb-item active"></li>
                        </ol>
                    </div>
                    <h4 class="page-title">Dashboard</h4>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="row dashboard">
            <!-- Total Applicants Card -->
            <div class="col-md-6">
                <div class="card bg-success">
                    <div class="card-body py-4">
                        <div class="d-flex flex-row p-3">
                            <div class="col-3 align-self-center">
                                <div class="round">
                                    <i class="fas fa-gas-pump"></i>
                                </div>
                            </div>
                            <div class="col-9 align-self-center text-right">
                                <div class="m-l-10">
                                    <h3 class="mt-0 text-white">
                                        <?php echo htmlspecialchars($employeecount); ?>
                                    </h3>
                                    <p class="mb-0 text-white">Total Employees</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Clients Card -->
            <div class="col-md-6">
                <div class="card bg-primary">
                    <div class="card-body py-4">
                        <div class="d-flex flex-row p-3">
                            <div class="col-3 align-self-center">
                                <div class="round">
                                    <i class="fas fa-truck-arrow-right"></i>
                                </div>
                            </div>
                            <div class="col-9 align-self-center text-right">
                                <div class="m-l-10">
                                    <h3 class="mt-0 text-white">
                                        <?php echo htmlspecialchars($clientsCount); ?>
                                    </h3>
                                    <p class="mb-0 text-white">Total Clients</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Jobs Card -->
            <div class="col-md-6">
                <div class="card bg-danger">
                    <div class="card-body py-4">
                        <div class="d-flex flex-row p-3">
                            <div class="col-3 align-self-center">
                                <div class="round">
                                    <i class="fa fa-file"></i>
                                </div>
                            </div>
                            <div class="col-9 align-self-center text-right">
                                <div class="m-l-10">
                                    <h3 class="mt-0 text-white">
                                        <?php echo htmlspecialchars($jobsCount); ?>
                                    </h3>
                                    <p class="mb-0 text-white">Total Jobs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Invoices Card -->
            <div class="col-md-6">
                <div class="card bg-secondary">
                    <div class="card-body py-4">
                        <div class="d-flex flex-row p-3">
                            <div class="col-3 align-self-center">
                                <div class="round">
                                    <i class="fa fa-user"></i>
                                </div>
                            </div>
                            <div class="col-9 align-self-center text-right">
                                <div class="m-l-10">
                                    <h3 class="mt-0 text-white">
                                        <?php echo htmlspecialchars($invoicesCount); ?>
                                    </h3>
                                    <p class="mb-0 text-white">Total Invoices</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Applicants Table -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h3>Job Applicants</h3>
                        <br>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Applicant Name</th>
                                        <th>Email</th>
                                        <th>Sex</th>
                                        <th>Age</th>
                                        <th>Application Status</th>
                                        <th>Application Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $sql = "
                                            SELECT 
                                                CONCAT(last_name, ', ', first_name, ' ', middle_name) AS candidate_name, 
                                                email, 
                                                sex, 
                                                age, 
                                                status,
                                                created_at
                                            FROM 
                                                candidates
                                            WHERE 
                                                status != 'Employed' ";
                                        $statement = $conn->prepare($sql);
                                        $statement->execute();
                                        $i = 1;
                                        while ($application = $statement->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($i) . "</td>";
                                            echo "<td>" . htmlspecialchars($application['candidate_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($application['email']) . "</td>";
                                            echo "<td>" . htmlspecialchars($application['sex']) . "</td>";
                                            echo "<td>" . htmlspecialchars($application['age']) . "</td>";
                                            echo "<td>" . htmlspecialchars($application['status']) . "</td>";
                                            echo "<td>" . htmlspecialchars($application['created_at']) . "</td>";
                                            echo "</tr>";
                                            $i++;
                                        }
                                    } catch (PDOException $e) {
                                        error_log("Error fetching applicants: " . $e->getMessage());
                                        echo "<tr><td colspan='7'>Error fetching data. Please try again later.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
