<?php
session_start();
include '../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch existing job titles from the database
    $existingJobTitles_stmt = $conn->query("SELECT DISTINCT title FROM jobs");
    $existingJobTitles = $existingJobTitles_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Retrieve list of clients from the database
    $clients_stmt = $conn->query("SELECT client_id, company_name FROM clients ORDER BY company_name");
    $clients = $clients_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_POST['add'])) {
        // CSRF token validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid CSRF token.');
        }

        // Retrieve client ID from the form
        $client_id = $_POST['client'];

        // Prepare and execute SQL statement to insert job data into the database
        $stmt = $conn->prepare("INSERT INTO jobs (client_id, title, description, requirements, posted_date, slot, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
       
        $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
        $requirements = htmlspecialchars($_POST['requirements'], ENT_QUOTES, 'UTF-8');
        $posted_date = htmlspecialchars($_POST['posted_date'], ENT_QUOTES, 'UTF-8');
        $slot = htmlspecialchars($_POST['slot'], ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');

        $stmt->execute([$client_id, $title, $description, $requirements, $posted_date, $slot, $status]);

        $_SESSION['success'] = "Job added successfully.";
        header("location:add_job.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:add_job.php");
    exit();
} catch (RuntimeException $e) {
    $_SESSION['error'] = $e->getMessage();
    header("location:add_job.php");
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
                        <h4 class="mt-0 header-title">Add Job</h4>
                        <form method="POST" action="" onsubmit="return validateForm()">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="client">Client</label>
                                <select class="form-control" name="client" required>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo $client['client_id']; ?>"><?php echo $client['company_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control text-uppercase" name="title" id="title" list="jobTitles" required oninput="toUpperCase(this)">
                                <datalist id="jobTitles">
                                    <?php foreach ($existingJobTitles as $jobTitle): ?>
                                        <option value="<?php echo $jobTitle; ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" name="description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="requirements">Requirements</label>
                                <textarea class="form-control" name="requirements"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="posted_date">Posted Date</label>
                                <input type="date" class="form-control" name="posted_date" required>
                            </div>
                            <div class="form-group">
                                <label for="slot">Available Slot</label>
                                <input type="number" class="form-control" name="slot" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                            <button type="submit" name="add" class="btn btn-success">Add Job</button>
                            <a href="manage_job.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>

<script>
// Function to convert input value to uppercase
function toUpperCase(field) {
    field.value = field.value.toUpperCase();
}

// Function to validate the form before submission
function validateForm() {
    var title = document.getElementById('title').value;

    // Validate that the title is in uppercase
    if (title !== title.toUpperCase()) {
        alert('Title must be in uppercase.');
        return false;
    }

    return true;
}

// Add event listener to the title input field for automatic uppercase conversion
document.getElementById('title').oninput = function() { toUpperCase(this); };
</script>
