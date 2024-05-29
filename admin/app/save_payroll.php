<?php
session_start();

include '../assets/constant/config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "CSRF token validation failed.";
        header("location:../create_payroll.php");
        exit();
    }

    // Get form data
    $contract_id = $_POST['contract_id'];
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
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        header("location:../admin/manage_employees.php");
        exit();
    } catch (PDOException $e) {
        // Redirect with error message
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("location:../create_payroll.php");
        exit();
    }
} else {
    // Redirect if form is not submitted via POST method
    header("location:../create_payroll.php");
    exit();
}
?>
