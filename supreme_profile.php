<?php
session_start();
include("connection.php"); // Database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Get the logged-in username from the session
$username = $_SESSION['username'];

// Initialize variables
$message = "";

// Fetch user details
$query = "SELECT Username, FirstName, LastName, Address, PhoneNumber, Email FROM Users WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user details exist
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<script>
            alert('User details not found.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

// Handle form submission to update user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $address = $_POST['address'];
    $phonenumber = $_POST['phonenumber'];
    $email = $_POST['email'];

    // Validate form inputs
    if (empty($firstname) || empty($lastname) || empty($address) || empty($phonenumber)|| empty($email)) {
        $message = "All fields are required.";
    } else {
        // Update query
        $updateQuery = "UPDATE Users SET FirstName = ?, LastName = ?, Address = ?, PhoneNumber = ?, Email = ? WHERE Username = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssssss", $firstname, $lastname, $address, $phonenumber, $email, $username);

        if ($updateStmt->execute()) {
            $message = "Profile updated successfully!";
            // Refresh user details
            $user['FirstName'] = $firstname;
            $user['LastName'] = $lastname;
            $user['Address'] = $address;
            $user['PhoneNumber'] = $phonenumber;
            $user['Email'] = $email;
        } else {
            $message = "Error updating profile: " . $conn->error;
        }

        $updateStmt->close();
    }
}
// Fetch joined events for the user
$joinedEventsQuery = "SELECT p.PostID, p.Username, p.Content, p.ImagePath, p.CreatedAt, p.Category, p.Username, p.LastEdited, p.EventDate
                      FROM participants pt 
                      JOIN posts p ON pt.PostID = p.PostID 
                      WHERE pt.Username = ? 
                      ORDER BY p.CreatedAt DESC";
$joinedEventsStmt = $conn->prepare($joinedEventsQuery);
$joinedEventsStmt->bind_param("s", $username);
$joinedEventsStmt->execute();
$joinedEventsResult = $joinedEventsStmt->get_result();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Community Service System</title>
    <link rel="stylesheet" href="main.css"> <!-- Optional CSS -->
    <script>
        function toggleEditMode() {
            const inputs = document.querySelectorAll('.editable');
            const editButton = document.getElementById('editButton');
            const saveButton = document.getElementById('saveButton');

            inputs.forEach(input => input.disabled = !input.disabled);
            editButton.style.display = editButton.style.display === 'none' ? 'block' : 'none';
            saveButton.style.display = saveButton.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body id="background_color">
<header>
    <a href="supreme_home.php" class="logo"><h1>Community Service System</h1></a> <!-- Update to index.php -->
    <nav>
    <nav>
        <ul>
            <li><a href="supreme_access.php">SUPREME ACCESS</a></li>
            <li class="active"><a href="supreme_profile.php">My Profile</a></li>
            <li><a href="supreme_join_events.php">Join Event</a></li>
            <li><a href="supreme_feed.php">Feed</a></li>
            <li><a href="supreme_create_post.html">Create Post</a></li> 
            <!-- <li><a href="contact.php">Contact Us</a></li> -->
            <li class='logout'><a href="logout.php">Log out</a> </li>
        </ul>
    </nav>
    </nav>
    <div class="logout">
        <a href=#></a> 
    </div>
</header>
<br><br><br>
<main>
    <h2>My Profile</h2>

    <!-- Display a success or error message -->
    <?php if (!empty($message)) { ?>
        <p class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST" action="supreme_profile.php">
    <div class="form-group">
            <!-- <label for="username">Username:</label> -->
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['Username']); ?></p>
            <input type="hidden" id="username" value="<?= htmlspecialchars($user['Username']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" class="editable" value="<?= htmlspecialchars($user['FirstName']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" class="editable" value="<?= htmlspecialchars($user['LastName']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" class="editable" value="<?= htmlspecialchars($user['Email']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" class="editable" value="<?= htmlspecialchars($user['Address']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="phonenumber">Phone Number:</label>
            <input type="text" id="phonenumber" name="phonenumber" class="editable" value="<?= htmlspecialchars($user['PhoneNumber']); ?>" disabled>
        </div>

        <!-- Buttons -->
        <div class="button-group">
            <button type="button" id="editButton" class="button" onclick="toggleEditMode()">Edit</button>
            <button type="submit" id="saveButton" name="update" class="button" style="display: none;">Save</button>
        </div>
    </form>
</main>
<div class="joined-events-container">
    <h3>Joined Events</h3>

    <!-- Display Joined Events -->
    <?php if ($joinedEventsResult->num_rows > 0) { ?>
        <?php while ($event = $joinedEventsResult->fetch_assoc()) { ?>
            <div class="event">
                <div class="event-header">
                    <div class="event-info">
                        <p><strong>Event:</strong> <?= htmlspecialchars($event['Content']); ?></p>
                        <p><span class="category">Category: <?= htmlspecialchars(str_replace('_', ' ', $event['Category'])); ?></span></p>
                    </div>
                    <div class="event-dates">
                        <p><small>Posted on: <?= $event['CreatedAt']; ?></small></p>
                        <p><small>Last Edited: <?= $event['LastEdited']; ?></small></p>
                        <p><small>Event Date: <?= $event['EventDate']; ?></small></p>
                    </div>
                </div>

                <?php if (!empty($event['ImagePath'])) { ?>
                    <div class="event-image-container">
                        <img src="<?= htmlspecialchars($event['ImagePath']); ?>" alt="Event Image" class="event-image">
                    </div>
                <?php } ?>

                <div class="event-footer">
                    <p><small>Posted by: <?= $event['Username']; ?></small></p>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p>You have not joined any events yet.</p>
    <?php } ?>
</div>


<style>
   .joined-events-container {
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    margin: 20px 0;
}

.joined-events-container h3 {
    text-align: center;
    color: #333;
    font-size: 1.8rem;
    margin-bottom: 20px;
}

.event {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    transition: background-color 0.3s;
}

.event:hover {
    background-color: #f0f0f0;
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.event-info {
    max-width: 70%;
}

.event-dates {
    font-size: 0.9rem;
    text-align: right;
    color: #555;
}

.category {
    font-weight: bold;
    color: #27ce80;
}

.event-image-container {
    margin-top: 15px;
    text-align: center;
}

.event-image {
    width: 300px;  /* Fixed width for standard size */
    height: auto;  /* Maintain aspect ratio */
    max-width: 100%;  /* Ensure the image doesn't overflow its container */
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}


.event-footer {
    margin-top: 15px;
    font-size: 0.9rem;
    text-align: right;
    color: #555;
}

.event-footer small {
    color: #888;
}

</style>


  


<style>
    main {
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        max-width: 800px;
        margin: 0 auto;
    }

    h2 {
        text-align: center;
        color: #333;
        font-size: 2rem;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: bold;
        color: #333;
        display: block;
        margin-bottom: 8px;
    }

    .form-group input {
        width: 100%;
        padding: 12px;
        border-radius: 6px;
        border: 1px solid #ddd;
        font-size: 16px;
        box-sizing: border-box;
    }

    .form-group input:disabled {
        background-color: #f4f4f4;
    }

    .editable:focus {
        outline: 2px solid #27ce80;
    }

    .button-group {
        text-align: center;
    }

    .button {
        padding: 12px 20px;
        font-size: 16px;
        background-color: #FF7043 ;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 10px;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: orangered;
    }

    .message {
        text-align: center;
        font-size: 1.1rem;
        margin: 20px 0;
    }

    .message.success {
        color: green;
    }

    .message.error {
        color: red;
    }
</style>

<script>
    function toggleEditMode() {
        const inputs = document.querySelectorAll('.editable');
        const editButton = document.getElementById('editButton');
        const saveButton = document.getElementById('saveButton');

        inputs.forEach(input => input.disabled = !input.disabled);
        editButton.style.display = editButton.style.display === 'none' ? 'block' : 'none';
        saveButton.style.display = saveButton.style.display === 'none' ? 'block' : 'none';
    }
</script>

</body>
</html>
