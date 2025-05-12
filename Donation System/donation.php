<?php
session_start();
$message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'bookswap');
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get form data
    $title = $_POST['book-title'];
    $author = $_POST['book-author'];
    $type = $_POST['book-type'];
    $condition = $_POST['book-condition'];
    $description = $_POST['book-description'];
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Prepare SQL statement
        $sql = "INSERT INTO donations (title, author, book_type, book_condition, description, user_id, donation_date) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $title, $author, $type, $condition, $description, $user_id);
        
        if ($stmt->execute()) {
            // Get the ID of the inserted donation
            $donation_id = $conn->insert_id;
            
            // Redirect to donation details page
            header("Location: donation_details.php?id=" . $donation_id);
            exit();
        } else {
            $message = '<div class="error-message">Sorry, there was an error processing your donation. Please try again.</div>';
        }
        
        $stmt->close();
    } else {
        $message = '<div class="error-message">Please log in to donate a book.</div>';
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Donation - BookSwap</title>
    <link rel="stylesheet" href="donation.css">
    <style>
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="navbar-left">
                <ul class="nav-links left-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="donation_list.php">Donation Book List</a></li>
                </ul>
            </div>
            <div class="navbar-right">
                <ul class="nav-links right-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.php">Sign in</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="donation-container">
        <h1 class="animated-heading">Donate Books, Change Lives</h1>
        
        <?php echo $message; ?>
        
        <section class="donation-info">
            <h2>Why Donate?</h2>
            <p>Your book donations can make a significant impact on underprivileged students' lives. Every book you donate opens up a world of knowledge and opportunities.</p>
        </section>

        <section class="donation-form">
            <h2>Donate a Book</h2>
            <form id="book-donation-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" id="book-title" name="book-title" placeholder="Book Title" required>
                <input type="text" id="book-author" name="book-author" placeholder="Author" required>
                <select id="book-type" name="book-type" required>
                    <option value="">Select Book Type</option>
                    <option value="academic">Academic</option>
                    <option value="fiction">Fiction</option>
                    <option value="non-fiction">Non-Fiction</option>
                    <option value="romance">Romance</option>
                    <option value="mystery">Mystery</option>
                    <option value="sci-fi">Science Fiction</option>
                    <option value="other">Other</option>
                </select>
                <select id="book-condition" name="book-condition" required>
                    <option value="">Select Book Condition</option>
                    <option value="new">New</option>
                    <option value="like-new">Like New</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                </select>
                <textarea id="book-description" name="book-description" placeholder="Brief description of the book" required></textarea>
                <button type="submit" class="donate-btn">Donate Now</button>
            </form>
        </section>

        <section class="writer-section">
            <h2>Are You a Writer?</h2>
            <p>If you're an author looking to donate your books or contribute to our cause, we'd love to hear from you!</p>
            <form id="writer-form">
                <input type="text" id="writer-name" placeholder="Your Name" required>
                <input type="email" id="writer-email" placeholder="Your Email" required>
                <textarea id="writer-message" placeholder="Tell us about your books or how you'd like to contribute" required></textarea>
                <button type="submit" class="writer-btn">Contact Us</button>
            </form>
        </section>

        <section class="donation-impact">
            <h2>Your Impact</h2>
            <div class="impact-counter">
                <span id="books-donated">0</span>
                <p>Books Donated</p>
            </div>
            <div class="impact-counter">
                <span id="students-helped">0</span>
                <p>Students Helped</p>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
    </footer>

    <script src="donation.js"></script>
</body>
</html>