<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Function to sanitize inputs
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user inputs from form
    $email = sanitizeInput($_POST['username']); // Email input field
    $password = $_POST['password']; // Password input field

    // Check if email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user is found, verify the password
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];

            // Check if there's a redirect URL
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']); // Clear the session variable
                header("Location: " . $redirect_url);
                exit();
            } else {
                // Redirect to the home page if no redirect URL
                header("Location: index.php");
                exit();
            }
        } else {
            // Invalid password
            $message = "Incorrect password. Please try again.";
        }
    } else {
        // Email not found
        $message = "Email not found. Please check your credentials.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <link rel="stylesheet" href="login.css" />
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <h2>Login</h2>

      <!-- Display error message if login fails -->
      <?php if (isset($message)): ?>
        <div class="error"><?php echo $message; ?></div>
      <?php endif; ?>

      <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <div class="input-group">
          <label for="username">Email</label>
          <input type="text" id="username" name="username" required />
        </div>
        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <div class="remember-me">
          <input type="checkbox" id="remember-me" name="remember-me" />
          <label for="remember-me">Remember me</label>
        </div>
        <div class="forgot-password">
          <a href="forgot-password.php">Forgot password?</a>
        </div>
        <button type="submit" class="login-btn">Login</button>
        <div class="register-link">
          <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
