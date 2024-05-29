<?php
session_start();
include '../assets/constant/config.php';

// Check if the payroll ID is set in the URL
if (!isset($_GET['payroll_id']) || !is_numeric($_GET['payroll_id'])) {
    $_SESSION['error'] = "Invalid payroll ID.";
    header("location:manage_payrolls.php");
    exit();
}

// Get the payroll ID from the URL
$payroll_id = $_GET['payroll_id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch payroll information based on payroll_id
    $query = "
        SELECT *
        FROM payrolls
        WHERE payroll_id = :payroll_id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':payroll_id', $payroll_id, PDO::PARAM_INT);
    $stmt->execute();
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payroll) {
        $_SESSION['error'] = "Payroll not found.";
        header("location:manage_payrolls.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:../manage_payrolls.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h4 {
            background-color: #f2f2f2;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .section table {
            width: 100%;
            border-collapse: collapse;
        }
        .section table, .section th, .section td {
            border: 1px solid #ddd;
        }
        .section th, .section td {
            padding: 8px;
            text-align: left;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        @media print {
            .container {
                border: none;
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .footer {
                page-break-after: always;
            }

            @media print {
            .payslip {
                page-break-after: always;
            }
        }
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="payslip">
        <div class="head">
            <h2>Company Name</h2>
            <p>Address, City, Zip Code</p>
            <p>Contact: (123) 456-7890 | Email: info@company.com</p>
        </div>

        <div class="section">
            <h4>Employee Information</h4>
            <table>
                <tr>
                    <th>Name</th>
                    <td><?php echo htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']); ?></td>
                </tr>
                <tr>
                    <th>Employee ID</th>
                    <td><?php echo htmlspecialchars($contract_id); ?></td>
                </tr>
                <tr>
                    <th>Pay Period</th>
                    <td><?php echo htmlspecialchars($_POST['period_start']); ?> to <?php echo htmlspecialchars($_POST['period_end']); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h4>Earnings</h4>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Basic Salary</td>
                    <td><?php echo number_format($_POST['basic_salary'], 2); ?></td>
                </tr>
                <tr>
                    <td>Overtime Pay</td>
                    <td><?php echo number_format($_POST['overtime_pay'], 2); ?></td>
                </tr>
                <tr>
                    <td>Holiday Pay</td>
                    <td><?php echo number_format($_POST['regular_holiday_pay'] + $_POST['special_non_working_holiday_pay'], 2); ?></td>
                </tr>
                <tr>
                    <td>Allowances</td>
                    <td><?php echo number_format($_POST['allowances'], 2); ?></td>
                </tr>
                <tr>
                    <td>Bonuses</td>
                    <td><?php echo number_format($_POST['bonuses'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h4>Deductions</h4>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>SSS Contribution</td>
                    <td><?php echo number_format($_POST['sss_contribution'], 2); ?></td>
                </tr>
                <tr>
                    <td>PhilHealth Contribution</td>
                    <td><?php echo number_format($_POST['philhealth_contribution'], 2); ?></td>
                </tr>
                <tr>
                    <td>Pag-IBIG Contribution</td>
                    <td><?php echo number_format($_POST['pagibig_contribution'], 2); ?></td>
                </tr>
                <tr>
                    <td>Withholding Tax</td>
                    <td><?php echo number_format($_POST['withholding_tax'], 2); ?></td>
                </tr>
                <tr>
                    <td>Other Deductions</td>
                    <td><?php echo number_format($_POST['other_deductions'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h4>Pay Summary</h4>
            <table>
                <tr>
                    <th>Gross Pay</th>
                    <td><?php echo number_format($_POST['gross_pay'], 2); ?></td>
                </tr>
                <tr>
                    <th>Net Pay</th>
                    <td><?php echo number_format($_POST['net_pay'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Generated on <?php echo date('Y-m-d'); ?></p>
            <p>Thank you for your hard work and dedication.</p>
        </div>
    </div>
    <div class="payslip">
        <div class="head">
            <h2>Company Name</h2>
            <p>Address, City, Zip Code</p>
            <p>Contact: (123) 456-7890 | Email: info@company.com</p>
        </div>

        <div class="section">
            <h4>Employee Information</h4>
            <table>
                <tr>
                    <th>Name</th>
                    <td><?php echo htmlspecialchars($contract['first_name'] . ' ' . $contract['last_name']); ?></td>
                </tr>
                <tr>
                    <th>Employee ID</th>
                    <td><?php echo htmlspecialchars($contract_id); ?></td>
                </tr>
                <tr>
                    <th>Pay Period</th>
                    <td><?php echo htmlspecialchars($_POST['period_start']); ?> to <?php echo htmlspecialchars($_POST['period_end']); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h4>Earnings</h4>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Basic Salary</td>
                    <td><?php echo number_format($_POST['basic_salary'], 2); ?></td>
                </tr>
                <tr>
                    <td>Overtime Pay</td>
                    <td><?php echo number_format($_POST['overtime_pay'], 2); ?></td>
                </tr>
                <tr>
                    <td>Holiday Pay</td>
                    <td><?php echo number_format($_POST['regular_holiday_pay'] + $_POST['special_non_working_holiday_pay'], 2); ?></td>
                </tr>
                <tr>
                    <td>Allowances</td>
                    <td><?php echo number_format($_POST['allowances'], 2); ?></td>
                </tr>
                <tr>
                    <td>Bonuses</td>
                    <td><?php echo number_format($_POST['bonuses'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h4>Deductions</h4>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>SSS Contribution</td>
                    <td><?php echo number_format($_POST['sss_contribution'], 2); ?></td>
                </tr>
                <tr>
                    <td>PhilHealth Contribution</td>
                    <td><?php echo number_format($_POST['philhealth_contribution'], 2); ?></td>
                </tr>
                <tr>
                    <td>Pag-IBIG Contribution</td>
                    <td><?php echo number_format($_POST['pagibig_contribution'], 2); ?></td>
                </tr>
                <tr>
                    <td>Withholding Tax</td>
                    <td><?php echo number_format($_POST['withholding_tax'], 2); ?></td>
                </tr>
                <tr>
                    <td>Other Deductions</td>
                    <td><?php echo number_format($_POST['other_deductions'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h4>Pay Summary</h4>
            <table>
                <tr>
                    <th>Gross Pay</th>
                    <td><?php echo number_format($_POST['gross_pay'], 2); ?></td>
                </tr>
                <tr>
                    <th>Net Pay</th>
                    <td><?php echo number_format($_POST['net_pay'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Generated on <?php echo date('Y-m-d'); ?></p>
            <p>Thank you for your hard work and dedication.</p>
        </div>
      </div>
    </div>
</body>
</html>
