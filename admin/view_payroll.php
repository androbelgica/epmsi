<?php
session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include '../assets/constant/config.php';

// Check if the contract ID is set in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid payroll ID.";
    header("location:list_payroll.php");
    exit();
}

// Get the contract ID from the URL
$payroll_id = (int)$_GET['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch full name and daily rate info based on contract_id
    $sql = "
    SELECT 
        p.contract_id,
        p.period_start,
        p.period_end,
        p.total_days,
        p.daily_rate,
        p.gross_pay,
        p.net_pay,
        p.basic_salary,
        p.overtime_hours,
        p.overtime_pay,
        p.special_non_working_holiday_hours,
        p.special_non_working_holiday_pay,
        p.regular_holiday_hours,
        p.regular_holiday_pay,
        p.allowances,
        p.bonuses,
        p.sss_contribution,
        p.philhealth_contribution,
        p.pagibig_contribution,
        p.withholding_tax,
        p.other_deductions,
        CONCAT(c.last_name, ' ', c.first_name, ' ', c.middle_name) AS full_name,
        cl.company_name
    FROM 
        payroll p
    INNER JOIN 
        contracts co ON p.contract_id = co.contract_id
    INNER JOIN 
        candidates c ON co.candidate_id = c.candidate_id
    INNER JOIN 
        jobs j ON co.job_id = j.job_id
    INNER JOIN 
        clients cl ON j.client_id = cl.client_id
    WHERE 
        p.payroll_id = :payroll_id;
";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':payroll_id', $payroll_id, PDO::PARAM_INT);
    $stmt->execute();
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:../admin/list_payroll.php");
    exit();
}
?>

<?php include('include/header.php'); ?>
<?php include('include/sidebar.php'); ?>


<div class="container">
    <h2>View Payroll</h2>
    <form method="POST" action="" onsubmit="return validateForm()">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        
        <div class="form-group">
            <h4>Employee Info</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="employee_name">Name:</label>
                    <input type="text" id="employee_name" name="employee_name" class="form-control" value="<?php echo htmlspecialchars($contract['full_name'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="period_start">Period Start:</label>
                    <input type="date" id="period_start" name="period_start" class="form-control" value="<?php echo htmlspecialchars($contract['period_start'] ?? ''); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Total Days</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="total_days">Total Days:</label>
                    <input type="number" id="total_days" name="total_days" class="form-control" value="<?php echo htmlspecialchars($contract['total_days'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="period_end">Period End:</label>
                    <input type="date" id="period_end" name="period_end" class="form-control" value="<?php echo htmlspecialchars($contract['period_end'] ?? ''); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Basic Salary</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="daily_rate">Daily Rate:</label>
                    <input type="number" step="0.01" id="daily_rate" name="daily_rate" class="form-control" value="<?php echo htmlspecialchars($contract['daily_rate'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="basic_salary">Basic Salary:</label>
                    <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-control" value="<?php echo htmlspecialchars($contract['basic_salary'] ?? ''); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Overtime</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="overtime_rate_per_hour">Rate per hour:</label>
                    <input type="number" step="0.01" id="overtime_rate_per_hour" name="overtime_rate_per_hour" class="form-control" value="<?php echo htmlspecialchars($contract['overtime_rate_per_hour'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="overtime_hours">Overtime Hours:</label>
                    <input type="number" step="0.01" id="overtime_hours" name="overtime_hours" class="form-control" value="<?php echo htmlspecialchars($contract['overtime_hours'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="overtime_pay">Overtime Pay:</label>
                    <input type="number" step="0.01" id="overtime_pay" name="overtime_pay" class="form-control" value="<?php echo htmlspecialchars($contract['overtime_pay'] ?? ''); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Holidays</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="special_non_working_holiday">Special Non-Working Holiday:</label>
                    <input type="number" step="0.01" id="special_non_working_holiday" name="special_non_working_holiday" class="form-control" value="<?php echo htmlspecialchars($contract['special_non_working_holiday_hours'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="special_non_working_holiday_pay">Special Non-Working Holiday Pay:</label>
                    <input type="number" step="0.01" id="special_non_working_holiday_pay" name="special_non_working_holiday_pay" class="form-control" value="<?php echo htmlspecialchars($contract['special_non_working_holiday_pay'] ?? ''); ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="regular_holiday">Regular Holiday:</label>
                    <input type="number" step="0.01" id="regular_holiday" name="regular_holiday" class="form-control" value="<?php echo htmlspecialchars($contract['regular_holiday_hours'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="regular_holiday_pay">Regular Holiday Pay:</label>
                    <input type="number" step="0.01" id="regular_holiday_pay" name="regular_holiday_pay" class="form-control" value="<?php echo htmlspecialchars($contract['regular_holiday_pay'] ?? ''); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Allowances and Bonuses</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="allowances">Allowances:</label>
                    <input type="number" step="0.01" id="allowances" name="allowances" class="form-control" value="<?php echo htmlspecialchars($contract['allowances'] ?? ''); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="bonuses">Bonuses:</label>
                    <input type="number" step="0.01" id="bonuses" name="bonuses" class="form-control" value="<?php echo htmlspecialchars($contract['bonuses'] ?? ''); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Deductions</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="sss_contribution">SSS Contribution:</label>
                    <input type="number" step="0.01" id="sss_contribution" name="sss_contribution" class="form-control" value="<?php echo htmlspecialchars($contract['sss'] ?? 0); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="philhealth_contribution">Philhealth Contribution:</label>
                    <input type="number" step="0.01" id="philhealth_contribution" name="philhealth_contribution" class="form-control" value="<?php echo htmlspecialchars($contract['philhealth'] ?? 0); ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="pagibig_contribution">Pagibig Contribution:</label>
                    <input type="number" step="0.01" id="pagibig_contribution" name="pagibig_contribution" class="form-control" value="<?php echo htmlspecialchars($contract['pagibig_contribution'] ?? 0); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="withholding_tax">Withholding Tax:</label>
                    <input type="number" step="0.01" id="withholding_tax" name="withholding_tax" class="form-control" value="<?php echo htmlspecialchars($contract['withholding_tax'] ?? 0); ?>" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="other_deductions">Other Deductions:</label>
                    <input type="number" step="0.01" id="other_deductions" name="other_deductions" class="form-control" value="<?php echo htmlspecialchars($contract['other_deductions'] ?? 0); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Gross and Net Pay</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="gross_pay">Gross Pay:</label>
                    <input type="number" step="0.01" id="gross_pay" name="gross_pay" class="form-control" value="<?php echo htmlspecialchars($contract['gross_pay'] ?? 0); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="net_pay">Net Pay:</label>
                    <input type="number" step="0.01" id="net_pay" name="net_pay" class="form-control" value="<?php echo htmlspecialchars($contract['net_pay'] ?? 0); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="form-group">
            <td>
                <a href="../admin/print_payroll.php?id=<?php echo $conn['payroll_id']; ?>" class="btn btn-primary">Print Payroll</a>
                <a href="../admin/view_payroll.php?id=<?php echo $contract['payroll_id']; ?>" class="btn btn-success">Edit Payroll</a>
                <a href="print_payslip.php?id=<?php echo $contract['payroll_id']; ?>" class="btn btn-sm btn-warning">Delete Payroll</a>
            </td>
        </div>
    </form>
</div>

<?php include('include/footer.php'); ?>

