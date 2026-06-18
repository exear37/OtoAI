<?php 
session_start();
include 'baglan.php';

// Oturum kontrolü
if(!isset($_SESSION['user_id'])) { header("Location: giris.php"); exit(); }

$uid = $_SESSION['user_id'];
$mesaj = "";

// Kullanıcı bilgilerini çek 
$sorgu = $db->prepare("SELECT * FROM users WHERE id = ?");
$sorgu->execute([$uid]);
$user = $sorgu->fetch(PDO::FETCH_ASSOC);

// FORM GÖNDERİLDİĞİNDE ÇALIŞACAK MOTOR
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Profil Resmi Yükleme İşlemi
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== 4) {
        $dosya = $_FILES['avatar'];
        $izin_verilenler = ['jpg', 'jpeg', 'png', 'webp'];
        $uzanti = strtolower(pathinfo($dosya['name'], PATHINFO_EXTENSION));
        
        // Klasör kontrolü (Eksiği giderir)
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        if ($dosya['error'] !== 0) {
            $mesaj = "Dosya yükleme hatası! Kod: " . $dosya['error'];
        } elseif (!in_array($uzanti, $izin_verilenler)) {
            $mesaj = "Hata: Sadece JPG, PNG ve WEBP formatları kabul edilir.";
        } else {
            // Benzersiz isim oluşturma
            $yeni_ad = "user_" . $uid . "_" . time() . "." . $uzanti;
            $hedef = "uploads/" . $yeni_ad;
            
            if (move_uploaded_file($dosya['tmp_name'], $hedef)) {
                // Eski resmi sil 
                if(!empty($user['profile_pic']) && $user['profile_pic'] != 'default.png' && file_exists("uploads/".$user['profile_pic'])){
                    unlink("uploads/".$user['profile_pic']);
                }

                // Veritabanını güncelle
                $guncelle = $db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                if($guncelle->execute([$yeni_ad, $uid])) {
                    $mesaj = "Profil resmi başarıyla güncellendi!";
                    // Verileri tazeleyelim
                    $sorgu->execute([$uid]);
                    $user = $sorgu->fetch(PDO::FETCH_ASSOC);
                }
            } else {
                $mesaj = "Dosya taşınamadı! Klasör yazma izinlerini kontrol edin.";
            }
        }
    }

    // 2. Şifre Değiştirme İşlemi
    if (!empty($_POST['new_pass'])) {
        $yeni_sifre = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
        $guncelle_pass = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        if($guncelle_pass->execute([$yeni_sifre, $uid])) {
            $mesaj = "Şifreniz başarıyla değiştirildi!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Merkezi | OtoAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/dist/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Space Grotesk', sans-serif; 
            background: radial-gradient(circle at top right, #1e40af, #0f172a, #030712); 
            min-height: 100vh; 
            color: #f1f5f9;
        }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .preview-glow { box-shadow: 0 0 40px rgba(59, 130, 246, 0.5); border-color: #3b82f6 !important; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #1e40af; border-radius: 10px; }
    </style>
</head>
<body class="p-6 md:p-12">

    <div class="max-w-5xl mx-auto">
        <!-- Üst Navigasyon -->
        <div class="flex justify-between items-center mb-12">
            <a href="index.php" class="text-blue-400 hover:text-blue-300 font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-chevron-left text-xs"></i> Teşhis Merkezi
            </a>
            <div class="text-right">
                <h1 class="text-3xl font-black uppercase tracking-tighter italic">Hesap <span class="text-blue-500">Ayarları</span></h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">OtoAI Command Center</p>
            </div>
        </div>

        <?php if($mesaj): ?>
            <div class="bg-blue-600/10 border border-blue-500/50 text-blue-400 p-5 rounded-2xl mb-10 font-bold text-center animate-pulse">
                <i class="fa-solid fa-circle-info mr-2"></i> <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- SOL PANEL: Profil Fotoğrafı -->
            <div class="glass p-10 rounded-[3rem] text-center h-fit">
                <form action="hesap.php" method="POST" enctype="multipart/form-data" id="avatarForm">
                    <div class="relative group mx-auto w-44 h-44 mb-8">
                        <!-- Profil Resmi (id="previewImage") -->
                        <img id="previewImage" src="uploads/<?php echo $user['profile_pic'] ?: 'default.png'; ?>" 
                             class="w-44 h-44 rounded-full object-cover border-4 border-white/10 shadow-2xl transition-all duration-500 group-hover:brightness-50">
                        
                        <!-- Seçme Katmanı -->
                        <label for="avatarInput" class="absolute inset-0 flex flex-col items-center justify-center cursor-pointer bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-white mb-2"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-white">Resmi Değiştir</span>
                        </label>
                        <input type="file" name="avatar" id="avatarInput" accept="image/*" class="hidden">
                    </div>

                    <h3 class="text-2xl font-black italic tracking-tight text-white"><?php echo $user['username']; ?></h3>
                    <span class="bg-blue-600/20 text-blue-400 text-[10px] px-4 py-1 rounded-full font-black uppercase tracking-widest mt-3 inline-block border border-blue-500/30">
                        <?php echo $user['role']; ?>
                    </span>

                    <!-- Seçim Yapıldığında Çıkan Kaydet Butonu -->
                    <div id="saveBtn" class="mt-8 hidden animate-bounce">
                        <p class="text-[10px] text-yellow-500 font-black uppercase mb-4 tracking-tighter">Değişiklikleri onaylıyor musun?</p>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-xl shadow-blue-900/40 border border-blue-400/20 text-white">
                            Sistemi Güncelle
                        </button>
                    </div>
                </form>
            </div>

            <!-- SAĞ PANEL: Güvenlik Ayarları -->
            <div class="lg:col-span-2 space-y-8">
                <div class="glass p-10 rounded-[3rem] border border-white/5 shadow-2xl">
                    <h3 class="text-xl font-black mb-10 flex items-center gap-4 italic uppercase tracking-tight text-blue-400">
                        <i class="fa-solid fa-shield-halved"></i> Güvenlik Protokolü
                    </h3>
                    
                    <form method="POST" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] text-slate-500 uppercase font-black tracking-widest ml-1">Kullanıcı Kimliği</label>
                                <div class="bg-black/40 p-4 rounded-2xl border border-white/5 text-slate-400 font-bold flex items-center gap-3">
                                    <i class="fa-solid fa-at text-blue-500"></i> <?php echo $user['username']; ?>
                                </div>
                                <p class="text-[9px] text-slate-600 italic ml-1">Kullanıcı adı sistem tarafından sabitlenmiştir.</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] text-slate-500 uppercase font-black tracking-widest ml-1">Yeni Erişim Şifresi</label>
                                <div class="relative">
                                    <input type="password" name="new_pass" placeholder="••••••••" 
                                           class="w-full bg-slate-950/50 p-4 rounded-2xl border border-white/10 focus:outline-none focus:border-blue-500 transition-all font-bold placeholder:text-slate-800 text-white">
                                    <i class="fa-solid fa-key absolute right-5 top-1/2 -translate-y-1/2 text-slate-700"></i>
                                </div>
                                <p class="text-[9px] text-slate-600 italic ml-1">Şifre en az 8 karakter ve karmaşık olmalıdır.</p>
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" class="w-full bg-white/5 hover:bg-white/10 py-5 rounded-2xl font-black uppercase tracking-widest text-xs border border-white/10 transition-all active:scale-95 text-white hover:border-blue-500/50">
                                Kimlik Bilgilerini Kaydet
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Durum Bilgisi Card -->
                <div class="glass p-8 rounded-[2rem] border-l-4 border-green-500 flex items-center justify-between">
                    <div class="flex items-center gap-5">
                        <div class="bg-green-500/20 p-4 rounded-2xl text-green-500">
                            <i class="fa-solid fa-user-shield text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white uppercase text-sm tracking-tight">Sistem Durumu: Çevrimiçi</h4>
                            <p class="text-[10px] text-slate-500 mt-1 uppercase font-bold tracking-widest">Giresun Erişim Noktası</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-check-double text-green-500 animate-pulse"></i>
                </div>
            </div>

        </div>
    </div>

    <!-- ANINDA ÖNİZLEME SİHRİ (JavaScript FileReader) -->
    <script>
        const avatarInput = document.getElementById('avatarInput');
        const previewImage = document.getElementById('previewImage');
        const saveBtn = document.getElementById('saveBtn');

        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Resmi anında değiştir
                    previewImage.src = event.target.result;
                    // Mavi parlatma efekti ekle (Lead dokunuşu)
                    previewImage.classList.add('preview-glow');
                    // Kaydet butonunu göster
                    saveBtn.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

</body>
</html>