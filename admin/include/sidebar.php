<?php
include '../assets/constant/config.php';
include '../assets/constant/checklogin.php';
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$stmt1 = $conn->prepare("SELECT * FROM `manage_web` ");
$stmt1->execute();
$record1 = $stmt1->fetchAll();
foreach ($record1 as $key1) { 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title><?php echo $key1['title']; ?></title>
    <link rel="shortcut icon" href="../assets/images/<?php echo $key1['photos']; ?>">
    <link href="../assets/plugins/fullcalendar/vanillaCalendar.css" rel="stylesheet" type="text/css" />
    <link href="../assets/plugins/morris/morris.css" rel="stylesheet">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/icons.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/style.css" rel="stylesheet" type="text/css">
    <link href="../assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="../assets/plugins/colorpicker/asColorPicker.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
    <link href="../assets/css/select2.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">
    <link href="bootstrap-colorpicker/dist/css/bootstrap-colorpicker.css" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body class="fixed-left">

    <!-- Loader -->
    <!-- <div id="preloader">
        <div id="status">
            <div class="spinner"></div>
        </div>
    </div> -->

    <!-- Begin page -->
    <div id="wrapper">

        <!-- ========== Left Sidebar Start ========== -->
        <div class="left side-menu">
            <button type="button" class="button-menu-mobile button-menu-mobile-topbar open-left waves-effect">
                <i class="ion-close"></i>
            </button>

            <!-- LOGO -->
            <div class="topbar-left">
                <div class="text-center">
                    <a class="logo">
                        <img src="../assets/images/<?php echo $key1['photo1']; ?>" alt="" width="200px" class="logo-large">
                    </a>
                </div>
            </div>

            <div class="sidebar-inner niceScrollleft">
                <div id="sidebar-menu">
                    <ul>
                        <li>
                            <a href="dashboard.php" class="waves-effect">
                                <i class="fas fa-home"></i>
                                <span> Dashboard </span>
                            </a>
                        </li>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="fas fa-users"></i> <span> Clients </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="list-unstyled">
                                <li><a href="add_client.php">Add Client</a></li>
                                <li><a href="manage_client.php">Manage Clients</a></li>
                            </ul>
                        </li>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="fa fa-user"></i> <span> Employees </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="list-unstyled">
                                <!-- <li><a href="add_employee.php">Hire Employee</a></li> -->
                                <li><a href="manage_employees.php">Manage Employees</a></li>
                            </ul>
                        </li>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="fa fa-briefcase"></i> <span> Jobs </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="list-unstyled">
                                <li><a href="add_job.php">Add Job</a></li>
                                <li><a href="manage_job.php">Manage Jobs</a></li>
                            </ul>
                        </li>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="fa fa-briefcase"></i> <span> Applications </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="list-unstyled">
                                <li><a href="add_candidate.php">Add Applicant</a></li>
                                <li><a href="manage_candidate.php">Manage Applications</a></li>
                            </ul>
                        </li>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="fa fa-briefcase"></i> <span> Contracts </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="list-unstyled">
                                <li><a href="add_contracts.php">Add Contracts</a></li>
                                <li><a href="manage_contract.php">Manage Contracts</a></li>
                            </ul>
                        </li>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="fa fa-file-invoice-dollar"></i> <span> Payroll </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                                <ul class="list-unstyled">
                                <li><a href="create_payroll.php">Generate Payroll</a></li>
                                <li><a href="list_payroll.php">View Payroll</a></li>
                            </ul>
                        </li>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="ti-receipt"></i> <span> Invoice </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="list-unstyled">
                                <li><a href="employee_report.php">Create Invoice</a></li>
                                <li><a href="client_report.php">Manage Invoice</a></li>
                                </ul>
                        </li>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="fa fa-chart-bar"></i> <span> Reports </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="list-unstyled">
                                <li><a href="employee_report.php">Employee Report</a></li>
                                <li><a href="client_report.php">Client Report</a></li>
                                <li><a href="job_report.php">Job Report</a></li>
                            </ul>
                        </li>
                        <?php 
                            // Check if the user role is Admin
                            if ($_SESSION['role'] == 'Admin') {
                        ?>
                        <li class="has_sub">
                            <a href="javascript:void(0);" class="waves-effect">
                                <i class="fa fa-users"></i> <span> User Management </span> <span class="float-right"><i class="mdi mdi-chevron-right"></i></span></a>
                            <ul class="list-unstyled">
                                <li><a href="add_user.php">Add User</a></li>
                                <li><a href="manage_users.php">Manage Users</a></li>
                                <!-- Add more sub-menu items as needed -->
                            </ul>
                        </li>
                        <li>
                            <a href="web.php" class="waves-effect">
                                <i class="fa fa-cog"></i>
                                <span> Web Appearance </span>
                            </a>
                        </li>
                        <?php } ?>
                        <li>
                            <a href="logout.php" class="waves-effect">
                                <i class="fa fa-sign-out-alt"></i>
                                <span> Logout </span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="clearfix"></div>
            </div> <!-- end sidebarinner -->
        </div>
        <!-- Left Sidebar End -->

        <!-- Start right Content here -->
        <div class="content-page">
            <!-- Start content -->
            <div class="content">
<?php } ?>
