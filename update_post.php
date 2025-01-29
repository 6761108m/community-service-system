<?php
session_start();
include('connection.php');

// Check if session is set properly
if (!isset($_SESSION['username'])) {
    echo "You must be logged in to update a post.";
    exit;
}

// Validate the post_id input
if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
    echo "Invalid request. POST ID: " . $_POST['post_id'];
    exit;
}

$post_id = intval($_POST['post_id']);
$content = $_POST['content'];
$category = $_POST['category'];

// Handle the Participants_Limit (Null if unchecked)
$participants_limit = isset($_POST['limit_participants']) ? ($_POST['limit_participants'] ? intval($_POST['participants_limit']) : null) : null;
$event_date = isset($_POST['event_date']) ? $_POST['event_date'] : null; // Handle EventDate

// Handle image logic
$image_path = null;
if (isset($_POST['delete_image']) && $_POST['delete_image'] == 'on') {
    // If the user wants to delete the image
    $image_path = null;
    if (isset($_POST['current_image']) && file_exists($_POST['current_image'])) {
        unlink($_POST['current_image']); // Delete the current image
    }
} elseif (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    // If a new image is uploaded
    $upload_dir = 'uploads/'; // Define your upload directory
    $image_name = basename($_FILES['image']['name']);
    $target_file = $upload_dir . $image_name;
    
    // Validate image file type and size (optional but recommended)
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($image_file_type, $allowed_types)) {
        echo "Invalid file type. Please upload a valid image.";
        exit;
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = $target_file; // Set the image path
    } else {
        echo "Error uploading the image.";
        exit;
    }
} elseif (isset($_POST['current_image']) && !empty($_POST['current_image'])) {
    // Retain the current image if no new image is uploaded
    $image_path = $_POST['current_image'];
}

// Prepare the SQL update query
$query = "UPDATE posts 
          SET Content = ?, Category = ?, Participants_Limit = ?, ImagePath = ?, EventDate = ?, LastEdited = NOW() 
          WHERE PostID = ? AND Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssissii", $content, $category, $participants_limit, $image_path, $event_date, $post_id, $_SESSION['username']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "<script>alert('Post updated successfully.'); window.location.href='feed.php';</script>";
} else {
    echo "<script>alert('Failed to update post.'); window.location.href='feed.php';</script>";
}

$stmt->close();
$conn->close();
?>
