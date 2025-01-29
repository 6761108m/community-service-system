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
    <a href="supreme_home.php" class="logo active"><h1>Community Service System</h1></a>
    <nav>
        <ul>
            <li><a href="supreme_access.php">SUPREME ACCESS</a></li>
            <li><a href="supreme_profile.php">My Profile</a></li>
            <li><a href="supreme_join_events.php">Join Event</a></li>
            <li><a href="supreme_feed.php">Feed</a></li>
            <li><a href="supreme_create_post.html">Create Post</a></li> 
            <!-- <li><a href="contact.php">Contact Us</a></li> -->
            <li class='logout'><a href="logout.php">Log out</a> </li>
        </ul>
    </nav>
</header>
<br>
 <h1>Welcome, Supreme Member <?= htmlspecialchars($userName); ?>!</h1>
        <p>Welcome to your Supreme Committee hub for events, posts, and collaboration, designed to unite the key members and leaders of Taman Bukit Katil. Whether you're looking to organize exciting local events, share your latest updates, or collaborate with fellow committee members, this platform is your go-to place for fostering connections, sharing insights, and staying updated on all activities and initiatives within the Supreme Committee. Embrace the spirit of leadership as we work together to create a vibrant and impactful environment for the betterment of Taman Bukit Katil.</p>
    
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
            <a href="supreme_feed.php" class="btnA">Explore More Posts</a>
        </div>

        <!-- Call to Action Section -->
        <div class="section">
            <h2>🚀 Get Involved!</h2>
            <p>This is where you have gained supreme access to the heart of the community. As a member of the Supreme Committee, you're empowered to take part in shaping the direction of events, initiatives, and collaborations that drive progress in Taman Bukit Katil. Whether it's planning new projects, contributing to important discussions, or leading the way in community engagement, your involvement makes a difference. Join us and be a part of the change you want to see!"</p>
            <ul>
                <li><a href="supreme_access.php" class="btnA">SUPREME ACCESS</a></li><br>
                <li><a href="supreme_create_post.html" class="btnA">Create a New Post</a></li><br>
                <li><a href="supreme_join_events.php" class="btnA">Join an Event</a></li>
                
            </ul>
        </div>

        <!-- Other Functions Section -->
        <div class="section">
            <h2>📌 Other Features</h2>
            <p>Explore more ways to stay engaged with the community and deepen your connections in Taman Bukit Katil! From participating in local events and discussions to sharing valuable posts and ideas, this platform offers a variety of opportunities to interact, learn, and grow with fellow residents. Join us as we foster a vibrant and active community, where collaboration and mutual support are at the heart of everything we do. Stay involved, stay informed, and make a difference in your community today!</p>
            <ul>
                <li><a href="supreme_profile.php" class="btnA">View Your Profile</a></li><br>
                <!-- <li><a href="supreme_contact.php" class="btnA">Contact Us</a></li> -->
            </ul>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 67611 Community Service System</p>
    </footer>
</body>
</html>
