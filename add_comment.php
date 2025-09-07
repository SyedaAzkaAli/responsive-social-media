<?php
session_start();
require_once 'connect.php';

// Ensure POST
if($_SERVER['REQUEST_METHOD']!=='POST'){
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1; // demo fallback
$post_id = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['comment'] ?? '');

if($post_id<=0 || $content===''){
    header('Location: index.php');
    exit;
}

pg_query_params($conn,'INSERT INTO comments (post_id,user_id,content) VALUES ($1,$2,$3)',[$post_id,$user_id,$content]);

header('Location: '.$_SERVER['HTTP_REFERER'] ?? 'index.php');
exit;
?>
