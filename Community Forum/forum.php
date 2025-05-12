<?php
session_start();
require_once 'connect.php';

// Display session messages
if (isset($_SESSION['message'])) {
    echo '<div class="success-message">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

// Fetch all forum posts with like counts
$sql = "SELECT fp.*, u.name as author_name, COUNT(fl.id) as like_count
        FROM forum_posts fp 
        JOIN users u ON fp.user_id = u.id 
        LEFT JOIN forum_likes fl ON fp.id = fl.post_id
        GROUP BY fp.id
        ORDER BY fp.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Error fetching posts: " . $conn->error);
}

// Handle new post creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $insert_sql = "INSERT INTO forum_posts (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iss", $user_id, $title, $content);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Post created successfully!";
        header("Location: forum.php");
        exit();
    } else {
        $error = "Error creating post: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookSwap Community Forum</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #bb86fc;
            text-align: center;
            margin-bottom: 30px;
        }

        .post-form {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .post-form h2 {
            color: #03dac6;
            margin-top: 0;
        }

        .post-form input[type="text"],
        .post-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #2c2c2c;
            border: 1px solid #3d3d3d;
            color: #e0e0e0;
            border-radius: 3px;
        }

        .post-form button {
            background-color: #bb86fc;
            color: #121212;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 3px;
            transition: background-color 0.3s;
        }

        .post-form button:hover {
            background-color: #9965f4;
        }

        .post {
            background-color: #1e1e1e;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .post-title {
            color: #03dac6;
            margin-top: 0;
        }

        .post-meta {
            color: #bb86fc;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .post-content {
            margin-bottom: 15px;
            color: #ffffff; /* Changed to white */
        }

        .post-actions {
            margin-top: 15px;
        }

        .like-btn {
            cursor: pointer;
            color: #03dac6;
            transition: color 0.3s;
        }

        .like-btn:hover {
            color: #bb86fc;
        }

        .error {
            color: #cf6679;
            margin-bottom: 10px;
        }

        .success-message {
            color: #03dac6;
            background-color: #1e1e1e;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        .error-message {
            color: #cf6679;
            background-color: #1e1e1e;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Community Forum</h1>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="post-form">
                <h2>Create a New Post</h2>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <form action="forum.php" method="POST">
                    <input type="text" id="title" name="title" placeholder="Title" required>
                    <textarea id="content" name="content" placeholder="Content" required></textarea>
                    <button type="submit">Create Post</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="posts">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="post" data-post-id="<?php echo $row['id']; ?>">
                        <h2 class="post-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                        <p class="post-meta">Posted by <?php echo htmlspecialchars($row['author_name']); ?> on <?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></p>
                        <p class="post-content"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                        <div class="post-actions">
                            <span class="like-btn" onclick="likePost(<?php echo $row['id']; ?>)">Like (<span class="like-count"><?php echo $row['like_count']; ?></span>)</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function likePost(postId) {
            $.post('like_post.php', { post_id: postId }, function(response) {
                if (response.success) {
                    $('.post[data-post-id="' + postId + '"] .like-count').text(response.likes);
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json')
            .fail(function() {
                alert('Error: Could not process the like action.');
            });
        }
    </script>
</body>
</html>