<?php
session_start();
require_once 'connect.php';

// -----------------------------------------------------------------------------
// Helper: Convert timestamp to "time‑ago" string (e.g. "2 DAYS AGO")
// -----------------------------------------------------------------------------
function time_elapsed_string($datetime, $full = false)
{
    $now  = new DateTime;
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w  = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// For demo we hard‑code the logged‑in user (Diana, user_id = 1)
$current_user_id = 1;

// -----------------------------------------------------------------------------
// 1. Fetch data needed for the page in ONE round‑trip when possible
// -----------------------------------------------------------------------------
$notifications = pg_query_params(
    $conn,
    'SELECT n.notification_id, n.message, n.is_read, n.created_at,
            u.full_name, u.profile_photo
     FROM notifications n
     JOIN users u ON n.user_id = u.user_id
     WHERE n.user_id = $1
     ORDER BY n.created_at DESC LIMIT 10',
    [$current_user_id]
);

$messages = pg_query_params(
    $conn,
    'SELECT m.message_id, m.content, m.created_at,
            u.full_name, u.profile_photo
     FROM messages m
     JOIN users u ON u.user_id = m.sender_id
     WHERE m.receiver_id = $1
     ORDER BY m.created_at DESC LIMIT 10',
    [$current_user_id]
);

$posts = pg_query(
    $conn,
    'SELECT p.post_id, p.content, p.image_url, p.created_at,
            u.full_name, u.username, u.profile_photo,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS like_cnt
     FROM posts p
     JOIN users u ON u.user_id = p.user_id
     ORDER BY p.created_at DESC'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nokoSocial</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <link rel="stylesheet" href="./style.css">
</head>
<body>
<nav>
    <div class="container">
        <h2 class="log">nokoSocial</h2>
        <div class="search-bar">
            <i class="uil uil-search"></i>
            <input type="search" placeholder="Search for creators, inspirations, and projects">
        </div>
        <div class="create">
            <label class="btn btn-primary" for="create-post">Create</label>
            <div class="profile-photo">
                <img src="<?php echo htmlspecialchars($currentUser['profile_photo'] ?? 'images/profile-1.jpg'); ?>" alt="profile">
            </div>
            <button id="menu-btn"><i class="uil uil-bars"></i></button>
        </div>
    </div>
</nav>

<main>
    <div class="container">
        <!--======================== LEFT ==========================-->
        <div class="left">
            <a class="profile">
                <div class="profile-photo">
                    <img src="<?php echo htmlspecialchars($currentUser['profile_photo'] ?? 'images/profile-1.jpg'); ?>" alt="profile">
                </div>
                <div class="handle">
                    <h4><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Guest'); ?></h4>
                    <p class="text-muted">@<?php echo htmlspecialchars($currentUser['username'] ?? 'guest'); ?></p>
                </div>
            </a>
            <!-- SIDEBAR -->
            <div class="sidebar">
                <!-- other menu‑items ... -->

                <!-- Notifications item -->
                <a class="menu-item" id="notifications">
                    <span><i class="uil uil-bell"><small class="notification-count"><?php echo pg_num_rows($notifications); ?></small></i></span>
                    <h3>Notifications</h3>
                    <div class="notifications-popup">
                        <?php while ($n = pg_fetch_assoc($notifications)): ?>
                            <div>
                                <div class="profile-photo"><img src="<?php echo htmlspecialchars($n['profile_photo']); ?>" alt="profile"></div>
                                <div class="notification-body">
                                    <?php echo htmlspecialchars($n['message']); ?>
                                    <small class="text-muted"><?php echo strtoupper(time_elapsed_string($n['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </a>
                <!-- /Notifications item -->

                <!-- Messages item -->
                <a class="menu-item" id="messages-notification">
                    <span><i class="uil uil-envelope-alt"><small class="notification-count"><?php echo pg_num_rows($messages); ?></small></i></span>
                    <h3>Messages</h3>
                </a>
            </div>
        </div>

        <!--======================== MIDDLE (Posts Feed) ==========================-->
        <div class="middle">
            <!-- Create Post form (optional) -->
            <form class="create-post" method="post" action="create_post.php" enctype="multipart/form-data">
                <div class="profile-photo"><img src="<?php echo htmlspecialchars($currentUser['profile_photo'] ?? 'images/profile-1.jpg'); ?>" alt="profile"></div>
                <input type="text" name="content" placeholder="What's on your mind, <?php echo htmlspecialchars($currentUser['full_name'] ?? 'friend'); ?>?" required>
                <input type="file" name="image" accept="image/*">
                <input type="submit" value="Post" class="btn btn-primary">
            </form>

            <!-- FEEDS -->
            <div class="feeds">
                <?php while ($p = pg_fetch_assoc($posts)): ?>
                    <div class="feed">
                        <div class="head">
                            <div class="user">
                                <div class="profile-photo"><img src="<?php echo htmlspecialchars($p['profile_photo']); ?>" alt="profile"></div>
                                <div class="ingo">
                                    <h3><?php echo htmlspecialchars($p['full_name']); ?></h3>
                                    <small><?php echo strtoupper(time_elapsed_string($p['created_at'])); ?></small>
                                </div>
                            </div>
                            <span class="edit"><i class="uil uil-ellipsis-h"></i></span>
                        </div>
                        <?php if ($p['image_url']): ?>
                            <div class="photo"><img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="post"></div>
                        <?php endif; ?>
                        <div class="action-buttons">
                            <div class="interaction-buttons">
                                <span><a href="like.php?post_id=<?php echo $p['post_id']; ?>"><i class="uil uil-heart"></i></a></span>
                                <span><i class="uil uil-comment-dots"></i></span>
                                <span><i class="uil uil-share-alt"></i></span>
                            </div>
                            <div class="bookmark"><span><i class="uil uil-bookmark-full"></i></span></div>
                        </div>
                        <div class="liked-by">
                            <p>Liked by <b><?php echo $p['like_cnt']; ?> others</b></p>
                        </div>
                        <div class="caption"><p><b><?php echo htmlspecialchars($p['full_name']); ?></b> <?php echo nl2br(htmlspecialchars($p['content'])); ?></p></div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!--======================== RIGHT (Messages) ==========================-->
        <div class="right">
            <div class="messages">
                <div class="heading"><h4>Messages</h4><i class="uil uil-edit"></i></div>
                <div class="search-bar"><i class="uil uil-search"></i><input type="search" placeholder="Search messages" id="message-search"></div>
                <div class="category"><h6 class="active">Primary</h6><h6>General</h6></div>

                <?php while ($m = pg_fetch_assoc($messages)): ?>
                    <div class="message">
                        <div class="profile-photo"><img src="<?php echo htmlspecialchars($m['profile_photo']); ?>" alt="profile"></div>
                        <div class="message-body">
                            <h5><?php echo htmlspecialchars($m['full_name']); ?></h5>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($m['content'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</main>
<script src="./index.js"></script>
</body>
</html>
