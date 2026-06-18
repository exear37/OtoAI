<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "oto_destek"; // Değişken adını değiştirdik çakışma olmasın diye

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Hatayı ekrana net bir şekilde yazdıralım ki sorunu şıp diye bulalım
    die("Veritabanı bağlantısı başarısız oldu! HATA DETAYI: " . $e->getMessage());
}
?>