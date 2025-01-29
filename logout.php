<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <script>
        // Display confirmation prompt before logging out
        window.onload = function() {
            var confirmAction = window.confirm("Are you sure you want to log out?");
            if (confirmAction) {
                // If user confirms, redirect to the same page to run PHP logout
                window.location.href = "logout.php?confirm=true";
            } else {
                // If user cancels, redirect to the home page or wherever you want
              window.history.back(); // stays on the same page before logout.php
            }
        }
    </script>
</head>
<body>
    <!-- The PHP session will be destroyed if confirm=true in the URL -->
    <?php
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'true') {
        session_unset();
        session_destroy();
        header("Location: login.html");
        exit();
    }
    ?>
</body>
</html>
