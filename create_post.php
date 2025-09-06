<?php
/**
 * create_post.php  (updated)
 * ------------------------------------------------------------------
 * Saves a new post and optional image to the database.
 * Image files are now stored in the existing `/images` directory.
 * ------------------------------------------------------------------
 */

session_start();
require_once 'connect.php';

// ---------------------------------------------------------------------------
// 1. Ensure user is logged‑in (fallback to demo user id 1 during development)
// ---------------------------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;   // remove this line once real auth is in place
}
$user_id = (int) $_SESSION['user_id'];

// Only accept POST requests --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ---------------------------------------------------------------------------
// 2. Validate content
// ---------------------------------------------------------------------------
$content   = trim($_POST['content'] ?? '');
$hasImage  = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

if ($content === '' && !$hasImage) {
    $_SESSION['flash_error'] = 'Post cannot be empty.';
    header('Location: index.php');
    exit;
}

// ---------------------------------------------------------------------------
// 3. Handle image upload (optional)  – will land in  /images/
// ---------------------------------------------------------------------------
$image_url = null;
if ($hasImage && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext        = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        $_SESSION['flash_error'] = 'Only JPG, PNG, GIF or WEBP images are allowed.';
        header('Location: index.php');
        exit;
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5 MB limit
        $_SESSION['flash_error'] = 'Image exceeds 5 MB.';
        header('Location: index.php');
        exit;
    }

    // Existing images directory
    $imgDirAbs = __DIR__ . '/images/';
    if (!is_dir($imgDirAbs)) {
        $_SESSION['flash_error'] = 'Images directory not found.';
        header('Location: index.php');
        exit;
    }

    // Unique file name to avoid clashes
    $filename = uniqid('post_', true) . '.' . $ext;
    $destAbs  = $imgDirAbs . $filename;      // absolute path for move_uploaded_file()
    $image_url = 'images/' . $filename;      // relative path stored in DB / used in <img>

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destAbs)) {
        $_SESSION['flash_error'] = 'Image upload failed.';
        header('Location: index.php');
        exit;
    }
}

// ---------------------------------------------------------------------------
// 4. Save post to database
// ---------------------------------------------------------------------------
$insert = 'INSERT INTO posts (user_id, content, image_url) VALUES ($1, $2, $3)';
$result = pg_query_params($conn, $insert, [$user_id, $content, $image_url]);

if (!$result) {
    error_log('Post insert failed: ' . pg_last_error($conn));
    $_SESSION['flash_error'] = 'Could not save your post. Please try again.';
}

// ---------------------------------------------------------------------------
// 5. All done – back to feed
// ---------------------------------------------------------------------------
header('Location: index.php');
exit;
?>
