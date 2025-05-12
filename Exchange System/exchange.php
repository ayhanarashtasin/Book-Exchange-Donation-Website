<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookSwap - Exchange Your Favorites</title>
    <link rel="stylesheet" href="exchange.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header class="animated-header">
        <h1><i class="fas fa-book-open"></i> BookSwap</h1>
    </header>
    <main>
        <!-- Add Book Form -->
        <section id="add-book-section" class="card animated-card">
            <h2>Add Your Book</h2>
            <form id="add-book-form" action="add_book.php" method="POST">
                <input type="text" id="book-title" name="book-title" placeholder="Book Title" required>
                <input type="text" id="book-author" name="book-author" placeholder="Author" required>
                <select name="book-condition" id="book-condition">
    <option value="New">New</option>
    <option value="Like New">Like New</option>
    <option value="Good">Good</option>
    <option value="Fair">Fair</option>
    <option value="Poor">Poor</option>
</select>
                <input type="text" id="book-genre" name="book-genre" placeholder="Genre" required>
                <textarea id="book-details" name="book-details" placeholder="Additional Details"></textarea>
                <input type="hidden" name="book-status" value="Available">
                <button type="submit" class="btn-primary">Add Book</button>
                <div class="home-button-container">
                    <a href="index.php" class="btn-home"><i class="fas fa-home"></i> Home</a>
                </div>
            </form>
        </section>
    </main>

    <footer class="animated-footer">
        <p>&copy; 2023 BookSwap. All rights reserved.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>