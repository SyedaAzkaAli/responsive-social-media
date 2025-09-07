<?php
session_start();
require_once 'connect.php';

$user_id = $_SESSION['user_id'] ?? 1; // Make sure this is correctly set after user login
$request_id = (int)($_GET['id'] ?? 0);

// Validate request ID
if($request_id <= 0){
    header('Location: index.php'); // Redirect to home if ID is invalid
    exit;
}

// Perform the decline action
// Ensure the request belongs to the current user and is pending
pg_query_params($conn,
    'UPDATE friend_requests SET status=\'declined\' WHERE request_id=$1 AND receiver_id=$2 AND status=\'pending\'',
    [$request_id, $user_id]
);

// Redirect back to the previous page or a default home page after processing
// This is the standard way to handle actions in PHP scripts.
header('Location: '.$_SERVER['HTTP_REFERER'] ?? 'index.php');
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nokoSocial</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <link rel="stylesheet" href="./style.css">
</head>
<body>
<!-- navbar markup (unchanged) -->
<main>
    <div class="container">
        <!-- left column unchanged -->

        <!-- ======================= MIDDLE ======================= -->
        <div class="middle">
            <!-- STORIES and CREATE POST (unchanged) -->

            <!-- FEEDS -->
            <div class="feeds">
                <?php while($p=pg_fetch_assoc($posts)): ?>
                <div class="feed">
                    <!-- head, photo, buttons unchanged -->

                    <!-- COMMENTS -->
                    <?php $comments=pg_query_params($conn,'SELECT c.content,c.created_at,u.full_name,u.profile_photo FROM comments c JOIN users u ON u.user_id=c.user_id WHERE c.post_id=$1 ORDER BY c.created_at',[$p['post_id']]); ?>
                    <div class="comments">
                        <?php if($comments && pg_num_rows($comments)>0): ?>
                            <?php while($c=pg_fetch_assoc($comments)): ?>
                                <div class="comment"><div class="profile-photo"><img src="<?php echo htmlspecialchars($c['profile_photo']); ?>" alt></div><div class="comment-body"><b><?php echo htmlspecialchars($c['full_name']); ?></b> <?php echo nl2br(htmlspecialchars($c['content'])); ?> <small class="text-muted"><?php echo time_elapsed_string($c['created_at']); ?></small></div></div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                        <!-- ADD COMMENT FORM -->
                        <form class="add-comment" method="post" action="add_comment.php">
                            <input type="hidden" name="post_id" value="<?php echo $p['post_id']; ?>">
                            <input type="text" name="comment" placeholder="Add a comment..." required>
                            <button type="submit" class="btn">Post</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- RIGHT column unchanged (friend request links already point to accept_request.php & decline_request.php) -->
    </div>
</main>
<script src="./index.js"></script>
</body>
</html>


