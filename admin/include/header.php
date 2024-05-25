<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>Dashboard</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/icons.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <?php 
    session_start();

    if (!isset($_SESSION['id'])) {
        header("Location: ../../index.php");
        exit();
    }

    $username = $_SESSION['username'];
    $userImage = $_SESSION['user_image'];
    ?>

    <div class="topbar">
        <nav class="navbar-custom">
            <ul class="list-inline menu-left float-left mb-0">
                <li class="float-left">
                    <button class="button-menu-mobile open-left waves-light waves-effect">
                        <i class="mdi mdi-menu"></i>
                    </button>
                </li>
                <li class="list-inline-item mt-4">
                    <b id="time" class="ml-lg-5 pl-lg-5 d-none d-md-block"><?php echo date("h:i:s A"); ?></b>
                </li>
            </ul>

            <ul class="list-inline float-right mb-0">
                <li class="list-inline-item dropdown notification-list">
                    <!-- User profile dropdown -->
                    <a class="nav-link dropdown-toggle waves-light waves-effect" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="../assets/images/<?php echo htmlspecialchars($userImage); ?>" alt="user" class="rounded-circle" width="40"> 
                        <span class="ml-1"><?php echo htmlspecialchars($username); ?> <i class="mdi mdi-chevron-down"></i> </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-dropdown">
                        <!-- item-->
                        <a class="dropdown-item" href="profile.php"><i class="mdi mdi-account-circle"></i> Profile</a>
                        <div class="dropdown-divider"></div>
                        <!-- item-->
                        <a class="dropdown-item" href="changepass.php"><i class="mdi-key-change"></i> Change Password</a>
                        <div class="dropdown-divider"></div>
                        <!-- item-->
                        <a class="dropdown-item" href="logout.php"><i class="mdi mdi-logout"></i> Logout</a>
                    </div>
                </li>
            </ul>

            <div class="clearfix"></div>
        </nav>
    </div>

    <script>
        // Function to update time
        function updateTime() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            var timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
            document.getElementById('time').innerHTML = timeString;
        }

        // Update time every second
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
