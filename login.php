<?php
session_start();

include('connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        showAlertAndRedirect("Please fill all required fields", 'login.html');
        exit();
    }

    // Check if username exists in users table
    $sql_users = "SELECT * FROM users WHERE Username = ?";
    $stmt_users = $conn->prepare($sql_users);
    $stmt_users->bind_param("s", $username);
    $stmt_users->execute();
    $result_users = $stmt_users->get_result();

    // Check if username exists in admin table
    $sql_admin = "SELECT * FROM admin WHERE AUsername = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("s", $username);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_users->num_rows > 0) {
        // Customer login
        $row = $result_users->fetch_assoc();
        $hashed_password = $row['Password']; 
        $name = $row['FirstName']; 
        $role = $row['RoleInCommunity']; 
        $status = $row['Status']; // Check user status

        // Prevent login if status is pending
        if ($status === 'Pending') {
            showAlertAndRedirect("Your account is still in pending approval.", 'login.html');
            exit();
        }

        // Verify password for customer
        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $row['Username']; 

            // Redirect based on role
            if ($role == 'Supreme Committee') {
                showAlertAndRedirect("Welcome Supreme Committee Member " . $name, 'supreme_home.php');
            } else {
                showAlertAndRedirect("Welcome " . $name, 'home.php');
            }
            exit();
        } else {
            showAlertAndRedirect("Invalid Password", 'login.html');
            exit();
        }

    } elseif ($result_admin->num_rows > 0) {
        // Admin login
        $row = $result_admin->fetch_assoc();
        $plain_text_password = $row['Password']; 
        $name = $row['Name']; 

        // Verify password for admin
        if ($password === $plain_text_password) {
            $_SESSION['username'] = $row['AUsername']; 
            showAlertAndRedirect("Welcome " . $name, 'admin/index.php');
            exit();
        } else {
            showAlertAndRedirect("Invalid Password", 'login.html');
            exit();
        }
    } else {
        showAlertAndRedirect("Invalid Username", 'login.html');
        exit();
    }

    // Close statements and connection
    $stmt_users->close();
    $stmt_admin->close();
    $conn->close();
}

// Function to show an alert message and redirect
function showAlertAndRedirect($message, $url = null) {
    echo "<script type='text/javascript'>
            alert('$message');";
    if ($url) {
        echo "window.location.href='$url';";
    }
    echo "</script>";
}
?>
