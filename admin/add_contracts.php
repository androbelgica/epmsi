<?php
session_start();
include '../assets/constant/config.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

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
        $emp_status = "Employed";
        $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;

        // Deduction fields with default value of 0
        $sss = !empty($_POST['sss']) ? $_POST['sss'] : 0;
        $pagibig = !empty($_POST['pagibig']) ? $_POST['pagibig'] : 0;
        $philhealth = !empty($_POST['philhealth']) ? $_POST['philhealth'] : 0;
        $insurance = !empty($_POST['insurance']) ? $_POST['insurance'] : 0;
        $w_tax = !empty($_POST['w_tax']) ? $_POST['w_tax'] : 0;
        $other_deduction = !empty($_POST['other_deduction']) ? $_POST['other_deduction'] : 0;


        try {
            // Start transaction
            $conn->beginTransaction();

            // Insert into contracts table
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

            // Get the last inserted contract ID
            $contract_id = $conn->lastInsertId();


            // Update the status of the candidate
            $stmt = $conn->prepare("
                UPDATE candidates 
                SET status = 'Employed' 
                WHERE candidate_id = :candidate_id
            ");
            $stmt->bindParam(':candidate_id', $candidate_id);
            $stmt->execute();

            // Find the current value of slot
            $stmt = $conn->prepare("
                SELECT slot FROM jobs WHERE job_id = :job_id
            ");
            $stmt->bindParam(':job_id', $job_id);
            $stmt->execute();
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_slot = $job['slot'] - 1;

            // Update the slot value in the jobs table
            $stmt = $conn->prepare("
                UPDATE jobs 
                SET slot = :new_slot
                WHERE job_id = :job_id
            ");
            $stmt->bindParam(':new_slot', $new_slot);
            $stmt->bindParam(':job_id', $job_id);
            $stmt->execute();

            // Insert into other_deductions table with default values of 0
            $stmt = $conn->prepare("
            INSERT INTO other_deductions (contract_id, lifeinsurance, uniforms_ppe, user_id) 
            VALUES (:contract_id, 0, 0, :user_id)
            ");
            $stmt->bindParam(':contract_id', $contract_id);
            $stmt->bindParam(':user_id', $_SESSION['id']);
            $stmt->execute();

            // Insert into sss table with default values of 0
            $stmt = $conn->prepare("
            INSERT INTO sss (contract_id, ee_share, er_share, user_id) 
            VALUES (:contract_id, 0, 0, :user_id)
            ");
            $stmt->bindParam(':contract_id', $contract_id);
            $stmt->bindParam(':user_id', $_SESSION['id']);
            $stmt->execute();

            // Insert into phic table with default values of 0
            $stmt = $conn->prepare("
            INSERT INTO phic (contract_id, ee_share, er_share, user_id) 
            VALUES (:contract_id, 0, 0, :user_id)
            ");
            $stmt->bindParam(':contract_id', $contract_id);
            $stmt->bindParam(':user_id', $_SESSION['id']);
            $stmt->execute();

            // Insert into ec table with default values of 0
            $stmt = $conn->prepare("
            INSERT INTO ec (contract_id, ee_share, er_share, user_id) 
            VALUES (:contract_id, 0, 0, :user_id)
            ");
            $stmt->bindParam(':contract_id', $contract_id);
            $stmt->bindParam(':user_id', $_SESSION['id']);
            $stmt->execute();

            // Insert into hdmf table with default values of 0
            $stmt = $conn->prepare("
            INSERT INTO hdmf (contract_id, ee_share, er_share, user_id) 
            VALUES (:contract_id, 0, 0, :user_id)
            ");
            $stmt->bindParam(':contract_id', $contract_id);
            $stmt->bindParam(':user_id', $_SESSION['id']);
            $stmt->execute();


            // Commit transaction
            $conn->commit();

            $_SESSION['success'] = "Contract and deductions added, and candidate status updated successfully.";
            header("location:manage_job.php");
            exit();
        } catch (PDOException $e) {
            // Rollback transaction if an error occurs
            $conn->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("location:manage_job.php?candidate_id=$candidate_id&job_id=$job_id");
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
                                <label for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <a href="manage_job.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container-fluid -->
</div><!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
