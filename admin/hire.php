<?php
session_start();
include '../assets/constant/config.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid job ID.";
    header("location:manage_job.php");
    exit();
}

$job_id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
        header("location:hire.php");
        exit();
    }

    // Prepare the base query for candidates excluding those already in contracts table and not
    $base_query = "
        SELECT * FROM candidates 
        WHERE status != 'Employed'
    ";

   

    // Check if search form is submitted
    $search = '';
    if (isset($_POST['search'])) {
        if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $search = htmlspecialchars($_POST['search'], ENT_QUOTES, 'UTF-8');
            $base_query .= " AND (first_name LIKE :search OR last_name LIKE :search)";
        } else {
            $_SESSION['error'] = "Invalid CSRF token.";
            header("location:hire.php");
            exit();
        }
    }

    // Sorting functionality
    $sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort'], ENT_QUOTES, 'UTF-8') : 'first_name';
    $order = isset($_GET['order']) && ($_GET['order'] == 'asc' || $_GET['order'] == 'desc') ? htmlspecialchars($_GET['order'], ENT_QUOTES, 'UTF-8') : 'asc';
    $base_query .= " ORDER BY $sort $order";

    $stmt = $conn->prepare($base_query);
    if (isset($_POST['search'])) {
        $stmt->bindValue(':search', '%' . $search . '%');
    }
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any candidates are found
    if (!$candidates) {
        $_SESSION['error'] = "No candidates found.";
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while fetching candidates. Please try again later.";
    header("location:../hire.php");
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
                        <h4 class="mt-0 header-title">Select applicant for <?php echo htmlspecialchars($job['company_name']); ?> as <?php echo htmlspecialchars($job['title']); ?></h4>
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
                        <div class="mb-4">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Search by first name or last name" value="<?php echo htmlspecialchars($search); ?>">
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
                                        <th><a href="?id=<?php echo $job_id; ?>&sort=last_name&order=<?php echo ($sort == 'last_name' && $order == 'asc') ? 'desc' : 'asc'; ?>">Last Name</a></th>
                                        <th><a href="?id=<?php echo $job_id; ?>&sort=first_name&order=<?php echo ($sort == 'first_name' && $order == 'asc') ? 'desc' : 'asc'; ?>">First Name</a></th>
                                        <th>Highest Educ. Attainment</th>
                                        <th>Sex</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidates as $candidate): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($candidate['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['first_name']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['highest_educ_attainment']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['sex']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['phone']); ?></td>
                                            <td>
                                                <a href="view_resume.php?id=<?php echo htmlspecialchars($candidate['candidate_id']); ?>" class="btn btn-sm btn-secondary">View Resume</a>
                                                <a href="add_contracts.php?candidate_id=<?php echo htmlspecialchars($candidate['candidate_id']); ?>&job_id=<?php echo htmlspecialchars($job_id); ?>" class="btn btn-sm btn-success">Hire</a>
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
