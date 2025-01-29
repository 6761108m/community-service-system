<?php
session_start();
include('connection.php');

// Fetch posts that have a participant limit (i.e., events where users can join)
$query = "SELECT p.PostID, p.Username, p.Content, p.ImagePath, p.CreatedAt, p.Category, p.Participants_Limit, p.LastEdited, p.EventDate, u.RoleInCommunity 
          FROM posts p 
          JOIN users u ON p.Username = u.Username
          WHERE p.Participants_Limit > 0
          AND p.EventDate >= CURDATE()  -- Check if the event date is not passed
          AND (SELECT COUNT(*) FROM participants WHERE PostID = p.PostID) < p.Participants_Limit  -- Ensure participants are not full
          ORDER BY p.CreatedAt DESC";


$result = $conn->query($query);

// Fetch number of participants for each post
function getParticipantsCount($postID, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS participants_count FROM participants WHERE PostID = ?");
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    return $data['participants_count'];
}

// Fetch usernames of all participants for a specific post
function getParticipantsList($postID, $conn) {
    $stmt = $conn->prepare("SELECT Username FROM participants WHERE PostID = ?");
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $result = $stmt->get_result();
    $participants = [];
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row['Username'];
    }
    return $participants;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Events</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .post { border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; background-color: #f0f0f0 ; border-radius: 20px}
        img { max-width:25%; height: auto; cursor: pointer; }
        .category { font-weight: bold; color: #4CAF50 ; }
        .join-button:disabled { background-color: #ddd; cursor: not-allowed; }
        .participants-list { font-size: 14px; margin-top: 10px; }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            background-color: rgba(0, 0, 0, 0.7); /* Black with transparency */
            text-align: center;
        }

        .modal-content {
            max-width: 100%;
            max-height: 100%;
            margin: auto;
        }

        #caption {
            color: #ccc;
            font-size: 18px;
            margin: 20px;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body id="background_color">
<header>
    <a href="supreme_home.php" class="logo"><h1>Community Service System</h1></a>
    <nav>
         <ul>
            <li><a href="supreme_access.php">SUPREME ACCESS</a></li> 
            <li><a href="supreme_profile.php">My Profile</a></li>
            <li class="active"><a href="supreme_join_events.php">Join Event</a></li>
            <li><a href="supreme_feed.php">Feed</a></li>
            <li><a href="supreme_create_post.html">Create Post</a></li> 
            <li class='logout'><a href="logout.php">Log out</a> </li>
         </ul>
    </nav>
    <div class="logout">
        <a href=#></a> 
    </div>
</header>
<br><br><br>
<h1>Join Events</h1>

<br>

<!-- Modal Structure -->
<div id="imageModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="modalImg">
    <div id="caption"></div>
</div>

<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $participantsCount = getParticipantsCount($row['PostID'], $conn); 
        $participantsList = getParticipantsList($row['PostID'], $conn); 
        $isJoined = false;

        // Check if the logged-in user has already joined
        if (isset($_SESSION['username'])) {
            $stmt = $conn->prepare("SELECT * FROM participants WHERE PostID = ? AND Username = ?");
            $stmt->bind_param("is", $row['PostID'], $_SESSION['username']);
            $stmt->execute();
            $joinedResult = $stmt->get_result();
            if ($joinedResult->num_rows > 0) {
                $isJoined = true; 
            }
        }

        // Check if the join button should be disabled for posts with a limit
        $isDisabled = (
            $row['Participants_Limit'] > 0 && 
            $participantsCount >= $row['Participants_Limit'] || 
            $isJoined || 
            strtotime($row['EventDate']) < time() && strtotime($row['EventDate']) !== strtotime(date('Y-m-d'))  // Disable if event date is in the past but allow today
        ) ? 'disabled' : '';
        
        // Check if the user posting is a Supreme Committee member
        $postedBy = htmlspecialchars($row['Username']);
        if ($row['RoleInCommunity'] == 'Supreme Committee') {
            $postedBy .= " ⭐";  // Add a star next to Supreme Committee posts
        }
       ?>
        
        <!-- POST CARD -->
        <div class="post">
            <p><strong>Posted by: <?= $postedBy; ?></strong></p>
            <p><span class="category">Category: <?= htmlspecialchars(str_replace('_', ' ', $row['Category'])); ?></span></p>
            <p><?= nl2br(htmlspecialchars($row['Content'])); ?></p>
            <?php if (!empty($row['ImagePath'])) { ?>
                <!-- Image with Lightbox Trigger -->
                <img src="<?= htmlspecialchars($row['ImagePath']); ?>" alt="" class="clickable-image">
            <?php } ?>
            <!-- Display the Event Date -->
            <?php if (!empty($row['EventDate'])) { ?>
                <p><strong>Event Date: </strong><?= htmlspecialchars($row['EventDate']); ?></p>
            <?php } ?>
            
            <p><small>Posted on: <?= $row['CreatedAt']; ?></small></p>
            <p><small>Last Edited on: <?= $row['LastEdited']; ?></small></p>

            <!-- Display participants info if a limit exists -->
            <?php if ($row['Participants_Limit'] > 0) { ?>
                <p>Participants: <?= $participantsCount . '/' . $row['Participants_Limit']; ?></p>
            <?php } ?>

            <!-- Join/Unjoin Button for Non-Authors -->
            <?php if ($_SESSION['username'] != $row['Username'] && $row['Participants_Limit'] > 0) { ?>
                <?php if (!$isJoined) { ?>
                    <form action="supreme_join_event.php" method="POST" style="display: inline;">
                        <input type="hidden" name="post_id" value="<?= $row['PostID']; ?>">
                        <button type="submit" class="join-button" <?= $isDisabled ?>>Join</button>
                    </form>
                <?php } else { ?>
                    <form action="supreme_unjoin_event.php" method="POST" style="display: inline;">
                        <input type="hidden" name="post_id" value="<?= $row['PostID']; ?>">
                        <button type="submit" class="join-button">Unjoin</button>
                    </form>
                <?php } ?>
            <?php } ?>

            <!-- List of Participants -->
            <?php if ($row['Participants_Limit'] > 0) { ?>
                <div class="participants-list">
                    <p><strong>Participants:</strong></p>
                    <ul>
                        <?php foreach ($participantsList as $participant) { ?>
                            <li><?= htmlspecialchars($participant); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
        </div>
        <?php
    }
} else {
    echo "<p>No joinable events available.</p>";
}
$conn->close();
?>


<!-- JavaScript for Modal -->
<script>
    // Get modal elements
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("modalImg");
    var caption = document.getElementById("caption");
    var closeBtn = document.getElementsByClassName("close")[0];

    // Open the modal when an image is clicked
    document.querySelectorAll('.clickable-image').forEach(function(img) {
        img.onclick = function() {
            modal.style.display = "block";
            modalImg.src = this.src;  // Set the clicked image source in the modal
            caption.innerHTML = this.alt; // Optional: Display image alt text as caption
        };
    });

    // Close the modal when the user clicks the close button
    closeBtn.onclick = function() {
        modal.style.display = "none";
    };

    // Close the modal when the user clicks outside the image
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
</script>

</body>
</html>
