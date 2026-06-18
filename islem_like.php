<?php
session_start();
include 'baglan.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: forum.php");
    exit();
}

$cid = $_GET['id'];
$uid = $_SESSION['user_id'];
$pid = $_GET['post_id'];

// Daha önce beğenmiş mi kontrol et
$kontrol = $db->prepare("SELECT * FROM comment_likes WHERE comment_id = ? AND user_id = ?");
$kontrol->execute([$cid, $uid]);

if ($kontrol->rowCount() > 0) {
    // Varsa kaldır
    $sil = $db->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
    $sil->execute([$cid, $uid]);
} else {
    // Yoksa ekle
    $ekle = $db->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
    $ekle->execute([$cid, $uid]);
}

header("Location: konu_detay.php?id=" . $pid);
exit();