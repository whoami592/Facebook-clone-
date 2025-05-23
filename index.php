<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user info
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch posts
$posts = $conn->query("SELECT p.content, p.created_at, u.username 
                       FROM posts p 
                       JOIN users u ON p.user_id = u.user_id 
                       ORDER BY p.created_at DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION['user_id'], $content);
        $stmt->execute();
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Social Network by Mr. Sabaz Ali Khan</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
        <a href="logout.php">Logout</a>
    </header>
    <main>
        <form method="POST">
            <textarea name="content" placeholder="What's on your mind?" required></textarea>
            <button type="submit">Post</button>
        </form>
        <h2>News Feed</h2>
        <?php while ($post = $posts->fetch_assoc()): ?>
            <div class="post">
                <p><strong><?php echo htmlspecialchars($post['username']); ?></strong> 
                   (<?php echo $post['created_at']; ?>)</p>
                <p><?php echo htmlspecialchars($post['content']); ?></p>
            </div>
        <?php endwhile; ?>
    </main>
</body>
</html>