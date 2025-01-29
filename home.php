<?php
session_start();
include("connection.php"); // Database connection file

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

// Fetch the logged-in user's name
$username = $_SESSION['username'];
$query = "SELECT FirstName FROM Users WHERE Username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user['FirstName'];
$stmt->close();

// Fetch latest community posts
$feedQuery = "SELECT PostID, Content, Category, CreatedAt FROM posts ORDER BY CreatedAt DESC LIMIT 3";
$feedResult = $conn->query($feedQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Page - Community Service System</title>
    <link rel="stylesheet" href="main.css">
    <style>
        /* body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            background: #f5f5f5;
            color: #333;
        }
        header {
            background: #FF7043;
            color: white;
            padding: 20px;
            text-align: center;
        } */
        /* h1 {
            margin: 0;
            font-size: 2.5rem;
        } */
        .container {
            padding: 30px;
            max-width: 1200px;
            margin: auto;
        }
        .section {
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .feed-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .feed-item {
            flex: 1;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            background: #fff;
            transition: transform 0.2s;
        }
        .feed-item:hover {
            transform: translateY(-5px);
        }

       footer {
    text-align: center;
    padding: 0.5px;
    background: #333;
    color: white;
    width: 100%; /* Ensures the footer spans the full width of the screen */
    position: fixed; /* Fixes the footer at the bottom of the screen */
    bottom: 0; /* Positions it at the bottom */
    left: 0; /* Ensures it starts from the left edge */
}
ul{
    list-style-type: none;
}

    </style>
</head>
<body id="background_color">
<header>
    <a href="home.php" class="logo active"><h1>Community Service System</h1></a>
    <nav>
        <ul>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="join_events.php">Join Event</a></li>
            <li><a href="feed.php">Feed</a></li>
            <li><a href="create_post.html">Create Post</a></li> 
            <li><a href="contact.php">Contact Us</a></li>
            <li class='logout'><a href="logout.php">Log out</a> </li>
        </ul>
    </nav>
</header>
<br>
 <h1>Welcome, <?= htmlspecialchars($userName); ?>!</h1>
        <p>Welcome to your community hub for events, posts, and collaboration, designed to bring together residents and enthusiasts of Taman Bukit Katil. Whether you're looking to join exciting local events, share your latest posts, or collaborate with others, this platform is your go-to place for fostering connections, sharing knowledge, and staying updated on all things happening in and around Taman Bukit Katil. Embrace the spirit of community as we work together to create a vibrant and engaging environment for everyone</p>
    
    <!-- Main Content -->
    <div class="container">

        <!-- Community Feed Section -->
        <div class="section">
            <h2>🌟 Community Feed Overview</h2>
            <div class="feed-container">
                <?php if ($feedResult->num_rows > 0): ?>
                    <?php while ($post = $feedResult->fetch_assoc()): ?>
                        <div class="feed-item">
                            <p><strong>Category:</strong> <?= htmlspecialchars(str_replace('_', ' ', $post['Category'])); ?></p>
                            <p><?= nl2br(htmlspecialchars($post['Content'])); ?></p>
                            <p><small>Posted on: <?= htmlspecialchars($post['CreatedAt']); ?></small></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No posts available yet. Be the first to contribute!</p>
                <?php endif; ?>
            </div>
            <br>
            <a href="feed.php" class="btnA">Explore More Posts</a>
        </div>

        <!-- Call to Action Section -->
        <div class="section">
            <h2>🚀 Get Involved!</h2>
            <p>As a valued community member, you have the opportunity to contribute to the growth and success of Taman Bukit Katil. Whether it's participating in events, supporting initiatives, or collaborating with fellow residents, your involvement helps build a stronger and more connected community. Join us and play a role in making a positive impact together!</p>
            <ul>
                <li><a href="create_post.html" class="btnA">Create a New Post</a></li><br>
                <li><a href="join_events.php" class="btnA">Join an Event</a></li><br>
                <!-- <li><a href="interact.php" class="btnA">Interact with the Community</a></li> -->
            </ul>
        </div>

        <!-- Other Functions Section -->
        <div class="section">
            <h2>📌 Other Features</h2>
            <p>Explore more ways to stay engaged with the community and deepen your connections in Taman Bukit Katil! From participating in local events and discussions to sharing valuable posts and ideas, this platform offers a variety of opportunities to interact, learn, and grow with fellow residents. Join us as we foster a vibrant and active community, where collaboration and mutual support are at the heart of everything we do. Stay involved, stay informed, and make a difference in your community today!</p>
            <ul>
                <li><a href="profile.php" class="btnA">View Your Profile</a></li><br>
                <li><a href="contact.php" class="btnA">Contact Us</a></li>
            </ul>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 67611 Community Service System</p>
    </footer>
</body>
</html>
