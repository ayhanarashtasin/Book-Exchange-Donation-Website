<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - BookSwap</title>
    <link rel="stylesheet" href="registration_success.css" />
</head>
<body>
<div class="success-container">
  <div class="success-box">
    <h2>Registration Successful!</h2>
    <p>Thank you for registering with BookSwap. Your account has been created successfully.</p>
    <p>Your email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
    <div class="button-group">
      <a href="index.php" class="btn">Go to Homepage</a>
      <a href="profile.php" class="btn">View Profile</a>
    </div>
  </div>
</div>
</body>
</html>