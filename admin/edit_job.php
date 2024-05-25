<?php
session_start();
include '../assets/constant/config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "Invalid job ID.";
        header("location:manage_jobs.php");
        exit();
    }

    $job_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT j.*, c.company_name 
                            FROM jobs j 
                            LEFT JOIN clients c ON j.client_id = c.client_id
                            WHERE j.job_id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        $_SESSION['error'] = "Job not found.";
        header("location:manage_jobs.php");
        exit();
    }

    if (isset($_POST['update'])) {
        // CSRF token validation
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid CSRF token.');
        }

        $stmt = $conn->prepare("UPDATE jobs 
                                SET title = ?, description = ?, requirements = ?, posted_date = ?, slot = ?, status = ? 
                                WHERE job_id = ?");

        $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
        $requirements = htmlspecialchars($_POST['requirements'], ENT_QUOTES, 'UTF-8');
        $posted_date = htmlspecialchars($_POST['posted_date'], ENT_QUOTES, 'UTF-8');
        $slot = htmlspecialchars($_POST['slot'], ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');

        $stmt->execute([$title, $description, $requirements, $posted_date, $slot, $status, $job_id]);

        $_SESSION['success'] = "Job updated successfully.";
        header("location:manage_job.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_job.php");
    exit();
} catch (RuntimeException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_job.php");
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
                        <h4 class="mt-0 header-title">Edit Job</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="client_name">Client</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($job['company_name']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" name="description" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="requirements">Requirements</label>
                                <textarea class="form-control" name="requirements"><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="posted_date">Posted Date</label>
                                <input type="date" class="form-control" name="posted_date" value="<?php echo htmlspecialchars($job['posted_date']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="slot">Availale Slot</label>
                                <input type="slot" class="form-control" name="slot" value="<?php echo htmlspecialchars($job['slot']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="Active" <?php echo $job['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Closed" <?php echo $job['status'] == 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <button type="submit" name="update" class="btn btn-success">Update</button>
                            <a href="manage_job.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
