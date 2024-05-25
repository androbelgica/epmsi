<?php
session_start();
include '../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch job data with company name from the database
    $stmt = $conn->query("
        SELECT j.*, c.company_name 
        FROM jobs j 
        LEFT JOIN clients c ON j.client_id = c.client_id
    ");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any jobs are found
    if (!$jobs) {
        $_SESSION['error'] = "No jobs found.";
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while fetching jobs. Please try again later.";
    header("location: manage_job.php");
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
                        <h4 class="mt-0 header-title">Manage Jobs</h4>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Posted Date</th>
                                        <th>Company</th>
                                        <th>Available Slot</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($job['title']); ?></td>
                                            <td><?php echo htmlspecialchars($job['description']); ?></td>
                                            <td><?php echo htmlspecialchars($job['posted_date']); ?></td>
                                            <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($job['slot']); ?></td>
                                            <td><?php echo htmlspecialchars($job['status']); ?></td>
                                            <td>
                                                <a href="edit_job.php?id=<?php echo htmlspecialchars($job['job_id']); ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <?php if ($job['slot'] > 0): ?>
                                                    <a href="hire.php?id=<?php echo htmlspecialchars($job['job_id']); ?>" class="btn btn-sm btn-success">Select Applicant</a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>No Slot Available</button>
                                                <?php endif; ?>
                                                <form method="POST" action="../admin/app/deletejob_crud.php" style="display: inline-block;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['job_id']); ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this job?')">Delete</button>
                                                </form>
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
