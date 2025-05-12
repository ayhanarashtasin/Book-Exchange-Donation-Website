<?php
// You can add PHP logic here, such as session handling or database connections
session_start();
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Handle the exchange link click
if (isset($_GET['action']) && $_GET['action'] == 'exchange') {
    if (isLoggedIn()) {
        header("Location: exchange.php");
        exit();
    } else {
        // Set a session variable to redirect after login
        $_SESSION['redirect_after_login'] = 'exchange.php';
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BookSwap - Where Books Find Their Next Reader</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <!-- Header Section -->
  <header>
    <nav class="navbar">
      <div class="navbar-left">
        <ul class="nav-links left-links">
          <li><a href="donation_list.php">Donation Book List</a></li>
          <li><a href="book_list.php">Exchange Book List</a></li>
          <li><a href="forum.php">Community Forum</a></li>
        </ul>
      </div>
      <div class="navbar-right">
        <ul class="nav-links right-links">
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="my_donations.php">My Books-Donation</a></li>
            <li><a href="user_books.php">My Books-Swap</a></li>
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

  <!-- Main Content Section -->
  <main class="main-container">
    <h1 class="animated-heading">Where books find their next reader</h1>

    <!-- Search Section -->
    <section class="search-container">
      <form action="search.php" method="GET">
        <input type="text" name="query" class="search-input" placeholder="Search for books..." />
        <button type="submit" class="search-btn">
          <span class="search-icon">🔍</span>
        </button>
      </form>
    </section>

    <!-- Cards Section -->
    <section class="card-container">
      <a href="?action=exchange" class="card1">
        <div class="avatar">
          <img src="image.jpg" alt="Book Exchange" />
        </div>
        <h3>Book</h3>
        <p>Exchange</p>
      </a>
      <a href="donation.php" class="card2">
        <div class="avatar">
          <img src="image2.jpg" alt="Book Donation" />
        </div>
        <h3>Book</h3>
        <p>Donation</p>
      </a>
    </section>
  </main>

  <!-- Footer Section -->
  <footer>
    <p>&copy; <?php echo date("Y"); ?> BookSwap. All rights reserved.</p>
  </footer>
</body>
</html>