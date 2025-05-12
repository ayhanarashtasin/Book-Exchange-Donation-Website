<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bookswap');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone($phone) {
    // You can add a more advanced regex for phone validation if needed
    return preg_match('/^\+?[0-9]{10,15}$/', $phone);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $zilla = sanitizeInput($_POST['zilla']);
    $city = sanitizeInput($_POST['city']);
    $phone = sanitizeInput($_POST['phone']);

    // Validate email format
    if (!isValidEmail($email)) {
        $message = "Invalid email format.";
    }

    // Validate phone format (optional)
    if ($phone && !isValidPhone($phone)) {
        $message = "Invalid phone number format.";
    }

    // Password strength check (minimum 8 characters)
    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    }

    // Proceed if no errors
    if (!$message) {
        // Check if email already exists
        $check_email = "SELECT * FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_email);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Error: Email already exists.";
        } else {
            // Password hash
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into database
            $sql = "INSERT INTO users (name, email, password, zilla, city, phone) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $email, $hashed_password, $zilla, $city, $phone);

            if ($stmt->execute()) {
                // Store user ID in session
                $user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;

                // Redirect to success page
                header("Location: registration_success.php");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BookSwap</title>
    <link rel="stylesheet" href="register.css" />
</head>
<body>
<div class="register-container">
  <div class="register-box">
    <h2>Register</h2>
    <?php if ($message): ?>
        <div class="error"><?php echo $message; ?></div>
    <?php endif; ?>
    <form id="registerForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="input-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required />
      </div>
      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required />
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
      </div>
      <div class="input-group">
        <label for="zilla">Zilla (District)</label>
        <input type="text" id="zilla" name="zilla" required />
      </div>
      <div class="input-group">
        <label for="city">City</label>
        <input type="text" id="city" name="city" required />
      </div>
      <div class="input-group">
        <label for="phone">Mobile Phone (Optional)</label>
        <input type="text" id="phone" name="phone" />
      </div>
      <button type="submit" class="register-btn">Register</button>
      <div class="login-link">
        <p>Already have an account? <a href="login.php">Login</a></p>
      </div>
    </form>
  </div>
</div>
</body>
</html>
