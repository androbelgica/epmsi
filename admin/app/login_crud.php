<?php
include '../../assets/constant/config.php';
session_start();

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['submit'])) {
        $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
        $password = $_POST['password']; // Raw password

        // Retrieve the user's data from the database
        $stmt = $conn->prepare("SELECT * FROM `login` WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Compare the provided password with the one stored in the database
            if ($password === $user['password']) {
                // Passwords match, proceed with login
                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_image'] = $user['image'];                
                header("location:../dashboard.php");
                exit();
            } else {
                // Passwords don't match, handle invalid login
                echo '<script>
                    alert("Wrong Password or Email");
                    window.location.href = "../../index.php";
                </script>';
                exit();
            }
        } else {
            // User not found, handle invalid login
            echo '<script>
                alert("User not found");
                window.location.href = "../../index.php";
            </script>';
            exit();
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
