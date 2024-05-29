<?php
session_start();
include '../assets/constant/config.php';



if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid payroll ID.";
    header("location:list_payroll.php");
    exit();
}

$payroll_id = (int)$_GET['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    cl.company_name,
    j.title
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

    $stmt1 = $conn->prepare("SELECT * FROM `manage_web` ");
    $stmt1->execute();
    $record1 = $stmt1->fetchAll();
    foreach ($record1 as $key1) 

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':payroll_id', $payroll_id, PDO::PARAM_INT);
    $stmt->execute();
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        $_SESSION['error'] = "Payroll not found.";
        header("location:list_payroll.php");
        exit();
    }

    function convertNumberToWords($number)
    {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = [
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'forty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        ];

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            trigger_error(
                'convertNumberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . convertNumberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= convertNumberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    function convertCurrencyToWords($number)
{
    $number = number_format($number, 2, '.', '');
    $parts = explode('.', $number);
    $pesos = convertNumberToWords($parts[0]) . ' pesos';
    $centavos = isset($parts[1]) && $parts[1] != '00' ? ' and ' . convertNumberToWords($parts[1]) . ' centavos' : '';

    // Append "only" to the final output
    $output = ucfirst($pesos . $centavos);
    // if (!empty($centavos)) {
    //     $output .= ' only';
    // }
    $output .= ' only';
    return $output;
}

    $net_pay_in_words = convertCurrencyToWords($contract['net_pay']);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("location:../admin/list_payroll.php");
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
        .container {
            width: 100%;
            max-width: 8.5in;
            margin: auto;
            font-family: Arial, sans-serif;
            font-size: 10pt;
            padding: 10px;
        }
        .payslip {
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #000;
            padding: 10px;
            box-sizing: border-box;
        }
        .header, .footer {
            text-align: center;
        }
        .section {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 5px;
            border: 1px solid #000;
            text-align: left;
        }
        .net-pay {
            font-weight: bold;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .signatures div {
            width: 45%;
            text-align: center;
        }
        .logo img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- First Payslip -->
        <div class="payslip">
            <div class="header">
                <a class="logo">
                    <img src="../assets/images/<?php echo $key1['photo1']; ?>" alt="" class="logo-large">
                </a>
                <p>17A Sta. Lucia St., San Antonio Valley 1</p>
                <p>Paranaque City, Metro Manila, 1700</p>
                <p>Contact: (123) 456-7890 | Email: info@company.com</p>
            </div>

            <div class="section">
                <table>
                    <tr>
                        <th>Employee</th>
                        <td><?php echo htmlspecialchars($contract['full_name']); ?></td>
                        <th>Pay Period</th>
                        <td><?php echo htmlspecialchars($contract['period_start']); ?> to <?php echo htmlspecialchars($contract['period_end']); ?></td>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <td><?php echo htmlspecialchars($contract['company_name']); ?></td>
                        <th>Daily Rate</th>
                        <td><?php echo htmlspecialchars($contract['daily_rate']); ?></td>
                    </tr>
                    <tr>
                        <th>Designation</th>
                        <td><?php echo htmlspecialchars($contract['title']); ?></td>
                        <th>Worked Days</th>
                        <td><?php echo htmlspecialchars($contract['total_days']); ?></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <table>
                    <tr>
                        <th>Earnings</th>
                        <th>Amount</th>
                        <th>Deductions</th>
                        <th>Amount</th>
                    </tr>
                    <tr>
                        <td>Basic</td>
                        <td><?php echo htmlspecialchars($contract['basic_salary']); ?></td>
                        <td>SSS</td>
                        <td><?php echo htmlspecialchars($contract['sss_contribution']); ?></td>
                    </tr>
                    <tr>
                        <td>Holidays</td>
                        <td><?php echo htmlspecialchars($contract['special_non_working_holiday_pay'] + $contract['regular_holiday_pay']); ?></td>
                        <td>Philhealth</td>
                        <td><?php echo htmlspecialchars($contract['philhealth_contribution']); ?></td>
                    </tr>
                    <tr>
                        <td>Overtime</td>
                        <td><?php echo htmlspecialchars($contract['overtime_pay']); ?></td>
                        <td>Pagibig Fund</td>
                        <td><?php echo htmlspecialchars($contract['pagibig_contribution']); ?></td>
                    </tr>
                    <tr>
                        <td>Allowances</td>
                        <td><?php echo htmlspecialchars($contract['allowances']); ?></td>
                        <td>W. Tax</td>
                        <td><?php echo htmlspecialchars($contract['withholding_tax']); ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>Other Deductions</td>
                        <td><?php echo htmlspecialchars($contract['other_deductions']); ?></td>
                    </tr>
                    <tr>
                        <th>Gross Earnings</th>
                        <th><?php echo htmlspecialchars($contract['gross_pay']); ?></th>
                        <th>Total Deductions</th>
                        <th><?php echo htmlspecialchars($contract['sss_contribution'] + $contract['philhealth_contribution'] + $contract['pagibig_contribution'] + $contract['withholding_tax'] + $contract['other_deductions']); ?></th>
                    </tr>
                    <tr>
                        <th>Net Pay</th>
                        <th><?php echo htmlspecialchars($contract['net_pay']); ?></th>
                        <th colspan="2"><?php echo ucwords($net_pay_in_words); ?></th>
                    </tr>
                </table>
            </div>

            <div class="signatures">
                <div>
                    <p>Prepared By:</p>
                    <p>_______________________</p>
                </div>
                <div>
                    <p>Employee Signature</p>
                    <p>_______________________</p>
                </div>
            </div>

            <div class="footer">
                <p>***This is a system generated payslip***</p>
            </div>
        </div>
        
        <!-- Second Payslip -->
        <div class="payslip">
            <div class="header">
                <a class="logo">
                    <img src="../assets/images/<?php echo $key1['photo1']; ?>" alt="" class="logo-large">
                </a>
                <p>17A Sta. Lucia St., San Antonio Valley 1</p>
                <p>Paranaque City, Metro Manila, 1700</p>
                <p>Contact: (123) 456-7890 | Email: info@company.com</p>
            </div>

            <div class="section">
                <table>
                    <tr>
                        <th>Employee</th>
                        <td><?php echo htmlspecialchars($contract['full_name']); ?></td>
                        <th>Pay Period</th>
                        <td><?php echo htmlspecialchars($contract['period_start']); ?> to <?php echo htmlspecialchars($contract['period_end']); ?></td>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <td><?php echo htmlspecialchars($contract['company_name']); ?></td>
                        <th>Daily Rate</th>
                        <td><?php echo htmlspecialchars($contract['daily_rate']); ?></td>
                    </tr>
                    <tr>
                        <th>Designation</th>
                        <td><?php echo htmlspecialchars($contract['title']); ?></td>
                        <th>Worked Days</th>
                        <td><?php echo htmlspecialchars($contract['total_days']); ?></td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <table>
                    <tr>
                        <th>Earnings</th>
                        <th>Amount</th>
                        <th>Deductions</th>
                        <th>Amount</th>
                    </tr>
                    <tr>
                        <td>Basic</td>
                        <td><?php echo htmlspecialchars($contract['basic_salary']); ?></td>
                        <td>SSS</td>
                        <td><?php echo htmlspecialchars($contract['sss_contribution']); ?></td>
                    </tr>
                    <tr>
                        <td>Holidays</td>
                        <td><?php echo htmlspecialchars($contract['special_non_working_holiday_pay'] + $contract['regular_holiday_pay']); ?></td>
                        <td>Philhealth</td>
                        <td><?php echo htmlspecialchars($contract['philhealth_contribution']); ?></td>
                    </tr>
                    <tr>
                        <td>Overtime</td>
                        <td><?php echo htmlspecialchars($contract['overtime_pay']); ?></td>
                        <td>Pagibig Fund</td>
                        <td><?php echo htmlspecialchars($contract['pagibig_contribution']); ?></td>
                    </tr>
                    <tr>
                        <td>Allowances</td>
                        <td><?php echo htmlspecialchars($contract['allowances']); ?></td>
                        <td>W. Tax</td>
                        <td><?php echo htmlspecialchars($contract['withholding_tax']); ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>Other Deductions</td>
                        <td><?php echo htmlspecialchars($contract['other_deductions']); ?></td>
                    </tr>
                    <tr>
                        <th>Gross Earnings</th>
                        <th><?php echo htmlspecialchars($contract['gross_pay']); ?></th>
                        <th>Total Deductions</th>
                        <th><?php echo htmlspecialchars($contract['sss_contribution'] + $contract['philhealth_contribution'] + $contract['pagibig_contribution'] + $contract['withholding_tax'] + $contract['other_deductions']); ?></th>
                    </tr>
                    <tr>
                        <th>Net Pay</th>
                        <th><?php echo htmlspecialchars($contract['net_pay']); ?></th>
                        <th colspan="2"><?php echo ucwords($net_pay_in_words); ?></th>
                    </tr>
                </table>
            </div>

            <div class="signatures">
                <div>
                    <p>Prepared By:</p>
                    <p>_______________________</p>
                </div>
                <div>
                    <p>Employee Signature</p>
                    <p>_______________________</p>
                </div>
            </div>

            <div class="footer">
                <p>***This is a system generated payslip***</p>
            </div>
        </div>
    </div>
</body>
</html>
