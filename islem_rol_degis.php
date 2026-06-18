<?php
session_start();
include 'baglan.php';

// Güvenlik: Sadece admin işlem yapabilir
if ($_SESSION['role'] == 'admin' && isset($_GET['id']) && isset($_GET['rol'])) {
    $uid = $_GET['id'];
    $yeniRol = $_GET['rol'];
    
    $guncelle = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    if($guncelle->execute([$yeniRol, $uid])) {
        header("Location: admin.php?durum=ok");
    } else {
        header("Location: admin.php?durum=hata");
    }
} else {
    header("Location: index.php");
}
exit();