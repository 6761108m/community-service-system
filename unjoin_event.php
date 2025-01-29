<?php
session_start();
include('connection.php');

// Check if the user is logged in and has a post ID
if (!isset($_SESSION['username']) || !isset($_POST['post_id'])) {
    echo "You must be logged in and specify a post ID to unjoin.";
    exit();
}

$Username = $_SESSION['username'];
$postID = $_POST['post_id'];

// Fetch the event date for the specified post ID
$stmt = $conn->prepare("SELECT EventDate FROM posts WHERE PostID = ?");
$stmt->bind_param("i", $postID);
$stmt->execute();
$stmt->bind_result($eventDate);
$stmt->fetch();
$stmt->close();

// Check if the event date has passed
$currentDate = date('Y-m-d');
$isDisabled = (strtotime($eventDate) < strtotime($currentDate)) ? 'disabled' : '';

// If the event date has passed, do not allow unjoin action
if ($isDisabled === 'disabled') {
    echo "<script>alert('You cannot unjoin this event as the event date has already passed.'); window.location.href='feed.php';</script>";
    exit();
}

// Remove user from participants table
$stmt = $conn->prepare("DELETE FROM participants WHERE PostID = ? AND Username = ?");
$stmt->bind_param("is", $postID, $Username);

if ($stmt->execute()) {
    // Successfully unjoined
    echo "<script>alert('You have successfully unjoined the event!'); window.location.href='feed.php';</script>";
} else {
    // Error
    echo "<script>alert('Error unjoining the event.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
