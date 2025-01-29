<?php
include('connection.php');

// Query to count the number of members for each gender
$genderQuery = "SELECT Gender, COUNT(*) AS count FROM users WHERE Status = 'verified' GROUP BY Gender";
$genderResult = $conn->query($genderQuery);

$genderData = [];
while ($row = $genderResult->fetch_assoc()) {
    $genderData[$row['Gender']] = $row['count'];
}

// Query to count the number of members for each role in the community
$roleQuery = "SELECT RoleInCommunity, COUNT(*) AS count FROM users WHERE Status = 'verified' GROUP BY RoleInCommunity";
$roleResult = $conn->query($roleQuery);

$roleData = [];
while ($row = $roleResult->fetch_assoc()) {
    $roleData[$row['RoleInCommunity']] = $row['count'];
}

// Query to count the number of posts for each category
$categoryQuery = "SELECT Category, COUNT(*) AS count FROM posts GROUP BY Category";
$categoryResult = $conn->query($categoryQuery);

$categoryData = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categoryName = str_replace('_', ' ', $row['Category']); 
    $categoryData[$categoryName] = $row['count'];
}

$approvedQuery = "SELECT Username, FirstName, LastName, Gender, Address, Email, RoleInCommunity, PhoneNumber
                  FROM users WHERE Status = 'Verified'";
$approvedResult = $conn->query($approvedQuery);

// Fetch rejected members
$rejectedQuery = "SELECT Username, FirstName, LastName, Gender, Address, Email, RoleInCommunity, PhoneNumber, CreatedAt 
                  FROM users WHERE Status = 'Reject'";
$rejectedResult = $conn->query($rejectedQuery);

// Query to fetch pending messages from the contact table
$responseQuery = "SELECT * FROM contact_messages WHERE status = 'pending'";  // Replace with your actual table/column names
$responseResult = $conn->query($responseQuery);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="main.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <style>
        #genderChart, #roleChart, #categoryChart {
            width: 100%;
            max-width: 500px;
            height: auto;
            margin: 20px auto;
        }
    </style>
</head>
<body>
<header>
        <a href="supreme_home.php" class="logo"><h1>Community Service System</h1></a>
        <nav>
            <ul>
                <li class="active"><a href="supreme_access.php">SUPREME ACCESS</a></li>
                <li><a href="supreme_profile.php">My Profile</a></li>
                <li><a href="supreme_join_events.php">Join Event</a></li>
                
                <li><a href="supreme_feed.php">Feed</a></li>
                <li ><a href="supreme_create_post.html">Create Post</a></li> 
                <!-- <li><a href="contact.php">Contact Us</a></li> -->
                <li class='logout'><a href="logout.php">Log out</a> </li>
            </ul>
        </nav>
        <div class="logout">
            <a href=#></a> 
        </div>
    </header>
<!-- Save buttons -->
 <br>
 <!-- <button id="saveCSVBtn" 
        style="padding: 8px 16px; font-size: 14px; background-color:rgb(51, 184, 122); color: white; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s ease;">
    Save as CSV
</button> -->

<h2 class="center">Gender Distribution</h2>
<canvas id="genderChart"></canvas>

<h2 class="center">Role in Community</h2>
<canvas id="roleChart"></canvas>

<h2 class="center">Post Categories</h2>
<canvas id="categoryChart"></canvas>



<script>
    var genderData = <?php echo json_encode($genderData); ?>;
    var genderLabels = Object.keys(genderData);
    var genderCounts = Object.values(genderData);
    var ctxGender = document.getElementById('genderChart').getContext('2d');
    var genderChart = new Chart(ctxGender, {
        type: 'pie',
        data: {
            labels: genderLabels,
            datasets: [{
                data: genderCounts,
                backgroundColor: ['#324893', '#c00093', '#3357FF'],
                borderColor: '#fff',
                borderWidth: 1
            }]
        }
    });

    var roleData = <?php echo json_encode($roleData); ?>;
    var roleLabels = Object.keys(roleData);
    var roleCounts = Object.values(roleData);
    var ctxRole = document.getElementById('roleChart').getContext('2d');

    // Create gradient background for bars
    var gradient = ctxRole.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, '#b0c800'); // Light green
    gradient.addColorStop(1, '#4CAF50'); // Dark green

    var roleChart = new Chart(ctxRole, {
        type: 'bar',
        data: {
            labels: roleLabels,
            datasets: [{
                label: 'Members Count',
                data: roleCounts,
                backgroundColor: gradient,  // Apply gradient color
                borderColor: '#333',
                borderWidth: 1,
                borderRadius: 10,           // Rounded corners on bars
                barThickness: 40,           // Control bar thickness
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 2000,             // Smooth animation effect
                easing: 'easeOutBounce'     // Bounce effect for fun animation
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return `${tooltipItem.label}: ${tooltipItem.raw} members`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        color: '#333',
                        font: {
                            size: 14
                        }
                    },
                    grid: {
                        color: '#ddd'
                    }
                },
                x: {
                    ticks: {
                        color: '#333',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        }
    });

    var categoryData = <?php echo json_encode($categoryData); ?>;
    var categoryLabels = Object.keys(categoryData);
    var categoryCounts = Object.values(categoryData);
    var ctxCategory = document.getElementById('categoryChart').getContext('2d');

    // Create a gradient fill for a stylish effect
    var categoryGradient = ctxCategory.createLinearGradient(0, 0, 0, 400);
    categoryGradient.addColorStop(0, '#FF5733');  // Vibrant Orange
    categoryGradient.addColorStop(1, '#FFC300');  // Soft Yellow

    var categoryChart = new Chart(ctxCategory, {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Post Count',
                data: categoryCounts,
                backgroundColor: categoryGradient,  // Applied gradient for a modern look
                borderColor: '#333',
                borderWidth: 2,
                borderRadius: 8,                   // Slightly rounded edges for modern style
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1500,                   // Smooth transition
                easing: 'easeOutQuart'            // Modern easing effect
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        font: {
                            size: 14,
                            family: 'Arial',
                            weight: 'bold'
                        },
                        color: '#333'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return `${tooltipItem.label}: ${tooltipItem.raw} posts`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#555',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: '#ddd'  // Light grid lines
                    }
                },
                x: {
                    ticks: {
                        color: '#555',
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });


  // Save Report as CSV
document.getElementById('saveCSVBtn').addEventListener('click', function() {
    var csvContent = "Gender,Count\n";
    for (var gender in genderData) {
        csvContent += gender + "," + genderData[gender] + "\n";
    }
    csvContent += "\nRole,Count\n";
    for (var role in roleData) {
        csvContent += role + "," + roleData[role] + "\n";
    }
    csvContent += "\nCategory,Count\n";
    for (var category in categoryData) {
        csvContent += category + "," + categoryData[category] + "\n";
    }

    // Create a downloadable link
    var encodedUri = encodeURI('data:text/csv;charset=utf-8,' + csvContent);
    var link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'dashboard_report.csv');
    document.body.appendChild(link);
    link.click();
});

// Hover effects for the Save CSV button
var button = document.getElementById('saveCSVBtn');

// Mouseover (hover) effect
button.addEventListener('mouseover', function() {
    button.style.backgroundColor = "orangered";  // Change color on hover
});

// Mouseout effect (when hover is removed)
button.addEventListener('mouseout', function() {
    button.style.backgroundColor = "rgb(51, 184, 122)";  // Reset color
});
$(".send-email-btn").click(function() {
    var button = $(this);
    var id = button.data("id");
    var username = button.data("username");
    var email = button.data("email");

    button.prop('disabled', true);
    button.text('Email Sent');

    var subject = "Notification from Community Service System";
    var body = "Hello " + username + ",\n\nThank you for contacting us. We have reviewed your responses and have taken appropriate action.\n\nWarmest regards, Community Service System.";

    $.ajax({
        type: "POST",
        url: "send_email.php",
        data: { email: email, subject: subject, body: body },
        success: function(response) {
            button.closest('tr').remove();
            alert(response);

            $.ajax({
                type: "POST",
                url: "update_status_contact.php",
                data: { id: id, status: 'completed' },
                success: function() {
                    // Optionally update the page or database
                }
            });
        },
        error: function(xhr, status, error) {
            alert("Error: " + xhr.status + ": " + xhr.statusText);
            button.prop('disabled', false);
            button.text('Send Review Email');
        }
    });
});

</script>
<h2>Approved Members</h2>
<table>
    <thead>
        <tr>
            <!-- <th>Registration Date</th> -->
            <th>Username</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Gender</th>
            <th>Address</th>
            <th>Email</th>
            <th>Role in Community</th>
            <th>Phone Number</th>
        </tr>
    </thead>
    <tbody id="approved-members">
        <?php while ($row = $approvedResult->fetch_assoc()) { ?>
            <tr>
                 <!-- $row['CreatedAt'];  -->
                <td><?= $row['Username']; ?></td>
                <td><?= $row['FirstName']; ?></td>
                <td><?= $row['LastName']; ?></td>
                <td><?= $row['Gender']; ?></td>
                <td><?= $row['Address']; ?></td>
                <td><?= $row['Email']; ?></td>
                <td><?= $row['RoleInCommunity']; ?></td>
                <td><?= $row['PhoneNumber']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<h2>Rejected Members</h2>
<table>
    <thead>
        <tr>
            <!-- <th>Registration Date</th> -->
            <th>Username</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Gender</th>
            <th>Address</th>
            <th>Email</th>
            <th>Role in Community</th>
            <th>Phone Number</th>
        </tr>
    </thead>
    <tbody id="rejected-members">
        <?php while ($row = $rejectedResult->fetch_assoc()) { ?>
            <tr>
                <!-- $row['CreatedAt'];  -->
                <td><?= $row['Username']; ?></td>
                <td><?= $row['FirstName']; ?></td>
                <td><?= $row['LastName']; ?></td>
                <td><?= $row['Gender']; ?></td>
                <td><?= $row['Address']; ?></td>
                <td><?= $row['Email']; ?></td>
                <td><?= $row['RoleInCommunity']; ?></td>
                <td><?= $row['PhoneNumber']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<h2 class="center">Pending Responses</h2>
<table border="1">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Subject</th>
        <th>Message</th>
        <th>Submitted At</th>
    </tr>
    <?php
    if ($responseResult->num_rows > 0) {
        while ($row = $responseResult->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['Name']}</td>
                    <td>{$row['Email']}</td>
                    <td>{$row['Subject']}</td>
                    <td>{$row['Message']}</td>
                    <td>{$row['SubmittedAt']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No pending responses found.</td></tr>";
    }
    ?>
</table>

</body>
</html>

<?php
$conn->close();
?>
