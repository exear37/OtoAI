<?php
session_start();
session_destroy(); // Tüm oturum verilerini siler
header("Location: index.php"); // Ana sayfaya geri gönderir
exit();
?>