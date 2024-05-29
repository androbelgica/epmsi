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

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT 
    contracts.*, 
    sss.*, 
    ec.*, 
    phic.*, 
    hdmf.*,
    other_deductions.*,
    CONCAT(candidates.last_name,', ',candidates.first_name,' ',candidates.middle_name) AS fullname,
    jobs.title, 
    clients.client_id,
    clients.company_name,
    client_salary_constants.*

FROM 
    contracts
LEFT JOIN 
    candidates ON candidates.candidate_id = contracts.candidate_id
LEFT JOIN 
    sss ON contracts.contract_id = sss.contract_id
LEFT JOIN 
    ec ON contracts.contract_id = ec.contract_id
LEFT JOIN 
    phic ON contracts.contract_id = phic.contract_id
LEFT JOIN 
    hdmf ON contracts.contract_id = hdmf.contract_id
LEFT JOIN 
    other_deductions ON contracts.contract_id = other_deductions.contract_id
LEFT JOIN 
    jobs ON jobs.job_id = contracts.job_id
LEFT JOIN 
    clients ON clients.client_id = jobs.client_id
LEFT JOIN 
    client_salary_constants ON client_salary_constants.client_id = clients.client_id

WHERE 
    contracts.contract_id = :contract_id
    ");
    $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

   

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
                    <label for="fullname">Name:</label>
                    <input type="text" id="fullname" name="fullname" class="form-control" value="<?php echo htmlspecialchars($result['fullname']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="company_name">Company:</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?php echo htmlspecialchars($result['company_name']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="deisgnation">Designation:</label>
                    <input type="text" id="deisgnation" name="deisgnation" class="form-control" value="<?php echo htmlspecialchars($result['deisgnation']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="period_start">Cut-off Date:</label>
                    <input type="date" id="period_start" name="period_start" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Total Days</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="total_days">Total Work Days:</label>
                    <input type="number" id="total_days" name="total_days" class="form-control" required onchange="calculatePayroll()">
                </div>
                <div class="col-md-6">
                    <label for="total_hours">Total Work Hours:</label>
                    <input type="number" id="total_hours" name="total_hours" class="form-control" readonly>
                </div>
                </div>
        </div>

        <div class="form-group">
            <h4>Basic Salary</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="daily_rate">Daily Rate:</label>
                    <input type="number" step="0.01" id="daily_rate" name="daily_rate" class="form-control exclude-default" value="<?php echo htmlspecialchars($result['daily_rate']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="basic_salary">Basic Salary:</label>
                    <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-control" readonly>
                </div>
                <div class="col-md-6">
                    <label for="b_ot">Basic Overtime(hours):</label>
                    <input type="number" step="0.01" id="b_ot" name="b_ot" class="form-control" >
                </div>
                <div class="form-group">
                        <label for="b_ot_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="b_ot_amt" name="b_ot_amt" value=""readonly>
                    </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Secondary Salaries</h4>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="nsd">Night Shift Differential(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="nsd" name="nsd" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="rdd">Rest Day(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="rdd" name="rdd" value="">
                    </div>
                   
                    <div class="form-group">
                        <label for="rdnsd">Rest Day Night Shift Differential(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="rdnsd" name="rdnsd" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="sh">Special Holiday(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="sh" name="sh" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="shnsd">Special Holiday Night Shift Differential(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="shnsd" name="shnsd" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="lh">Legal Holiday(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="lh" name="lh" value="">
                    </div>
                   
                    <div class="form-group">
                        <label for="lhnsd">Legal Holiday Night Shift Differential(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="lhnsd" name="lhnsd" value="">
                    </div>
                   
                    <div class="form-group">
                        <label for="shrd">Special Holiday Rest Day(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="shrd" name="shrd" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="13th">13th Month Pay</label>
                        <input type="number" step="0.01" class="form-control" id="13th" name="13th" value="">
                    </div>
                    <div class="form-group">
                        <label for="sil">Service Incentive Leave</label>
                        <input type="number" step="0.01" class="form-control" id="sil" name="sil" value="">
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="nsd_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="nsd_amt" name="nsd_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="rdd_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="rdd_amt" name="rdd_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="rdnsd_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="rdnsd_amt" name="rdnsd_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="sh_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="sh_amt" name="sh_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="shnsd_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="shnsd_amt" name="shnsd_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="lh_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="lh_amt" name="lh_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="lhnsd_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="lhnsd_amt" name="lhnsd_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="shrd_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="shrd_amt" name="shrd_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="13th_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="13th_amt" name="13th_amt" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="sil_amt">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="sil_amt" name="sil_amt" value=""readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <h4>Overtime</h4>
                 <div class="row">
                 <div class="col-lg-6">
                     <div class="form-group">
                        <label for="nsd_ot">Night Shift Differential Overtime(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="nsd_ot" name="nsd_ot" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="rdd_ot">Rest Day Overtime(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="rdd_ot" name="rdd_ot" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="rdnsd_ot">Rest Day Night Shift Differential Overtime(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="rdnsd_ot" name="rdnsd_ot" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="sh_ot">Special Holiday Overtime(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="sh_ot" name="sh_ot" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="shnsd_ot">Special Holiday Night Shift Differential Overtime(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="shnsd_ot" name="shnsd_ot" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="lh_ot">Legal Holiday Overtime(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="lh_ot" name="lh_ot" value="">
                    </div>
                    
                    <div class="form-group">
                        <label for="lhnsd_ot">Legal Holiday Night Shift Differential Overtime(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="lhnsd_ot" name="lhnsd_ot" value="">
                    </div>

                    
                    <div class="form-group">
                        <label for="shrd_ot">Special Holiday Rest Day Overtime(Total hours)</label>
                        <input type="number" step="0.01" class="form-control" id="shrd_ot" name="shrd_ot" value="">
                    </div>
                 </div>
                 <div class="col-lg-4">
                    <div class="form-group">
                        <label for="nsd_amt_ot">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="nsd_amt_ot" name="nsd_amt_ot" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="rdd_amt_ot">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="rdd_amt_ot" name="rdd_amt_ot" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="rdnsd_amt_ot">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="rdnsd_amt_ot" name="rdnsd_amt_ot" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="sh_amt_ot">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="sh_amt_ot" name="sh_amt_ot" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="shnsd_amt_ot">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="shnsd_amt_ot" name="shnsd_amt_ot" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="lh_amt_ot">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="lh_amt_ot" name="lh_amt_ot" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="lhnsd_amt_ot">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="lhnsd_amt_ot" name="lhnsd_amt_ot" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="shrd_amt_ot">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="shrd_amt_ot" name="shrd_amt_ot" value=""readonly>
                    </div>

                   </div>    
                    
                                  
            </div>
        </div>
        <div class="form-group">
            <h4>Other Salaries</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="allowances">Allowances:</label>
                    <input type="number" step="0.01" id="allowances" name="allowances" class="form-control" value="">
                </div>
                <div class="col-md-6">
                    <label for="other_salaries">Other Salaries:</label>
                    <input type="number" step="0.01" id="other_salaries" name="other_salaries" class="form-control" value="">
                </div>
                                
            </div>
        </div>

        <div class="form-group">
            <h4>Deductions</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="sss_deduction">SSS Deduction:</label>
                    <input type="number" step="0.01" id="sss_deduction" name="sss_deduction" class="form-control exclude-default" value="<?php echo htmlspecialchars($result['ee_share']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="ec_deduction">EC Deduction:</label>
                    <input type="number" step="0.01" id="ec_deduction" name="ec_deduction" class="form-control exclude-default" value="<?php echo htmlspecialchars($result['ee_share']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="phic_deduction">PHIC Deduction:</label>
                    <input type="number" step="0.01" id="phic_deduction" name="phic_deduction" class="form-control exclude-default" value="<?php echo htmlspecialchars($result['ee_share']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="hdmf_deduction">HDMF Deduction:</label>
                    <input type="number" step="0.01" id="hdmf_deduction" name="hdmf_deduction" class="form-control exclude-default" value="<?php echo htmlspecialchars($result['ee_share']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="life_insurance">Life Insurance:</label>
                    <input type="number" step="0.01" id="life_insurance" name="life_insurance" class="form-control exclude-default" value="<?php echo htmlspecialchars($result['lifeinsurance']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="uniform_ppe">Uniform/PPE:</label>
                    <input type="number" step="0.01" id="uniform_ppe" name="uniform_ppe" class="form-control exclude-default" value="<?php echo htmlspecialchars($result['uniforms_ppe']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="late_undertime">Late / Undertime:</label>
                    <input type="number" step="0.01" id="late_undertime" name="late_undertime" class="form-control exclude-default" required>
                </div>
            </div>
        </div>
        <div class="form-group">
            <h4>Other Deductions</h4>
            <div class="row">
                <div class="col-md-6">
                    <label for="sss_loan">SSS Loan:</label>
                    <input type="number" step="0.01" id="sss_loan" name="sss_loan" class="form-control" value="">
                </div>
                <div class="col-md-6">
                    <label for="hdmf_loan">HDMF Loan:</label>
                    <input type="number" step="0.01" id="hdmf_loan" name="hdmf_loan" class="form-control" value="">
                </div>
                <div class="col-md-6">
                    <label for="other_loan">Other Loan:</label>
                    <input type="number" step="0.01" id="other_loan" name="other_loan" class="form-control" value="">
                </div>
                <div class="col-md-6">
                    <label for="other_deductions">Other Deductions:</label>
                    <input type="number" step="0.01" id="other_deductions" name="other_deductions" class="form-control" value="">
                </div>
                
            </div>
        </div>

        <button type="button" id="calculateButton" class="btn btn-success">Calculate</button>

        <div class="form-group">
            <h4>Summary</h4>
            <div class="row">
                    <div class="form-group">
                        <label for="gross_pay">Gross Pay</label>
                        <input type="number" step="0.01" class="form-control" id="gross_pay" name="gross_pay" value=""readonly>
                    </div>
                     <div class="form-group">
                        <label for="tot_deductions">Total Deductions</label>
                        <input type="number" step="0.01" class="form-control" id="tot_deductions" name="tot_deductions" value=""readonly>
                    </div>
                    <div class="form-group">
                        <label for="net_pay">Net Pay</label>
                        <input type="number" step="0.01" class="form-control" id="net_pay" name="net_pay" value=""readonly>
                    </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="manage_employees.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('include/footer.php'); ?>

<?php
// Assuming you have the $client_id from your $result
$client_id = $result['client_id'];

$sql = "SELECT * FROM client_salary_constants WHERE client_id = :client_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':client_id', $client_id);
$stmt->execute();
$salary_constants = $stmt->fetch(PDO::FETCH_ASSOC);

// Pass the constants to JavaScript
echo "<script>
    var salaryConstants = " . json_encode($salary_constants) . ";
</script>";
?>

<script>
    // JavaScript function to set default values to 0, excluding certain fields
    function setDefaultValues() {
        const inputs = document.querySelectorAll('.form-control:not(.exclude-default)');
        inputs.forEach(input => {
            if (input.type === 'number') {
                input.value = 0;
            }
        });
    }

    // Call the function when the page loads
    window.onload = setDefaultValues;
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to the calculate button
    document.getElementById('calculateButton').addEventListener('click', function() {
        // Get the basic salary value
        var basicSalary = parseFloat(document.getElementById('basic_salary').value) || 0;
        
       // Get all input fields with label name "Amount" and add their values to the basic salary
       var nsd = parseFloat(document.getElementById('nsd_amt').value) || 0;
        var rdd = parseFloat(document.getElementById('rdd_amt').value) || 0;
        var rdnsd = parseFloat(document.getElementById('rdnsd_amt').value) || 0;
        var sh = parseFloat(document.getElementById('sh_amt').value) || 0;
        var shnsd = parseFloat(document.getElementById('shnsd_amt').value) || 0;
        var lh = parseFloat(document.getElementById('lh_amt').value) || 0;
        var lhnsd = parseFloat(document.getElementById('lhnsd_amt').value) || 0;
        var shrd = parseFloat(document.getElementById('shrd_amt').value) || 0;
        var thirteenth = parseFloat(document.getElementById('13th_amt').value) || 0;
        var sil = parseFloat(document.getElementById('sil_amt').value) || 0;
        
        // Get all input fields for OT amounts and add their values to the basic salary
        var nsdOT = parseFloat(document.getElementById('nsd_amt_ot').value) || 0;
        var rddOT = parseFloat(document.getElementById('rdd_amt_ot').value) || 0;
        var rdnsdOT = parseFloat(document.getElementById('rdnsd_amt_ot').value) || 0;
        var shOT = parseFloat(document.getElementById('sh_amt_ot').value) || 0;
        var shnsdOT = parseFloat(document.getElementById('shnsd_amt_ot').value) || 0;
        var lhOT = parseFloat(document.getElementById('lh_amt_ot').value) || 0;
        var lhnsdOT = parseFloat(document.getElementById('lhnsd_amt_ot').value) || 0;
        var shrdOT = parseFloat(document.getElementById('shrd_amt_ot').value) || 0;
        
        // Add all the amounts to the basic salary to get the gross pay including OT amounts
        var grossPay = basicSalary + nsd + rdd + rdnsd + sh + shnsd + lh + lhnsd + shrd + thirteenth + sil + nsdOT + rddOT + rdnsdOT + shOT + shnsdOT + lhOT + lhnsdOT + shrdOT;
        
        // Update the gross pay input field with the calculated value
        document.getElementById('gross_pay').value = grossPay.toFixed(2);

        // Get total deductions
        var sssDeduction = parseFloat(document.getElementById('sss_deduction').value) || 0;
        var ecDeduction = parseFloat(document.getElementById('ec_deduction').value) || 0;
        var phicDeduction = parseFloat(document.getElementById('phic_deduction').value) || 0;
        var hdmfDeduction = parseFloat(document.getElementById('hdmf_deduction').value) || 0;
        var lifeInsurance = parseFloat(document.getElementById('life_insurance').value) || 0;
        var uniformPPE = parseFloat(document.getElementById('uniform_ppe').value) || 0;
        var lateUndertime = parseFloat(document.getElementById('late_undertime').value) || 0;

        // Calculate total deductions
        var totalDeductions = sssDeduction + ecDeduction + phicDeduction + hdmfDeduction + lifeInsurance + uniformPPE + lateUndertime;

        // Update the total deductions input field with the calculated value
        document.getElementById('tot_deductions').value = totalDeductions.toFixed(2);

        // Calculate net pay
        var netPay = grossPay - totalDeductions;

        // Update the net pay input field with the calculated value
        document.getElementById('net_pay').value = netPay.toFixed(2);
    });
});
</script>


<script>
        function calculatePayroll() {
            // Get input values
            let totalDays = parseInt(document.getElementById('total_days').value);
            let dailyRate = parseFloat(document.getElementById('daily_rate').value);
            
            // Calculate total work hours (totalDays * 8)
            let totalWorkHours = totalDays * 8;
            document.getElementById('total_hours').value = totalWorkHours;

            // Calculate basic salary (totalDays * dailyRate)
            let basicSalary = totalDays * dailyRate;
            document.getElementById('basic_salary').value = basicSalary.toFixed(2);

            
        }
    </script>
    

<!-- <script>
    document.getElementById('calculateButton').addEventListener('click', calculateSecondarySalaries);

        function calculateSecondarySalaries() {
            // Get the input values
            let nsd = parseFloat(document.getElementById('nsd').value) || 0;
            let rdd = parseFloat(document.getElementById('rdd').value) || 0;
            let rdnsd = parseFloat(document.getElementById('rdnsd').value) || 0;
            let sh = parseFloat(document.getElementById('sh').value) || 0;
            let shnsd = parseFloat(document.getElementById('shnsd').value) || 0;
            let lh = parseFloat(document.getElementById('lh').value) || 0;
            let lhnsd = parseFloat(document.getElementById('lhnsd').value) || 0;
            let shrd = parseFloat(document.getElementById('shrd').value) || 0;
            let thirteenth = parseFloat(document.getElementById('13th').value) || 0;
            let sil = parseFloat(document.getElementById('sil').value) || 0;
            let nsd_ot = parseFloat(document.getElementById('nsd_ot').value) || 0;
            let rdd_ot = parseFloat(document.getElementById('rdd_ot').value) || 0;
            let rdnsd_ot = parseFloat(document.getElementById('rdnsd_ot').value) || 0;
            let sh_ot = parseFloat(document.getElementById('sh_ot').value) || 0;
            let shnsd_ot = parseFloat(document.getElementById('shnsd_ot').value) || 0;
            let lh_ot = parseFloat(document.getElementById('lh_ot').value) || 0;
            let lhnsd_ot = parseFloat(document.getElementById('lhnsd_ot').value) || 0;
            let shrd_ot = parseFloat(document.getElementById('shrd_ot').value) || 0;
            
           // Get the constants from PHP
            let salaryConstants = window.salaryConstants;
            if (!salaryConstants || !salaryConstants['nsd'] || !salaryConstants['rdd'] || !salaryConstants['rdnsd'] || !salaryConstants['sh'] || !salaryConstants['shnsd'] || !salaryConstants['lh'] || !salaryConstants['lhnsd'] || !salaryConstants['shrd'] || !salaryConstants['thirteenth'] || !salaryConstants['sil'] || !salaryConstants['nsd_ot'] || !salaryConstants['rdd_ot'] || !salaryConstants['rdnsd_ot'] || !salaryConstants['sh_ot'] || !salaryConstants['shnsd_ot'] || !salaryConstants['lh_ot'] || !salaryConstants['lhnsd_ot'] || !salaryConstants['shrd_ot']) {
                alert("Salary constants are not defined properly");
                return;
    }

            // Perform the calculations
            let nsdAmt = nsd * parseFloat(salaryConstants['nsd']);
            let rddAmt = rdd * parseFloat(salaryConstants['rdd']);
            let rdnsdAmt = rdnsd * parseFloat(salaryConstants['rdnsd']);
            let shAmt = sh * parseFloat(salaryConstants['sh']);
            let shnsdAmt = shnsd * parseFloat(salaryConstants['shnsd']);
            let lhAmt = lh * parseFloat(salaryConstants['lh']);
            let lhnsdAmt = lhnsd * parseFloat(salaryConstants['lhnsd']);
            let shrdAmt = shrd * parseFloat(salaryConstants['shrd']);
            let thirteenthAmt = thirteenth * parseFloat(salaryConstants['thirteenth']);
            let silAmt = sil * parseFloat(salaryConstants['sil']);
            let nsd_amt_ot = nsd_ot * parseFloat(overtimeConstants['nsd_ot']);
            let rdd_amt_ot = rdd_ot * parseFloat(overtimeConstants['rdd_ot']);
            let rdnsd_amt_ot = rdnsd_ot * parseFloat(overtimeConstants['rdnsd_ot']);
            let sh_amt_ot = sh_ot * parseFloat(overtimeConstants['sh_ot']);
            let shnsd_amt_ot = shnsd_ot * parseFloat(overtimeConstants['shnsd_ot']);
            let lh_amt_ot = lh_ot * parseFloat(overtimeConstants['lh_ot']);
            let lhnsd_amt_ot = lhnsd_ot * parseFloat(overtimeConstants['lhnsd_ot']);
            let shrd_amt_ot = shrd_ot * parseFloat(overtimeConstants['shrd_ot']);

            // Display the results in the corresponding fields
            document.getElementById('nsd_amt').value = nsdAmt.toFixed(2);
            document.getElementById('rdd_amt').value = rddAmt.toFixed(2);
            document.getElementById('rdnsd_amt').value = rdnsdAmt.toFixed(2);
            document.getElementById('sh_amt').value = shAmt.toFixed(2);
            document.getElementById('shnsd_amt').value = shnsdAmt.toFixed(2);
            document.getElementById('lh_amt').value = lhAmt.toFixed(2);
            document.getElementById('lhnsd_amt').value = lhnsdAmt.toFixed(2);
            document.getElementById('shrd_amt').value = shrdAmt.toFixed(2);
            document.getElementById('13th_amt').value = thirteenthAmt.toFixed(2);
            document.getElementById('sil_amt').value = silAmt.toFixed(2);
            document.getElementById('nsd_amt_ot').value = nsd_amt_ot.toFixed(2);
            document.getElementById('rdd_amt_ot').value = rdd_amt_ot.toFixed(2);
            document.getElementById('rdnsd_amt_ot').value = rdnsd_amt_ot.toFixed(2);
            document.getElementById('sh_amt_ot').value = sh_amt_ot.toFixed(2);
            document.getElementById('shnsd_amt_ot').value = shnsd_amt_ot.toFixed(2);
            document.getElementById('lh_amt_ot').value = lh_amt_ot.toFixed(2);
            document.getElementById('lhnsd_amt_ot').value = lhnsd_amt_ot.toFixed(2);
            document.getElementById('shrd_amt_ot').value = shrd_amt_ot.toFixed(2);
        }

</script> -->

<script>
    document.getElementById('calculateButton').addEventListener('click', calculateSecondarySalaries);

    function calculateSecondarySalaries() {
        // Get the input values
        let b_ot = parseFloat(document.getElementById('b_ot').value) || 0;
        let nsd = parseFloat(document.getElementById('nsd').value) || 0;
        let rdd = parseFloat(document.getElementById('rdd').value) || 0;
        let rdnsd = parseFloat(document.getElementById('rdnsd').value) || 0;
        let sh = parseFloat(document.getElementById('sh').value) || 0;
        let shnsd = parseFloat(document.getElementById('shnsd').value) || 0;
        let lh = parseFloat(document.getElementById('lh').value) || 0;
        let lhnsd = parseFloat(document.getElementById('lhnsd').value) || 0;
        let shrd = parseFloat(document.getElementById('shrd').value) || 0;
        let thirteenth = parseFloat(document.getElementById('13th').value) || 0;
        let sil = parseFloat(document.getElementById('sil').value) || 0;
        let nsd_ot = parseFloat(document.getElementById('nsd_ot').value) || 0;
        let rdd_ot = parseFloat(document.getElementById('rdd_ot').value) || 0;
        let rdnsd_ot = parseFloat(document.getElementById('rdnsd_ot').value) || 0;
        let sh_ot = parseFloat(document.getElementById('sh_ot').value) || 0;
        let shnsd_ot = parseFloat(document.getElementById('shnsd_ot').value) || 0;
        let lh_ot = parseFloat(document.getElementById('lh_ot').value) || 0;
        let lhnsd_ot = parseFloat(document.getElementById('lhnsd_ot').value) || 0;
        let shrd_ot = parseFloat(document.getElementById('shrd_ot').value) || 0;

        // Get the constants from PHP
        let salaryConstants = window.salaryConstants;

        // Perform the calculations
        let b_otAmt = nsd !== 0 ? b_ot * parseFloat(salaryConstants['b_ot']) : 0;
        let nsdAmt = nsd !== 0 ? nsd * parseFloat(salaryConstants['nsd']) : 0;
        let rddAmt = rdd !== 0 ? rdd * parseFloat(salaryConstants['rdd']) : 0;
        let rdnsdAmt = rdnsd !== 0 ? rdnsd * parseFloat(salaryConstants['rdnsd']) : 0;
        let shAmt = sh !== 0 ? sh * parseFloat(salaryConstants['sh']) : 0;
        let shnsdAmt = shnsd !== 0 ? shnsd * parseFloat(salaryConstants['shnsd']) : 0;
        let lhAmt = lh !== 0 ? lh * parseFloat(salaryConstants['lh']) : 0;
        let lhnsdAmt = lhnsd !== 0 ? lhnsd * parseFloat(salaryConstants['lhnsd']) : 0;
        let shrdAmt = shrd !== 0 ? shrd * parseFloat(salaryConstants['shrd']) : 0;
        let thirteenthAmt = thirteenth !== 0 ? thirteenth * parseFloat(salaryConstants['13th']) : 0;
        let silAmt = sil !== 0 ? sil * parseFloat(salaryConstants['sil']) : 0;
        let nsd_otAmt = nsd_ot !== 0 ? nsd_ot * parseFloat(salaryConstants['nsd_ot']) : 0;
        let rdd_otAmt = rdd_ot !== 0 ? rdd_ot * parseFloat(salaryConstants['rdd_ot']) : 0;
        let rdnsd_otAmt = rdnsd_ot !== 0 ? rdnsd_ot * parseFloat(salaryConstants['rdnsd_ot']) : 0;
        let sh_otAmt = sh_ot !== 0 ? sh_ot * parseFloat(salaryConstants['sh_ot']) : 0;
        let shnsd_otAmt = shnsd_ot !== 0 ? shnsd_ot * parseFloat(salaryConstants['shnsd_ot']) : 0;
        let lh_otAmt = lh_ot !== 0 ? lh_ot * parseFloat(salaryConstants['lh_ot']) : 0;
        let lhnsd_otAmt = lhnsd_ot !== 0 ? lhnsd_ot * parseFloat(salaryConstants['lhnsd_ot']) : 0;
        let shrd_otAmt = shrd_ot !== 0 ? shrd_ot * parseFloat(salaryConstants['shrd_ot']) : 0;

        // Display the results in the corresponding fields
        document.getElementById('b_ot_amt').value = b_otAmt.toFixed(2);
        document.getElementById('nsd_amt').value = nsdAmt.toFixed(2);
        document.getElementById('rdd_amt').value = rddAmt.toFixed(2);
        document.getElementById('rdnsd_amt').value = rdnsdAmt.toFixed(2);
        document.getElementById('sh_amt').value = shAmt.toFixed(2);
        document.getElementById('shnsd_amt').value = shnsdAmt.toFixed(2);
        document.getElementById('lh_amt').value = lhAmt.toFixed(2);
        document.getElementById('lhnsd_amt').value = lhnsdAmt.toFixed(2);
        document.getElementById('shrd_amt').value = shrdAmt.toFixed(2);
        document.getElementById('13th_amt').value = thirteenthAmt.toFixed(2);
        document.getElementById('sil_amt').value = silAmt.toFixed(2);
        document.getElementById('nsd_amt_ot').value = nsd_otAmt.toFixed(2);
        document.getElementById('rdd_amt_ot').value = rdd_otAmt.toFixed(2);
        document.getElementById('rdnsd_amt_ot').value = rdnsd_otAmt.toFixed(2);
        document.getElementById('sh_amt_ot').value = sh_otAmt.toFixed(2);
        document.getElementById('shnsd_amt_ot').value = shnsd_otAmt.toFixed(2);
        document.getElementById('lh_amt_ot').value = lh_otAmt.toFixed(2);
        document.getElementById('lhnsd_amt_ot').value = lhnsd_otAmt.toFixed(2);
        document.getElementById('shrd_amt_ot').value = shrd_otAmt.toFixed(2);

   
        // Set other fields similarly
    }
</script>
<!-- <script>
    document.getElementById('calculateButton').addEventListener('click', calculateSecondarySalaries);

    function calculateSecondarySalaries() {
        // Get the input values
        let nsd = parseFloat(document.getElementById('nsd').value) || 0;
        let rdd = parseFloat(document.getElementById('rdd').value) || 0;
        let rdnsd = parseFloat(document.getElementById('rdnsd').value) || 0;
            let sh = parseFloat(document.getElementById('sh').value) || 0;
            let shnsd = parseFloat(document.getElementById('shnsd').value) || 0;
            let lh = parseFloat(document.getElementById('lh').value) || 0;
            let lhnsd = parseFloat(document.getElementById('lhnsd').value) || 0;
            let shrd = parseFloat(document.getElementById('shrd').value) || 0;
            let thirteenth = parseFloat(document.getElementById('13th').value) || 0;
            let sil = parseFloat(document.getElementById('sil').value) || 0;
            let nsd_ot = parseFloat(document.getElementById('nsd_ot').value) || 0;
            let rdd_ot = parseFloat(document.getElementById('rdd_ot').value) || 0;
            let rdnsd_ot = parseFloat(document.getElementById('rdnsd_ot').value) || 0;
            let sh_ot = parseFloat(document.getElementById('sh_ot').value) || 0;
            let shnsd_ot = parseFloat(document.getElementById('shnsd_ot').value) || 0;
            let lh_ot = parseFloat(document.getElementById('lh_ot').value) || 0;
            let lhnsd_ot = parseFloat(document.getElementById('lhnsd_ot').value) || 0;
            let shrd_ot = parseFloat(document.getElementById('shrd_ot').value) || 0;
        // Add other fields similarly

        // Get the constants from PHP
        let salaryConstants = window.salaryConstants;
    if (!salaryConstants || !salaryConstants['nsd'] || !salaryConstants['rdd'] || !salaryConstants['rdnsd'] || !salaryConstants['sh'] || !salaryConstants['shnsd'] || !salaryConstants['lh'] || !salaryConstants['lhnsd'] || !salaryConstants['shrd'] || !salaryConstants['thirteenth'] || !salaryConstants['sil'] || !salaryConstants['nsd_ot'] || !salaryConstants['rdd_ot'] || !salaryConstants['rdnsd_ot'] || !salaryConstants['sh_ot'] || !salaryConstants['shnsd_ot'] || !salaryConstants['lh_ot'] || !salaryConstants['lhnsd_ot'] || !salaryConstants['shrd_ot']) {
        alert("Salary constants are not defined properly");
        return;
    }

        // Perform the calculations
        let nsdAmt = nsd !== 0 ? nsd * parseFloat(salaryConstants['nsd']) : 0;
    let rddAmt = rdd !== 0 ? rdd * parseFloat(salaryConstants['rdd']) : 0;
    let rdnsdAmt = rdnsd * parseFloat(salaryConstants['rdnsd']);
    let shAmt = sh * parseFloat(salaryConstants['sh']);
    let shnsdAmt = shnsd * parseFloat(salaryConstants['shnsd']);
    let lhAmt = lh * parseFloat(salaryConstants['lh']);
    let lhnsdAmt = lhnsd * parseFloat(salaryConstants['lhnsd']);
    let shrdAmt = shrd * parseFloat(salaryConstants['shrd']);
    let thirteenthAmt = thirteenth * parseFloat(salaryConstants['thirteenth']);
    let silAmt = sil * parseFloat(salaryConstants['sil']);
    let nsd_amt_ot = nsd_ot * parseFloat(salaryConstants['nsd_ot']);
    let rdd_amt_ot = rdd_ot * parseFloat(salaryConstants['rdd_ot']);
    let rdnsd_amt_ot = rdnsd_ot * parseFloat(salaryConstants['rdnsd_ot']);
    let sh_amt_ot = sh_ot * parseFloat(salaryConstants['sh_ot']);
    let shnsd_amt_ot = shnsd_ot * parseFloat(salaryConstants['shnsd_ot']);
    let lh_amt_ot = lh_ot * parseFloat(salaryConstants['lh_ot']);
    let lhnsd_amt_ot = lhnsd_ot * parseFloat(salaryConstants['lhnsd_ot']);
    let shrd_amt_ot = shrd_ot * parseFloat(salaryConstants['shrd_ot']);


        // Add other calculations similarly

        // Display the results in the corresponding fields
        document.getElementById('nsd_amt').value = nsdAmt.toFixed(2);
        document.getElementById('rdd_amt').value = rddAmt.toFixed(2);
        document.getElementById('rdnsd_amt').value = rdnsdAmt.toFixed(2);
            document.getElementById('sh_amt').value = shAmt.toFixed(2);
            document.getElementById('shnsd_amt').value = shnsdAmt.toFixed(2);
            document.getElementById('lh_amt').value = lhAmt.toFixed(2);
            document.getElementById('lhnsd_amt').value = lhnsdAmt.toFixed(2);
            document.getElementById('shrd_amt').value = shrdAmt.toFixed(2);
            document.getElementById('13th_amt').value = thirteenthAmt.toFixed(2);
            document.getElementById('sil_amt').value = silAmt.toFixed(2);
            document.getElementById('nsd_amt_ot').value = nsd_amt_ot.toFixed(2);
            document.getElementById('rdd_amt_ot').value = rdd_amt_ot.toFixed(2);
            document.getElementById('rdnsd_amt_ot').value = rdnsd_amt_ot.toFixed(2);
            document.getElementById('sh_amt_ot').value = sh_amt_ot.toFixed(2);
            document.getElementById('shnsd_amt_ot').value = shnsd_amt_ot.toFixed(2);
            document.getElementById('lh_amt_ot').value = lh_amt_ot.toFixed(2);
            document.getElementById('lhnsd_amt_ot').value = lhnsd_amt_ot.toFixed(2);
            document.getElementById('shrd_amt_ot').value = shrd_amt_ot.toFixed(2);
   
        // Set other fields similarly
    }
</script>




 -->
