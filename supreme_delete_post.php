<?php
session_start();
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];

    // Delete only if the user owns the post
    $query = "DELETE FROM posts WHERE PostID = ? AND Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $post_id, $_SESSION['username']);
    
    if ($stmt->execute()) {
        echo "<script>alert('Post deleted successfully!'); window.location.href='supreme_feed.php';</script>";
    } else {
        echo "<script>alert('Error deleting post.'); window.history.back();</script>";
    }
}
?>
