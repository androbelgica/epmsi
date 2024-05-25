<?php
session_start();
include '../assets/constant/config.php';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php include('include/sidebar.php'); ?>
<?php include('include/header.php'); ?>
<div class="page-content-wrapper">
    <div class="row title">
        <div class="top col-md-5 align-self-center">
            <h5>Add Candidate</h5>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Add Applicants</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8" style="margin-left: 10%;">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active p-3" id="home" role="tabpanel">
                                <?php if (isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success">
                                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                    </div>
                                <?php endif; ?>
                                <form id="add_candidate" method="POST" action="../admin/app/addcandidate_crud.php" enctype="multipart/form-data" onsubmit="return validateForm()">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Last Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control text-uppercase" id="last_name" name="last_name" required oninput="toUpperCase(this)">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">First Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control text-uppercase" id="first_name" name="first_name" required oninput="toUpperCase(this)">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Middle Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control text-uppercase" id="middle_name" name="middle_name" oninput="toUpperCase(this)">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Email</label>
                                            <div class="col-sm-9">
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Phone</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="phone" name="phone" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Resume</label>
                                            <div class="col-sm-9">
                                                <input type="file" class="form-control" id="resume" name="resume" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Skills</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control text-uppercase" id="skills" name="skills" oninput="toUpperCase(this)">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Highest Education Attainment</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="highest_educ_attainment" name="highest_educ_attainment">
                                                    <option value="">~~SELECT~~</option>
                                                    <option value="High School">High School</option>
                                                    <option value="Senior High">Senior High</option>
                                                    <option value="College">College</option>
                                                    <option value="Post Graduate">Post Graduate</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Sex</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="sex" name="sex">
                                                    <option value="">~~SELECT~~</option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Age</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="age" name="age" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="image">Image</label>
                                        <input type="file" class="form-control-file" id="image" name="image" onchange="previewImage(this)">
                                        <img id="imagePreview" src="#" alt="Image Preview" style="display: none; max-width: 200px; margin-top: 10px;">
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-3 control-label">Application Status</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="emp_status" name="emp_status" required>
                                                    <option value="">~~SELECT~~</option>
                                                    <option value="Pending">Pending</option>
                                                    <option value="Employed">In Review</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <button class="btn btn-primary" type="submit" name="submit">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- Page content Wrapper -->
</div> <!-- content -->
<?php include('include/footer.php'); ?>

<script>
// Function to convert input value to uppercase
function toUpperCase(field) {
    field.value = field.value.toUpperCase();
}

// Function to validate the form before submission
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

// Add event listeners to input fields for automatic uppercase conversion
document.getElementById('first_name').oninput = function() { toUpperCase(this); };
document.getElementById('middle_name').oninput = function() { toUpperCase(this); };
document.getElementById('last_name').oninput = function() { toUpperCase(this); };
document.getElementById('skills').oninput = function() { toUpperCase(this); };
</script>

<script>
// Function to preview image before uploading
function previewImage(input) {
    var preview = document.getElementById('imagePreview');
    var file = input.files[0];
    var reader = new FileReader();

    reader.onloadend = function() {
        preview.src = reader.result;
        preview.style.display = 'block'; // Display the image preview
    }

    if (file) {
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
    }
}
</script>
