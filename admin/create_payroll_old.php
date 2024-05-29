<?php
session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include '../assets/constant/config.php';

// Check if the contract ID is set in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid contract ID.";
    header("location:manage_employees.php");
    exit();
}

// Get the contract ID from the URL
$contract_id = (int)$_GET['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch full name and daily rate info based on contract_id
    $query = "
        SELECT ca.first_name, ca.last_name, c.daily_rate, d.sss, d.pagibig, d.philhealth, d.insurance, d.w_tax, d.other_deduction
        FROM contracts c
        JOIN candidates ca ON c.candidate_id = ca.candidate_id
        LEFT JOIN deductions d ON c.contract_id = d.contract_id
        WHERE c.contract_id = :contract_id
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
    $stmt->execute();
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($_POST['save'])) {

        // CSRF token validation
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new RuntimeException('Invalid CSRF token.');
        }

        // Get form data
        $period_start = $_POST['period_start'];
        $period_end = $_POST['period_end'];
        $total_days = $_POST['total_days'];
        $daily_rate = $_POST['daily_rate'];
        $basic_salary = $_POST['basic_salary'];
        $overtime_hours = $_POST['overtime_hours'];
        $overtime_pay = $_POST['overtime_pay'];
        $special_non_working_holiday_hours = $_POST['special_non_working_holiday'];
        $special_non_working_holiday_pay = $_POST['special_non_working_holiday_pay'];
        $regular_holiday_hours = $_POST['regular_holiday'];
        $regular_holiday_pay = $_POST['regular_holiday_pay'];
        $allowances = $_POST['allowances'];
        $bonuses = $_POST['bonuses'];
        $gross_pay = $_POST['gross_pay'];
        $sss_contribution = $_POST['sss_contribution'];
        $philhealth_contribution = $_POST['philhealth_contribution'];
        $pagibig_contribution = $_POST['pagibig_contribution'];
        $withholding_tax = $_POST['withholding_tax'];
        $other_deductions = $_POST['other_deductions'];
        $net_pay = $_POST['net_pay'];

        try {
            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO payroll (contract_id, period_start, period_end, total_days, daily_rate, basic_salary, overtime_hours, overtime_pay, special_non_working_holiday_hours, special_non_working_holiday_pay, regular_holiday_hours, regular_holiday_pay, allowances, bonuses, gross_pay, sss_contribution, philhealth_contribution, pagibig_contribution, withholding_tax, other_deductions, net_pay) VALUES (:contract_id, :period_start, :period_end, :total_days, :daily_rate, :basic_salary, :overtime_hours, :overtime_pay, :special_non_working_holiday_hours, :special_non_working_holiday_pay, :regular_holiday_hours, :regular_holiday_pay, :allowances, :bonuses, :gross_pay, :sss_contribution, :philhealth_contribution, :pagibig_contribution, :withholding_tax, :other_deductions, :net_pay)");

            // Bind parameters
            $stmt->bindParam(':contract_id', $contract_id);
            $stmt->bindParam(':period_start', $period_start);
            $stmt->bindParam(':period_end', $period_end);
            $stmt->bindParam(':total_days', $total_days);
            $stmt->bindParam(':daily_rate', $daily_rate);
            $stmt->bindParam(':basic_salary', $basic_salary);
            $stmt->bindParam(':overtime_hours', $overtime_hours);
            $stmt->bindParam(':overtime_pay', $overtime_pay);
            $stmt->bindParam(':special_non_working_holiday_hours', $special_non_working_holiday_hours);
            $stmt->bindParam(':special_non_working_holiday_pay', $special_non_working_holiday_pay);
            $stmt->bindParam(':regular_holiday_hours', $regular_holiday_hours);
            $stmt->bindParam(':regular_holiday_pay', $regular_holiday_pay);
            $stmt->bindParam(':allowances', $allowances);
            $stmt->bindParam(':bonuses', $bonuses);
            $stmt->bindParam(':gross_pay', $gross_pay);
            $stmt->bindParam(':sss_contribution', $sss_contribution);
            $stmt->bindParam(':philhealth_contribution', $philhealth_contribution);
            $stmt->bindParam(':pagibig_contribution', $pagibig_contribution);
            $stmt->bindParam(':withholding_tax', $withholding_tax);
            $stmt->bindParam(':other_deductions', $other_deductions);
            $stmt->bindParam(':net_pay', $net_pay);
            
            // Execute the statement
            $stmt->execute();

            // Redirect with success message
            $_SESSION['success'] = "Payroll record inserted successfully.";
            header("location:../admin/create_payroll.php");
            exit();
        } catch (PDOException $e) {
            // Redirect with error message
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("location:../admin/create_payroll.php");
            exit();
        }
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:../admin/create_payroll.php");
    exit();
}
?>

<?php include('include/header.php'); ?>
<?php include('include/sidebar.php'); ?>

<div class="container">
    <h2>Create Payroll</h2>
    <form method="POST" action="" onsubmit="return validateForm()">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="form-group">
            <h4>Employee Info</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="employee_name">Name:</label>
                    <input type="text" id="employee_name" name="employee_name" class="form-control" value="<?php echo htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="period_start">Period Start:</label>
                    <input type="date" id="period_start" name="period_start" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Total Days</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="total_days">Total Days:</label>
                    <input type="number" id="total_days" name="total_days" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="period_end">Period End:</label>
                    <input type="date" id="period_end" name="period_end" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Basic Salary</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="daily_rate">Daily Rate:</label>
                    <input type="number" step="0.01" id="daily_rate" name="daily_rate" class="form-control" value="<?php echo htmlspecialchars($contract['daily_rate']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="basic_salary">Basic Salary:</label>
                    <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Overtime</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="overtime_rate_per_hour">Rate per hour:</label>
                    <input type="number" step="0.01" id="overtime_rate_per_hour" name="overtime_rate_per_hour" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="overtime_hours">Overtime Hours:</label>
                    <input type="number" step="0.01" id="overtime_hours" name="overtime_hours" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="overtime_pay">Overtime Pay:</label>
                    <input type="number" step="0.01" id="overtime_pay" name="overtime_pay" class="form-control">
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Holidays</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="special_non_working_holiday">Special Non-Working Holiday:</label>
                    <input type="number" step="0.01" id="special_non_working_holiday" name="special_non_working_holiday" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="special_non_working_holiday_pay">Special Non-Working Holiday Pay:</label>
                    <input type="number" step="0.01" id="special_non_working_holiday_pay" name="special_non_working_holiday_pay" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="regular_holiday">Regular Holiday:</label>
                    <input type="number" step="0.01" id="regular_holiday" name="regular_holiday" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="regular_holiday_pay">Regular Holiday Pay:</label>
                    <input type="number" step="0.01" id="regular_holiday_pay" name="regular_holiday_pay" class="form-control">
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Allowances and Bonuses</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="allowances">Allowances:</label>
                    <input type="number" step="0.01" id="allowances" name="allowances" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="bonuses">Bonuses:</label>
                    <input type="number" step="0.01" id="bonuses" name="bonuses" class="form-control">
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Deductions</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="sss_contribution">SSS Contribution:</label>
                    <input type="number" step="0.01" id="sss_contribution" name="sss_contribution" class="form-control" value="<?php echo htmlspecialchars($contract['sss'] ?? 0); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="philhealth_contribution">Philhealth Contribution:</label>
                    <input type="number" step="0.01" id="philhealth_contribution" name="philhealth_contribution" class="form-control" value="<?php echo htmlspecialchars($contract['philhealth'] ?? 0); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="pagibig_contribution">Pagibig Contribution:</label>
                    <input type="number" step="0.01" id="pagibig_contribution" name="pagibig_contribution" class="form-control" value="<?php echo htmlspecialchars($contract['pagibig'] ?? 0); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="withholding_tax">Withholding Tax:</label>
                    <input type="number" step="0.01" id="withholding_tax" name="withholding_tax" class="form-control" value="<?php echo htmlspecialchars($contract['w_tax'] ?? 0); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="other_deductions">Other Deductions:</label>
                    <input type="number" step="0.01" id="other_deductions" name="other_deductions" class="form-control" value="<?php echo htmlspecialchars($contract['other_deduction'] ?? 0); ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Gross and Net Pay</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="gross_pay">Gross Pay:</label>
                    <input type="number" step="0.01" id="gross_pay" name="gross_pay" class="form-control" readonly>
                </div>
                <div class="col-md-6">
                    <label for="net_pay">Net Pay:</label>
                    <input type="number" step="0.01" id="net_pay" name="net_pay" class="form-control" readonly>
                </div>
            </div>
        </div>

        <button type="button" id="calculate_pay" class="btn btn-primary">Calculate Pay</button>
        <button type="submit" name="save" class="btn btn-success">Save Payroll</button>
    </form>
</div>


<?php include('include/footer.php'); ?>
<script>
// Calculate Basic Salary
document.getElementById('total_days').addEventListener('input', function() {
    var totalDays = parseFloat(this.value);
    var dailyRate = parseFloat(document.getElementById('daily_rate').value);
    var basicSalary = totalDays * dailyRate;
    document.getElementById('basic_salary').value = basicSalary.toFixed(2);
});

// Calculate Overtime Pay
function calculateOvertimePay() {
    var overtimeHours = parseFloat(document.getElementById("overtime_hours").value);
    var ratePerHour = parseFloat(document.getElementById("overtime_rate_per_hour").value);

    if (!isNaN(overtimeHours) && !isNaN(ratePerHour)) {
        var overtimePay = 0;
        if (overtimeHours > 0 && overtimeHours <= 8) {
            overtimePay = overtimeHours * ratePerHour;
        } else if (overtimeHours > 8 && overtimeHours <= 12) {
            overtimePay = (8 * ratePerHour) + ((overtimeHours - 8) * (ratePerHour * 2));
        } else if (overtimeHours > 12) {
            overtimePay = (8 * ratePerHour) + (4 * (ratePerHour * 2)) + ((overtimeHours - 12) * (ratePerHour * 3));
        }
        document.getElementById("overtime_pay").value = overtimePay.toFixed(2);
    } else {
        document.getElementById("overtime_pay").value = "";
    }
}

document.getElementById("overtime_hours").addEventListener("input", calculateOvertimePay);
document.getElementById("overtime_rate_per_hour").addEventListener("input", calculateOvertimePay);

// Calculate Holiday Pay
function calculateHolidayPay() {
    var specialHolidayHours = parseFloat(document.getElementById("special_non_working_holiday").value);
    var regularHolidayHours = parseFloat(document.getElementById("regular_holiday").value);
    var dailyRate = parseFloat(document.getElementById("daily_rate").value);

    var specialHolidayPay = 0;
    var regularHolidayPay = 0;

    if (!isNaN(specialHolidayHours) && !isNaN(dailyRate)) {
        specialHolidayPay = dailyRate * 1.3 * specialHolidayHours;
    }

    if (!isNaN(regularHolidayHours) && !isNaN(dailyRate)) {
        regularHolidayPay = dailyRate * regularHolidayHours;
    }

    document.getElementById("special_non_working_holiday_pay").value = specialHolidayPay.toFixed(2);
    document.getElementById("regular_holiday_pay").value = regularHolidayPay.toFixed(2);
}

document.getElementById("special_non_working_holiday").addEventListener("input", calculateHolidayPay);
document.getElementById("regular_holiday").addEventListener("input", calculateHolidayPay);
document.getElementById("daily_rate").addEventListener("input", calculateHolidayPay);

// Calculate Gross and Net Pay
function calculateGrossPay() {
    var basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
    var overtimePay = parseFloat(document.getElementById('overtime_pay').value) || 0;
    var specialHolidayPay = parseFloat(document.getElementById('special_non_working_holiday_pay').value) || 0;
    var regularHolidayPay = parseFloat(document.getElementById('regular_holiday_pay').value) || 0;
    var allowances = parseFloat(document.getElementById('allowances').value) || 0;
    var bonuses = parseFloat(document.getElementById('bonuses').value) || 0;

    var grossPay = basicSalary + overtimePay + specialHolidayPay + regularHolidayPay + allowances + bonuses;

    document.getElementById('gross_pay').value = grossPay.toFixed(2);
}

function calculateNetPay() {
    var grossPay = parseFloat(document.getElementById('gross_pay').value) || 0;
    var sssContribution = parseFloat(document.getElementById('sss_contribution').value) || 0;
    var philhealthContribution = parseFloat(document.getElementById('philhealth_contribution').value) || 0;
    var pagibigContribution = parseFloat(document.getElementById('pagibig_contribution').value) || 0;
    var withholdingTax = parseFloat(document.getElementById('withholding_tax').value) || 0;
    var otherDeductions = parseFloat(document.getElementById('other_deductions').value) || 0;
    var totalDeductions = sssContribution + philhealthContribution + pagibigContribution + withholdingTax + otherDeductions;

    var netPay = grossPay - totalDeductions;

    document.getElementById('net_pay').value = netPay.toFixed(2);
}

document.getElementById('calculate_pay').addEventListener('click', function() {
    calculateGrossPay();
    calculateNetPay();
});
</script>

