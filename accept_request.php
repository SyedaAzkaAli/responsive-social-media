<?php
session_start();
require_once 'connect.php';

$user_id = $_SESSION['user_id'] ?? 1;
$request_id = (int)($_GET['id'] ?? 0);
if($request_id<=0){ header('Location: index.php'); exit; }

pg_query_params($conn,'UPDATE friend_requests SET status=\'accepted\' WHERE request_id=$1 AND receiver_id=$2 AND status=\'pending\'',[$request_id,$user_id]);

header('Location: '.$_SERVER['HTTP_REFERER'] ?? 'index.php');
exit;
?>
