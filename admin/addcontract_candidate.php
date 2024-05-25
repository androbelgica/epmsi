<?php
session_start();
include '../assets/constant/config.php';

// Retrieve candidate_id from the URL parameter
if (isset($_GET['id'])) {
    $candidate_id = $_GET['id'];
} else {
    // Redirect or handle error if candidate_id is not provided
    $_SESSION['error'] = "Candidate ID not provided.";
    header("location:../manage_candidate.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch candidate details
    $stmt = $conn->prepare("SELECT first_name, last_name, middle_name, image FROM candidates WHERE candidate_id = ?");
    $stmt->execute([$candidate_id]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidate) {
        $_SESSION['error'] = "Candidate not found.";
        header("location:manage_candidate.php");
        exit();
    }

    // Fetch clients
    $stmt = $conn->query("SELECT client_id, company_name FROM clients");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $client_id = $_POST['client_id'];
        $job_id = $_POST['job_id'];

        // Validate form data
        if (!empty($client_id) && !empty($job_id) && isset($_POST['start_date'])) {
            // Process form data (update database, etc.)
            // Redirect or set success message
            $_SESSION['success'] = "Form submitted successfully!";
            header("location:manage_candidate.php");
            exit();
        } else {
            $_SESSION['error'] = "Please fill in all required fields.";
        }
    }

} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_candidate.php");
    exit();
}
?>

<?php include('include/sidebar.php'); ?>
<?php include('include/header.php'); ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const clientSelect = document.getElementById('client_id');
        const jobSelect = document.getElementById('job_id');

        clientSelect.addEventListener('change', function() {
            const selectedClientId = clientSelect.value;

            // Clear existing job options
            jobSelect.innerHTML = '<option value="">Select Job</option>';

            // Fetch jobs associated with the selected client
            fetch('get_jobs.php?client_id=' + selectedClientId)
                .then(response => response.json())
                .then(jobs => {
                    jobs.forEach(job => {
                        const option = document.createElement('option');
                        option.value = job.job_id;
                        option.textContent = job.title;
                        jobSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching jobs:', error));
        });
    });
</script>
<div class="page-content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mt-0 header-title"></h4>
                        <div class="candidate-info">
                            <h5>Hire Applicant: <?php echo htmlspecialchars($candidate['last_name'] . ', ' . $candidate['first_name'] . ' ' . $candidate['middle_name']); ?></h5>
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
                                <label for="client_id">Select Client</label>
                                <select class="form-control" id="client_id" name="client_id">
                                    <option value="">Select Client</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo htmlspecialchars($client['client_id']); ?>"><?php echo htmlspecialchars($client['company_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="job_id">Select Job</label>
                                <select class="form-control" id="job_id" name="job_id">
                                    <option value="">Select Job</option>
                                </select>
                            </div>
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
                            <a href="manage_candidate.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container-fluid -->
</div><!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>
