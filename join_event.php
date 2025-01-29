<?php
session_start();
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "You need to be logged in to join an event.";
    exit();
}

$Username = $_SESSION['username']; // Get the logged-in user's username
$PostID = $_POST['post_id']; // Get the PostID from the form submission

// Fetch the number of participants for the event
function getParticipantsCount($postID, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS participants_count FROM participants WHERE PostID = ?");
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['participants_count'];
}

// Fetch the post details and check the event date
$stmt = $conn->prepare("SELECT Participants_Limit, EventDate FROM posts WHERE PostID = ?");
$stmt->bind_param("i", $PostID);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

// Check if the event exists
if (!$post) {
    echo "Event not found.";
    exit();
}

$participantsCount = getParticipantsCount($PostID, $conn); // Get the number of participants
$participantsLimit = $post['Participants_Limit'];
// $eventDate = $post['EventDate']; // Get the event date

// // Check if the event date has passed
// if (strtotime($eventDate) < time()) {
//     echo "<script>alert('Sorry, the event has already passed.'); window.history.back();</script>";
//     exit();
// }

// Check if the event is full or if the user has already joined
if ($participantsLimit && $participantsCount >= $participantsLimit) {
    echo "<script>alert('Sorry, the event is full.'); window.location.href='feed.php';</script>";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM participants WHERE PostID = ? AND Username = ?");
$stmt->bind_param("is", $PostID, $Username);
$stmt->execute();
$joinedResult = $stmt->get_result();

// If the user has already joined
if ($joinedResult->num_rows > 0) {
    echo "<script>alert('You have already joined this event.'); window.history.back();</script>";
    exit();
}

// Insert the user into the participants table
$stmt = $conn->prepare("INSERT INTO participants (PostID, Username) VALUES (?, ?)");
$stmt->bind_param("is", $PostID, $Username);

if ($stmt->execute()) {
    echo "<script>alert('You have successfully joined the event!'); window.history.back();</script>";
} else {
    echo "<script>alert('Error: Could not join the event.'); window.location.href='feed.php';</script>";
}

$stmt->close();
$conn->close();
?>
