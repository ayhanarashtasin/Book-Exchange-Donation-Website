<?php
session_start();
include 'connect.php'; // Make sure this file contains your database connection

// Check if a search query was submitted
if(isset($_GET['query']) && !empty($_GET['query'])) {
    $search_query = $_GET['query'];
    
    // Prepare the SQL statement to search for books
    $sql = "SELECT books.*, users.name AS owner_name 
            FROM books 
            JOIN users ON books.user_id = users.id
            WHERE (books.title LIKE ? OR books.author LIKE ?) 
            AND books.status = 'Available'";
    
    $stmt = $conn->prepare($sql);
    $search_term = "%$search_query%";
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check which books the user has already requested (for condition 2)
    $requested_books = [];
    if (isset($_SESSION['user_id'])) {
        $request_check_sql = "SELECT book_id FROM book_requests WHERE requester_id = ?";
        $request_check_stmt = $conn->prepare($request_check_sql);
        $request_check_stmt->bind_param("i", $_SESSION['user_id']);
        $request_check_stmt->execute();
        $request_check_result = $request_check_stmt->get_result();
        
        while ($row = $request_check_result->fetch_assoc()) {
            $requested_books[] = $row['book_id'];
        }
        $request_check_stmt->close();
    }
    
    // Check if user has added any books (for condition 1)
    $has_books = false;
    if (isset($_SESSION['user_id'])) {
        $check_books_sql = "SELECT COUNT(*) as book_count FROM books WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_books_sql);
        $check_stmt->bind_param("i", $_SESSION['user_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $book_count = $check_result->fetch_assoc()['book_count'];
        $has_books = ($book_count > 0);
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - BookSwap</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            color: #333;
        }

        /* Header */
        header {
            background-color: #2b3a42;
            padding: 1rem 0;
            color: white;
            text-align: center;
        }

        /* Main Content */
        main {
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 1200px;
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2b3a42;
        }

        .search-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .search-input {
            padding: 0.8rem;
            font-size: 1rem;
            width: 70%;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            margin-right: 1rem;
        }

        .search-btn {
            padding: 0.8rem 1.5rem;
            background-color: #2b3a42;
            color: white;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-btn:hover {
            background-color: #1e2a32;
        }

        .search-results {
            margin-top: 2rem;
        }

        .search-results ul {
            list-style: none;
            padding: 0;
        }

        .search-results li {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .search-results li:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .search-results h2 {
            font-size: 1.5rem;
            color: #2b3a42;
            margin-bottom: 1rem;
        }

        .search-results p {
            font-size: 1rem;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }

        .search-results a {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #2b3a42;
            color: white;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }

        .search-results a:hover {
            background-color: #1e2a32;
        }

        .no-results {
            text-align: center;
            font-size: 1.25rem;
            color: #4a5568;
        }

        footer {
            background-color: #2b3a42;
            color: white;
            text-align: center;
            padding: 1rem;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        .disabled-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #9ca3af;
            color: white;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 1rem;
            cursor: not-allowed;
        }

        .alert-message {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #ef4444;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<?php if(isset($result)): ?>
    <?php if($result->num_rows > 0): ?>
        <div class="search-results">
            <ul>
                <?php while($row = $result->fetch_assoc()): ?>
                    <li>
                        <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($row['author']); ?></p>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($row['genre']); ?></p>
                        <p><strong>Condition:</strong> <?php echo htmlspecialchars($row['book_condition']); ?></p>
                        <p><strong>Owner:</strong> <?php echo htmlspecialchars($row['owner_name']); ?></p>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $row['user_id']): ?>
                            <?php if(in_array($row['id'], $requested_books)): ?>
                                <span class="disabled-btn">Already Requested</span>
                            <?php elseif(!$has_books): ?>
                                <span class="disabled-btn">Add a book first</span>
                            <?php else: ?>
                                <form action="express_interest.php" method="post">
                                    <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="requester_id" value="<?php echo $_SESSION['user_id']; ?>">
                                    <button type="submit" class="search-btn">Interested</button>
                                </form>
                            <?php endif; ?>
                        <?php elseif(!isset($_SESSION['user_id'])): ?>
                            <a href="login.php" class="search-btn">Login to Request</a>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php else: ?>
        <p class="no-results">No books available for the search term: <strong><?php echo htmlspecialchars($search_query); ?></strong>.</p>
    <?php endif; ?>
<?php else: ?>
    <p>Please enter a search query.</p>
<?php endif; ?>
</html>

<?php
if(isset($stmt)) $stmt->close();
$conn->close();
?>