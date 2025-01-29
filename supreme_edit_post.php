<?php
session_start();
include('connection.php');

// Check if session is set properly
if (!isset($_SESSION['username'])) {
    echo "You must be logged in to edit a post.";
    exit;
}

// Validate the post_id input
if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    echo "Invalid request. POST ID: " . $_GET['post_id'];
    exit;
}

$post_id = intval($_GET['post_id']);

// Fetch the post with a JOIN to ensure the Username is correctly linked to the users table
$query = "
SELECT p.Content, p.Category, p.ImagePath, p.Participants_Limit, p.EventDate, u.Username 
FROM posts p
JOIN users u ON p.Username = u.Username
WHERE p.PostID = ? AND u.Username = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $post_id, $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

// Check if the post exists and if the user is authorized
if ($result->num_rows === 0) {
    echo "You are not authorized to edit this post.";
    exit;
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="create_post.css">
    <title>Edit Post</title>
</head>
<body id="background_color">
    <header>
    <a href="supreme_home.php" class="logo"><h1>Community Service System</h1></a>
    <nav>
         <ul>
            <li><a href="supreme_access.php">SUPREME ACCESS</a></li> 
            <li><a href="supreme_profile.php">My Profile</a></li>
            <li><a href="supreme_join_events.php">Join Event</a></li>
            <li><a href="supreme_feed.php">Feed</a></li>
            <li class="active"><a href="supreme_edit_post.php?post_id=<?= $post_id ?>">Edit Post</a></li> 
            <li class='logout'><a href="logout.php">Log out</a> </li>
         </ul>
    </nav>
    </header>
<br><br><br>
    <h2>Edit Your Post</h2>
    <form action="supreme_update_post.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?= $post_id; ?>">
        
        <textarea name="content" placeholder="Write your post here..."cols="80" rows="10" required><?= htmlspecialchars($row['Content']); ?></textarea><br>

        <!-- Dropdown for categories -->
        <label for="category">Choose a category:</label>
        <select name="category" id="category" required>
            <option value="Religious_Events" <?= ($row['Category'] == 'Religious_Events') ? 'selected' : ''; ?>>Religious Events</option>
            <option value="Health_&_Welness" <?= ($row['Category'] == 'Health_&_Welness') ? 'selected' : ''; ?>>Health & Wellness</option>
            <option value="Education_&_Literacy" <?= ($row['Category'] == 'Education_&_Literacy') ? 'selected' : ''; ?>>Education & Literacy</option>
            <option value="Environmental_Protection" <?= ($row['Category'] == 'Environmental_Protection') ? 'selected' : ''; ?>>Environmental Protection</option>
            <option value="Humanity" <?= ($row['Category'] == 'Humanity') ? 'selected' : ''; ?>>Social Welfare & Humanitarian Aid</option>
            <option value="Youth" <?= ($row['Category'] == 'Youth') ? 'selected' : ''; ?>>Youth & Children</option>
            <option value="Animal_Welfare" <?= ($row['Category'] == 'Animal_Welfare') ? 'selected' : ''; ?>>Animal Welfare</option>
            <option value="Elderly_Care" <?= ($row['Category'] == 'Elderly_Care') ? 'selected' : ''; ?>>Elderly Care & Support</option>
            <option value="Cultural_Events" <?= ($row['Category'] == 'Cultural_Events') ? 'selected' : ''; ?>>Cultural Events & Heritage</option>
            <option value="Disaster_Relief" <?= ($row['Category'] == 'Disaster_Relief') ? 'selected' : ''; ?>>Disaster Relief</option>
            <option value="Job_Assistance" <?= ($row['Category'] == 'Job_Assistance') ? 'selected' : ''; ?>>Job & Career Assistance</option>
            <option value="Advocacy_Legal" <?= ($row['Category'] == 'Advocacy_Legal') ? 'selected' : ''; ?>>Advocacy & Legal Support</option>
            <option value="Arts_Creativity" <?= ($row['Category'] == 'Arts_Creativity') ? 'selected' : ''; ?>>Arts & Creativity</option>
            <option value="Community_Development" <?= ($row['Category'] == 'Community_Development') ? 'selected' : ''; ?>>Community Development</option>
            <option value="Sports_Recreation" <?= ($row['Category'] == 'Sports_Recreation') ? 'selected' : ''; ?>>Sports & Recreation</option>
            <option value="Others" <?= ($row['Category'] == 'Others') ? 'selected' : ''; ?>>Others</option>
        </select><br><br>

        <!-- Participants limit checkbox -->
        <label for="limit_participants">Set a participant limit for this event:</label>
        <input type="checkbox" name="limit_participants" id="limit_participants" <?= $row['Participants_Limit'] ? 'checked' : ''; ?>><br>
        
        <!-- Only show the input field for participant limit if the checkbox is checked -->
        <div id="limit_input_div" style="display: <?= $row['Participants_Limit'] !== null ? 'block' : 'none'; ?>;">
            <label for="participants_limit">Participants Limit:</label>
            <input type="number" name="participants_limit" id="participants_limit" min="1" value="<?= htmlspecialchars($row['Participants_Limit']) ?>" placeholder="Enter participant limit"><br><br>
        </div>

        <!-- Event Date Calendar -->
        <label for="event_date">Event Date:</label>
        <input type="date" name="event_date" id="event_date" value="<?= $row['EventDate'] ? $row['EventDate'] : ''; ?>" /><br><br>

        <!-- Current image path -->
        <?php if (!empty($row['ImagePath'])) { ?>
            <p>Current Image: <img src="<?= htmlspecialchars($row['ImagePath']); ?>" alt="Post Image" style="max-width: 200px; height: auto;"></p>
            <input type="hidden" name="current_image" value="<?= htmlspecialchars($row['ImagePath']); ?>">
            <label for="delete_image">Delete Image:</label>
            <input type="checkbox" name="delete_image" id="delete_image"><br>
            <label for="image">Change Image:</label>
            <input type="file" name="image"><br><br>
        <?php } else { ?>
            <label for="image">Upload an Image:</label>
            <input type="file" name="image"><br><br>
        <?php } ?>
    
        <button type="submit">Update Post</button>
        <button type="button" class="logoutbtn" onclick="window.history.back()">Cancel</button>
    </form>

    <script>
        // Disable past dates using JavaScript
        document.getElementById('event_date').addEventListener('focus', function() {
            let today = new Date();
            let dd = String(today.getDate()).padStart(2, '0');
            let mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
            let yyyy = today.getFullYear();
            today = yyyy + '-' + mm + '-' + dd;
            document.getElementById('event_date').setAttribute('min', today);
        });
        
        // Toggle the visibility of the participants limit input field based on checkbox state
        document.getElementById('limit_participants').addEventListener('change', function() {
            document.getElementById('limit_input_div').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>
