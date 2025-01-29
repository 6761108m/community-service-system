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
$participants_limit = isset($_POST['participants_limit']) ? ($_POST['participants_limit'] ? intval($_POST['participants_limit']) : null) : null;
$event_date = isset($_POST['event_date']) ? $_POST['event_date'] : null; // Handle EventDate

// Check if the image needs to be deleted
$image_path = null;
if (isset($_POST['delete_image']) && $_POST['delete_image'] == 'on') {
    // If user checked "Delete Image", set image_path to null and delete the image file
    $image_path = null;
    if (isset($_POST['current_image']) && file_exists($_POST['current_image'])) {
        unlink($_POST['current_image']); // Delete the existing image from the server
    }
} elseif (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    // Handle file upload (new image)
    $image_path = 'uploads/' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
} elseif (isset($_POST['current_image']) && !empty($_POST['current_image'])) {
    // If no new image is uploaded, retain the current image
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
    echo "<script>alert('Post updated successfully.'); window.location.href='supreme_feed.php';</script>";
} else {
    echo "<script>alert('Failed to update post.'); window.location.href='supreme_feed.php';</script>";
}

$stmt->close();
$conn->close();
?>

