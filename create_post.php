<?php
session_start();
include('connection.php');

// Debugging session (you can remove these comments in production)
if (!isset($_SESSION['username'])) {
    echo "You need to be logged in to create a post.";
    exit();
}

$Username = $_SESSION['username']; // Fetch the username from the active session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = $_POST['content'];
    $category = $_POST['category']; // Get the selected category
    $eventDate = isset($_POST['event_date']) && !empty($_POST['event_date']) ? $_POST['event_date'] : null; // Check if event date is provided, otherwise null

    // Handling image upload
    $targetDir = "uploads/";
    $imagePath = null; // Default in case no image is uploaded

    if (!empty($_FILES['image']['name'])) {
        $targetFile = $targetDir . basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check file type and size (optional security)
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $validExtensions) && $_FILES['image']['size'] <= 10000000) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            } else {
                echo "Error uploading the image.";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file type or size too large.'); window.location.href='create_post.html';</script>";
            exit();
        }
    }

    // Get the participant limit if the checkbox is ticked
    $participants_limit = isset($_POST['limit_participants']) ? $_POST['participants_limit'] : null;

    // Insert post into the database, including the category, participants limit, and event date
    $stmt = $conn->prepare("INSERT INTO posts (Username, Content, ImagePath, Category, Participants_Limit, EventDate) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $Username, $content, $imagePath, $category, $participants_limit, $eventDate); // Bind all the fields
    
    if ($stmt->execute()) {
        // Post created successfully
        echo "<script>alert('Post created successfully!'); window.location.href='feed.php';</script>";
    } else {
        // Error occurred
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
