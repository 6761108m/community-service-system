<?php
include("connection.php"); // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecting form data
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $roleincommunity = $_POST['roleincommunity'];
    $password = $_POST['password']; 
    $confirmpassword = $_POST['confirmpassword'];
    $phonenumber = $_POST['phonenumber'];

    // Validate required fields
    if (empty($username) || empty($firstname) || empty($lastname) || empty($gender) || empty($address) || 
        empty($email) || empty($roleincommunity) || empty($password) || empty($confirmpassword) || empty($phonenumber)) {
        echo "<script>
                alert('All fields are required.');
                window.history.back();
              </script>";
        exit();
    }

    // Password confirmation check
    if ($password !== $confirmpassword) {
        echo "<script>
                alert('Passwords do not match.');
                window.history.back();
              </script>";
        exit();
    }

    // Password strength validation
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{6,}$/', $password)) {
        echo "<script>
                alert('Password must be at least 6 characters long, including uppercase, lowercase, a number, and a special character.');
                window.history.back();
              </script>";
        exit();
    }

    // Check for duplicate username
    $checkUsernameQuery = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($checkUsernameQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script>
                alert('Username already exists.');
                window.history.back();
              </script>";
        exit();
    }

    // Check for duplicate phone number
    $checkPhoneQuery = "SELECT * FROM Users WHERE PhoneNumber = ?";
    $stmt = $conn->prepare($checkPhoneQuery);
    $stmt->bind_param("s", $phonenumber);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script>
                alert('Phone number already exists.');
                window.history.back();
              </script>";
        exit();
    }

    // Check for duplicate email
    $checkEmailQuery = "SELECT * FROM Users WHERE Email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script>
                alert('Email already exists.');
                window.history.back();
              </script>";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data into the Users table
    $sql = "INSERT INTO Users (Username, FirstName, LastName, Gender, Address, Email, RoleInCommunity, Password, PhoneNumber, Status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $username, $firstname, $lastname, $gender, $address, $email, $roleincommunity, $hashed_password, $phonenumber);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('We will inform the approval via email and it will take 1-3 business days to receive an email. If you don\'t receive any email after ONE WEEK, your approval has been REJECTED. Thank you.');
            window.location.href = 'login.html';
            </script>";
        exit();
    } else {
        echo "<script>
            alert('Error: " . addslashes($stmt->error) . "');
            window.history.back();
            </script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
