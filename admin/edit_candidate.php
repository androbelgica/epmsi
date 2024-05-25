<?php
session_start();
include '../assets/constant/config.php';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "Invalid candidate ID.";
        header("location:manage_candidates.php");
        exit();
    }

    $candidate_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE candidate_id = ?");
    $stmt->execute([$candidate_id]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidate) {
        $_SESSION['error'] = "Candidate not found.";
        header("location:manage_candidate.php");
        exit();
    }

    if (isset($_POST['update'])) {
        // CSRF token validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid CSRF token.');
        }

        $stmt = $conn->prepare("UPDATE candidates SET first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, resume = ?, skills = ?, highest_educ_attainment = ?, sex = ?, age = ?, status = ?, image = ? WHERE candidate_id = ?");
        $first_name = htmlspecialchars(strtoupper($_POST['first_name']), ENT_QUOTES, 'UTF-8');
        $middle_name = htmlspecialchars(strtoupper($_POST['middle_name']), ENT_QUOTES, 'UTF-8');
        $last_name = htmlspecialchars(strtoupper($_POST['last_name']), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
        $skills = htmlspecialchars(strtoupper($_POST['skills']), ENT_QUOTES, 'UTF-8');
        $highest_educ_attainment = htmlspecialchars($_POST['highest_educ_attainment'], ENT_QUOTES, 'UTF-8');
        $sex = htmlspecialchars($_POST['sex'], ENT_QUOTES, 'UTF-8');
        $age = htmlspecialchars($_POST['age'], ENT_QUOTES, 'UTF-8');
        $emp_status = htmlspecialchars($_POST['emp_status'], ENT_QUOTES, 'UTF-8');

        // Handle resume upload
        $resume = $candidate['resume'];
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
            $resume_dir = "../assets/images/";
            $resume_name = basename($_FILES['resume']['name']);
            $target_file = $resume_dir . $resume_name;
            move_uploaded_file($_FILES['resume']['tmp_name'], $target_file);
            $resume = $resume_name;
        }

        $image = $candidate['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            // Handle image upload
            $image_dir = "../assets/images/";
            $image_name = basename($_FILES['image']['name']);
            $target_file = $image_dir . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
            $image = $image_name;
        }
        

        $stmt->execute([$first_name, $middle_name, $last_name, $email, $phone, $resume, $skills, $highest_educ_attainment, $sex, $age, $emp_status, $image, $candidate_id]);
        $_SESSION['success'] = "Candidate updated successfully.";
        header("location:manage_candidate.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_candidate.php");
    exit();
} catch (RuntimeException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_candidate.php");
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
                        <h4 class="mt-0 header-title">Edit Candidate</h4>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($candidate['last_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($candidate['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" value="<?php echo htmlspecialchars($candidate['middle_name']); ?>" required>
                            </div>
                             <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($candidate['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($candidate['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="skills">Skills</label>
                                <textarea class="form-control" name="skills" required><?php echo htmlspecialchars($candidate['skills']); ?></textarea>
                            </div>
                                                        
                            <div class="form-group">
                                <label for="sex">Sex</label>
                                <select class="form-control" id="sex" name="sex">
                                    <option value="">~~SELECT~~</option>
                                    <option value="Male" <?php if($candidate['sex'] == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if($candidate['sex'] == 'Female') echo 'selected'; ?>>Female</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="age">Age</label>
                                <input type="text" class="form-control" name="age" value="<?php echo htmlspecialchars($candidate['age']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="resume">Resume</label>
                                <input type="file" class="form-control-file" name="resume">
                            </div>
                            <div class="form-group">
                                <label for="image">Image</label>
                                <input type="file" class="form-control-file" id="image" name="image">
                            </div>
                            <div class="form-group">
                                <label for="emp_status">Application Status</label>
                                <select class="form-control" id="emp_status" name="emp_status">
                                    <option value="">~~SELECT~~</option>
                                    <option value="Pending" <?php if($candidate['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="In Review" <?php if($candidate['status'] === 'In Review') echo 'selected'; ?>>Employed</option>
                                    <option value="Inactive" <?php if($candidate['status'] === 'Inactive') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                            <button type="submit" name="update" class="btn btn-success">Update</button>
                            <a href="manage_candidate.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->
<?php include('include/footer.php'); ?>

<script>
function toUpperCase(field) {
    field.value = field.value.toUpperCase();
}

function validateForm() {
    var firstName = document.getElementById('first_name').value;
    var lastName = document.getElementById('last_name').value;
    var age = document.getElementById('age').value;

    // Validate that the first name and last name are all uppercase
    if (firstName !== firstName.toUpperCase() || lastName !== lastName.toUpperCase()) {
        alert('First Name and Last Name must be in uppercase.');
        return false;
    }

    // Validate that the age field contains only numbers
    if (!/^\d+$/.test(age)) {
        alert('Age must be a number.');
        return false;
    }

    return true;
}

// Add the event listeners to the fields
document.getElementById('first_name').oninput = function() { toUpperCase(this); };
document.getElementById('middle_name').oninput = function() { toUpperCase(this); };
document.getElementById('last_name').oninput = function() { toUpperCase(this); };
document.getElementById('skills').oninput = function() { toUpperCase(this); };
</script>

