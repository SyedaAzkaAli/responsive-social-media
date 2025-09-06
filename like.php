<?php
/**
 * like.php
 * --------------------------------------------------------------
 * Handles <a href="like.php?post_id=…"> links in index.php.
 * › Toggles a like for the current user on the given post.
 * › If the user already liked the post → removes the like.
 * › Otherwise → inserts a new like row.
 *
 * Requires:
 *   ▸ connect.php for the $conn object (Postgres)
 *   ▸ likes  table with UNIQUE (user_id, post_id)
 */

session_start();
require_once 'connect.php';

// ---------------------------------------------------------------------------
// 1. Ensure we know who the user is (fallback to demo user id 1)
// ---------------------------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;   // TODO: replace with real auth later
}
$user_id = (int) $_SESSION['user_id'];

// ---------------------------------------------------------------------------
// 2. Validate & sanitise the post_id coming from GET
// ---------------------------------------------------------------------------
$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
if ($post_id <= 0) {
    header('Location: index.php');
    exit;
}

// ---------------------------------------------------------------------------
// 3. Toggle like ↔︎ unlike in a single round‑trip
// ---------------------------------------------------------------------------
pg_query($conn, 'BEGIN');

$exists = pg_query_params(
    $conn,
    'SELECT 1 FROM likes WHERE user_id = $1 AND post_id = $2',
    [$user_id, $post_id]
);

if (pg_num_rows($exists) > 0) {
    // Already liked ⇒ unlike (delete row)
    pg_query_params(
        $conn,
        'DELETE FROM likes WHERE user_id = $1 AND post_id = $2',
        [$user_id, $post_id]
    );
} else {
    // Not liked yet ⇒ add like
    pg_query_params(
        $conn,
        'INSERT INTO likes (user_id, post_id) VALUES ($1, $2)',
        [$user_id, $post_id]
    );
}

pg_query($conn, 'COMMIT');

// ---------------------------------------------------------------------------
// 4. Back to where the user came from (falls back to index.php)
// ---------------------------------------------------------------------------
$ref = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $ref);
exit;
?>
