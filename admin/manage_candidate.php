<?php
session_start();
include '../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the base query
    $base_query = "
    SELECT * FROM candidates 
    WHERE status != 'Employed'";

    // Check if search form is submitted
    if (isset($_POST['search'])) {
        // Prepare search query
        $search = htmlspecialchars($_POST['search'], ENT_QUOTES, 'UTF-8');
        $base_query .= " WHERE first_name LIKE :search OR last_name LIKE :search";
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
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:../manage_candidate.php");
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
                        <h4 class="mt-0 header-title">Manage Applicants</h4>
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
                                <div class="input-group">
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
                                        <th><a href="?sort=last_name&order=<?php echo ($sort == 'last_name' && $order == 'asc') ? 'desc' : 'asc'; ?>">Last Name</a></th>
                                        <th><a href="?sort=first_name&order=<?php echo ($sort == 'first_name' && $order == 'asc') ? 'desc' : 'asc'; ?>">First Name</a></th>
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
                                            <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                            <td><?php echo htmlspecialchars($candidate['phone']); ?></td>
                                            <td>
                                                <a href="edit_candidate.php?id=<?php echo $candidate['candidate_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="view_resume.php?id=<?php echo $candidate['candidate_id']; ?>" class="btn btn-sm btn-secondary">View Resume</a>
                                                <form method="POST" action="../admin/app/managecandidate_crud.php" style="display: inline-block;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                    <input type="hidden" name="candidate_id" value="<?php echo $candidate['candidate_id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this candidate?')">Delete</button>
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
