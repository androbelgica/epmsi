<?php
session_start();
include '../assets/constant/config.php';

// Check if client ID is provided in the query string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid client ID.";
    header("location:manage_client.php");
    exit();
}

$client_id = $_GET['id'];

// Fetch client and salary constants details
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
        SELECT clients.company_name, client_salary_constants.*
        FROM clients
        LEFT JOIN client_salary_constants ON clients.client_id = client_salary_constants.client_id
        WHERE clients.client_id = :client_id
    ");
    $stmt->bindParam(':client_id', $client_id);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        $_SESSION['error'] = "Client not found.";
        header("location:manage_client.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:manage_client.php");
    exit();
}

// Update client constants details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [
        'b_ot', 'nsd', 'nsd_ot', 'rdd', 'rdd_ot', 'rdnsd', 'rdnsd_ot', 'sh', 
        'sh_ot', 'shnsd', 'shnsd_ot', 'lh', 'lh_ot', 'lhnsd', 'lhnsd_ot', 
        'shrd', 'shrd_ot', '13th', 'sil'
    ];

    // Prepare the update statement
    $updateStmt = $conn->prepare("
        UPDATE client_salary_constants 
        SET 
            b_ot = :b_ot, nsd = :nsd, nsd_ot = :nsd_ot, rdd = :rdd, rdd_ot = :rdd_ot,
            rdnsd = :rdnsd, rdnsd_ot = :rdnsd_ot, sh = :sh, sh_ot = :sh_ot, shnsd = :shnsd,
            shnsd_ot = :shnsd_ot, lh = :lh, lh_ot = :lh_ot, lhnsd = :lhnsd, lhnsd_ot = :lhnsd_ot,
            shrd = :shrd, shrd_ot = :shrd_ot, 13th = :13th, sil = :sil
        WHERE client_id = :client_id
    ");

    foreach ($fields as $field) {
        $updateStmt->bindValue(":$field", $_POST[$field]);
    }
    $updateStmt->bindValue(':client_id', $client_id);

    try {
        // Start transaction
        $conn->beginTransaction();

        // Execute the update statement
        $updateStmt->execute();

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "Client Payroll Info updated successfully.";
        header("location:manage_client.php");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction if an error occurs
        $conn->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("location:edit_deduction.php?id=" . $client_id);
        exit();
    }
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
                        <h4 class="mt-0 header-title">Modify Payroll Information for <?php echo htmlspecialchars($client['company_name']); ?></h4>
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
                            <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client['client_id']); ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="b_ot">Basic Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="b_ot" name="b_ot" value="<?php echo htmlspecialchars($client['b_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="nsd">Night Shift Differential</label>
                                        <input type="number" step="0.01" class="form-control" id="nsd" name="nsd" value="<?php echo htmlspecialchars($client['nsd'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="rdd">Rest Day</label>
                                        <input type="number" step="0.01" class="form-control" id="rdd" name="rdd" value="<?php echo htmlspecialchars($client['rdd'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="rdnsd">Rest Day Night Shift Differential</label>
                                        <input type="number" step="0.01" class="form-control" id="rdnsd" name="rdnsd" value="<?php echo htmlspecialchars($client['rdnsd'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="sh">Special Holiday</label>
                                        <input type="number" step="0.01" class="form-control" id="sh" name="sh" value="<?php echo htmlspecialchars($client['sh'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="shnsd">Special Holiday Night Shift Differential</label>
                                        <input type="number" step="0.01" class="form-control" id="shnsd" name="shnsd" value="<?php echo htmlspecialchars($client['shnsd'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="lh">Legal Holiday</label>
                                        <input type="number" step="0.01" class="form-control" id="lh" name="lh" value="<?php echo htmlspecialchars($client['lh'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="lhnsd">Legal Holiday Night Shift Differential</label>
                                        <input type="number" step="0.01" class="form-control" id="lhnsd" name="lhnsd" value="<?php echo htmlspecialchars($client['lhnsd'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="shrd">Special Holiday Rest Day</label>
                                        <input type="number" step="0.01" class="form-control" id="shrd" name="shrd" value="<?php echo htmlspecialchars($client['shrd'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="13th">13th Month Pay</label>
                                        <input type="number" step="0.01" class="form-control" id="13th" name="13th" value="<?php echo htmlspecialchars($client['13th'] ?? 0); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                    <div class="form-group" >
                                            <label for="nsd_ot">
                                                
                                            </label>
                                         </div>
                                         <div class="form-group" >
                                            <label for="nsd_ot"></label>
                                         </div>
                                        <label for="nsd_ot">Night Shift Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="nsd_ot" name="nsd_ot" value="<?php echo htmlspecialchars($client['nsd_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="rdd_ot">Rest Day Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="rdd_ot" name="rdd_ot" value="<?php echo htmlspecialchars($client['rdd_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="rdnsd_ot">Rest Day Night Shift Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="rdnsd_ot" name="rdnsd_ot" value="<?php echo htmlspecialchars($client['rdnsd_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="sh_ot">Special Holiday Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="sh_ot" name="sh_ot" value="<?php echo htmlspecialchars($client['sh_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="shnsd_ot">Special Holiday Night Shift Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="shnsd_ot" name="shnsd_ot" value="<?php echo htmlspecialchars($client['shnsd_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="lh_ot">Legal Holiday Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="lh_ot" name="lh_ot" value="<?php echo htmlspecialchars($client['lh_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="lhnsd_ot">Legal Holiday Night Shift Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="lhnsd_ot" name="lhnsd_ot" value="<?php echo htmlspecialchars($client['lhnsd_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="shrd_ot">Special Holiday Rest Day Overtime</label>
                                        <input type="number" step="0.01" class="form-control" id="shrd_ot" name="shrd_ot" value="<?php echo htmlspecialchars($client['shrd_ot'] ?? 0); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="sil">Service Incentive Leave</label>
                                        <input type="number" step="0.01" class="form-control" id="sil" name="sil" value="<?php echo htmlspecialchars($client['sil'] ?? 0); ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div> <!-- container-fluid -->
</div> <!-- page-content-wrapper -->


<?php include('include/footer.php'); ?>
