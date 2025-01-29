<?php
session_start();
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: login.html");
    exit();
}

// Fetch user details from the database based on the current session's username
$username = $_SESSION['username']; // Assuming the session stores the logged-in username

$sql = "SELECT FirstName, LastName, Email FROM users WHERE Username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the user's data
    $user = $result->fetch_assoc();
    $userName = $user['FirstName'] . " " . $user['LastName'];
    $userEmail = $user['Email'];
} else {
    // Handle case if user data is not found
    echo "User data not found.";
    exit();
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    // Include the logged-in username in the insert
    $username = $_SESSION['username']; // Ensure session contains the username

    // Insert message into the database
    $sql = "INSERT INTO contact_messages (Name, Email, Subject, Message, Username)
            VALUES ('$name', '$email', '$subject', '$message', '$username')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
            alert('Message submitted successfully. You will be notified once your response has been reviewed.');
            window.location.href = 'contact.php';
            </script>";
    } else {
        echo "Error: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="contact.css">
    <title>Contact Us</title>
</head>
<body id="background_color">
<header>
    <a href="home.php" class="logo"><h1>Community Service System</h1></a>
    <nav>
        <ul>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="join_events.php">Join Event</a></li>
            <li><a href="feed.php">Feed</a></li>
            <li><a href="create_post.html">Create Post</a></li> 
            <li class="active"><a href="contact.php">Contact Us</a></li>
            <li class='logout'><a href="logout.php">Log out</a> </li>
        </ul>
    </nav>
</header>
<br><br>
<h2>Contact Us</h2>

<p class="info-text">Use this form to send us your messages. If you have any community issues or suggestions, feel free to message the Supreme Committee or Supreme Committee members.</p>

<form id="form" action="contact.php" method="POST">
    <div class="formcontent">
        <div class="left">
            <div class="formitem">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
                <!-- Hidden input to send the name -->
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($userName); ?>">
            </div>
            <div class="formitem">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                <!-- Hidden input to send the email -->
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>">
            </div>
            <div class="formitem">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" placeholder="State your issue" required>
            </div>
            <div class="formitem">
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required placeholder="e.g., Community Issue or Suggestion"></textarea>
            </div>
            <button type="submit">Submit</button>
            <a href="feed.php"><button type="button" class="logoutbtn">Cancel</button></a>
        </div>
    </div>
</form>


</body>
</html>

