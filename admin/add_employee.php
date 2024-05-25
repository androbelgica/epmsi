<?php
session_start();
include '../assets/constant/config.php';

if (!isset($_GET['candidate_id']) || !isset($_GET['job_id'])) {
    $_SESSION['error'] = "Invalid candidate ID or job ID.";
    header("location:manage_job.php");
    exit();
}

$candidate_id = $_GET['candidate_id'];
$job_id = $_GET['job_id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch job and company details
    $stmt = $conn->prepare("
        SELECT j.title, c.company_name 
        FROM jobs j 
        LEFT JOIN clients c ON j.client_id = c.client_id
        WHERE j.job_id = ?
    ");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        $_SESSION['error'] = "Job not found.";
        header("location:manage_jobs.php");
        exit();
    }

    // Fetch candidate details
    $stmt = $conn->prepare("SELECT first_name, last_name, middle_name, image FROM candidates WHERE candidate_id = ?");
    $stmt->execute([$candidate_id]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
    

    if (!$candidate) {
        $_SESSION['error'] = "Candidate not found.";
        header("location:hire.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get form data
        $start_date = $_POST['start_date'];
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
        $designation = !empty($_POST['designation']) ? $_POST['designation'] : NULL;
        $daily_rate = !empty($_POST['daily_rate']) ? $_POST['daily_rate'] : NULL;
        $emp_status = $_POST['emp_status'];
        $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;

        try {
            // Start transaction
            $conn->beginTransaction();

            // Insert into employees table
            $stmt = $conn->prepare("
                INSERT INTO contracts (candidate_id, job_id, start_date, end_date, designation, daily_rate, emp_status, remarks) 
                VALUES (:candidate_id, :job_id, :start_date, :end_date, :designation, :daily_rate, :emp_status, :remarks)
            ");
            $stmt->bindParam(':candidate_id', $candidate_id);
            $stmt->bindParam(':job_id', $job_id);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':designation', $designation);
            $stmt->bindParam(':daily_rate', $daily_rate);
            $stmt->bindParam(':emp_status', $emp_status);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->execute();

           
            $_SESSION['success'] = "Contract added successfully.";
            header("location:manage_employees.php");
            exit();
        } catch (PDOException $e) {
            // Rollback transaction if an error occurs
            $conn->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("location:add_employee.php?candidate_id=$candidate_id&job_id=$job_id");
            exit();
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:hire.php");
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
                        <h4 class="mt-0 header-title">Add Employee for <?php echo htmlspecialchars($job['company_name']); ?> as <?php echo htmlspecialchars($job['title']); ?></h4>
                        <div class="candidate-info">
                            <h5>Candidate: <?php echo htmlspecialchars($candidate['last_name'] . ', ' . $candidate['first_name'] . ' ' . $candidate['middle_name']); ?></h5>
                            <?php if ($candidate['image']): ?>
                                <img src="../assets/images/<?php echo htmlspecialchars($candidate['image']); ?>" alt="Candidate Image" style="width:100px; height:100px;">
                            <?php else: ?>
                                <img src="../assets/images/default.png" alt="Candidate Image" style="width:100px; height:100px;">
                            <?php endif; ?>
                        </div>
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
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                            <div class="form-group">
                                <label for="designation">Designation</label>
                                <input type="text" class="form-control" id="designation" name="designation">
                            </div>
                            <div class="form-group">
                                <label for="daily_rate">Daily Rate</label>
                                <input type="number" step="0.01" class="form-control" id="daily_rate" name="daily_rate">
                            </div>
                            <div class="form-group">
                                <label for="emp_status">Employment Status</label>
                                <select class="form-control" id="emp_status" name="emp_status" required>
                                    <option value="">~~SELECT~~</option>
                                    <option value="On-Going">On-Going</option>
                                    <option value="AWOL">AWOL</option>
                                    <option value="Terminated">Terminated</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Employee</button>
                            <a href="manage_job.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container-fluid -->
</div><!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
