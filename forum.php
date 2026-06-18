<?php 
session_start();
include 'baglan.php';

// ==========================================
// YENİ: KONU SİLME İŞLEMİ
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    if (isset($_SESSION['user_id'])) {
        $sil_id = intval($_POST['delete_post_id']);
        
        // Güvenlik: Sadece konuyu açan kişi (veya admin) silebilir
        $yetki_sorgu = $db->prepare("SELECT user_id, image_path FROM forum_posts WHERE id = ?");
        $yetki_sorgu->execute([$sil_id]);
        $konu_bilgi = $yetki_sorgu->fetch(PDO::FETCH_ASSOC);

        if ($konu_bilgi && $konu_bilgi['user_id'] == $_SESSION['user_id']) {
            // Konuya ait yüklenmiş bir resim varsa, sunucudan dosyayı sil[cite: 3]
            if (!empty($konu_bilgi['image_path']) && file_exists($konu_bilgi['image_path'])) {
                unlink($konu_bilgi['image_path']);
            }

            // HATA ÇÖZÜMÜ: Önce yorumlara gelen beğenileri sil (Zincirleme silme)[cite: 3]
            $db->query("DELETE FROM comment_likes WHERE comment_id IN (SELECT id FROM forum_comments WHERE post_id = $sil_id)");
            
            // Bağlı yorumları ve konunun kendisini sil[cite: 3]
            $db->query("DELETE FROM forum_comments WHERE post_id = $sil_id");
            $db->query("DELETE FROM forum_posts WHERE id = $sil_id");
            
            header("Location: forum.php");
            exit;
        }
    }
}

// ==========================================
// YENİ: HIZLI RESİMLİ KONU EKLEME İŞLEMİ
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hizli_konu_ekle'])) {
    if (isset($_SESSION['user_id'])) {
        $baslik = trim($_POST['title']);
        $user_id = $_SESSION['user_id'];
        $image_path = null;

        // Resim Yükleme Kontrolü[cite: 3]
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
            $izin_verilenler = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $dosya_uzantisi = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($dosya_uzantisi, $izin_verilenler)) {
                // Rastgele benzersiz bir isim oluştur[cite: 3]
                $yeni_ad = uniqid('oto_') . '.' . $dosya_uzantisi;
                $hedef = 'uploads/' . $yeni_ad;
                
                // Klasör yoksa oluştur[cite: 3]
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                // Dosyayı taşı[cite: 3]
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], $hedef)) {
                    $image_path = $hedef;
                }
            }
        }

        if (!empty($baslik)) {
            // İçerik detayı olmadan sadece başlık ve resimle hızlı konu açılışı[cite: 3]
            $ekle = $db->prepare("INSERT INTO forum_posts (user_id, title, content, image_path, created_at) VALUES (?, ?, '', ?, NOW())");
            $ekle->execute([$user_id, $baslik, $image_path]);
            
            header("Location: forum.php");
            exit;
        }
    }
}


// Tüm konuları çekiyoruz
$sorgu = $db->query("SELECT forum_posts.*, users.username FROM forum_posts JOIN users ON forum_posts.user_id = users.id ORDER BY created_at DESC");
$konular = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// Popüler konuları (en çok yorum alanlar) çekiyoruz[cite: 3]
$populer_sorgu = $db->query("SELECT forum_posts.*, users.username, COUNT(forum_comments.id) as comment_count 
    FROM forum_posts 
    LEFT JOIN forum_comments ON forum_posts.id = forum_comments.post_id 
    JOIN users ON forum_posts.user_id = users.id 
    GROUP BY forum_posts.id 
    ORDER BY comment_count DESC LIMIT 3");
$populerler = $populer_sorgu->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// AKILLI MARKA TESPİT VE LOGO SİSTEMİ
// ==========================================
function markaTespitEt($metin) {
    $markalar = ['bmw', 'mercedes', 'audi', 'volkswagen', 'vw', 'renault', 'fiat', 'ford', 'toyota', 'honda', 'peugeot', 'opel', 'hyundai', 'kia', 'nissan', 'skoda', 'dacia', 'seat', 'volvo', 'chevrolet', 'mazda', 'suzuki', 'subaru', 'porsche', 'jeep', 'tesla'];
    $metin = strtolower($metin);
    
    foreach($markalar as $m) {
        if (preg_match('/\b' . preg_quote($m, '/') . '\b/i', $metin)) {
            return $m;
        }
    }
    return false;
}

function logoBul($marka) {
    $logolar = [
        'bmw' => 'https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg',
        'mercedes' => 'https://upload.wikimedia.org/wikipedia/commons/9/90/Mercedes-Logo.svg',
        'audi' => 'https://upload.wikimedia.org/wikipedia/commons/9/92/Audi-Logo_2016.svg',
        'volkswagen' => 'https://upload.wikimedia.org/wikipedia/commons/a/a1/Volkswagen_Logo_till_1995.svg',
        'vw' => 'https://upload.wikimedia.org/wikipedia/commons/a/a1/Volkswagen_Logo_till_1995.svg',
        'renault' => 'https://upload.wikimedia.org/wikipedia/commons/b/b7/Renault_2021_Textless.svg',
        'fiat' => 'https://upload.wikimedia.org/wikipedia/commons/1/12/Fiat_Automobiles_logo.svg',
        'ford' => 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Ford_Motor_Company_Logo.svg',
        'toyota' => 'https://upload.wikimedia.org/wikipedia/commons/e/e5/Toyota_emblem.svg',
        'honda' => 'https://upload.wikimedia.org/wikipedia/commons/7/7b/Honda_Logo.svg',
        'peugeot' => 'https://upload.wikimedia.org/wikipedia/commons/f/f7/Peugeot_Logo.svg',
        'opel' => 'https://upload.wikimedia.org/wikipedia/commons/c/c4/Opel_2020_logo.svg',
        'hyundai' => 'https://upload.wikimedia.org/wikipedia/commons/4/44/Hyundai_Motor_Company_logo.svg',
        'kia' => 'https://upload.wikimedia.org/wikipedia/commons/4/47/KIA_logo2.svg',
        'nissan' => 'https://upload.wikimedia.org/wikipedia/commons/8/8c/Nissan_logo.png',
        'skoda' => 'https://upload.wikimedia.org/wikipedia/commons/c/c8/Skoda_Auto_logo_%282023%29.svg',
        'dacia' => 'https://upload.wikimedia.org/wikipedia/commons/0/07/Dacia_Logo_2021.svg',
        'seat' => 'https://upload.wikimedia.org/wikipedia/commons/2/23/SEAT_Logo_from_2017.svg',
        'volvo' => 'https://upload.wikimedia.org/wikipedia/commons/2/29/Volvo-Iron-Mark-Black.svg',
        'chevrolet' => 'https://upload.wikimedia.org/wikipedia/commons/1/1e/Chevrolet-logo.png',
        'mazda' => 'https://upload.wikimedia.org/wikipedia/commons/c/c2/Mazda_Logo.svg',
        'suzuki' => 'https://upload.wikimedia.org/wikipedia/commons/1/12/Suzuki_logo_2.svg',
        'subaru' => 'https://upload.wikimedia.org/wikipedia/commons/4/48/Subaru_logo.svg',
        'porsche' => 'https://upload.wikimedia.org/wikipedia/commons/8/8c/Porsche_logo.svg',
        'jeep' => 'https://upload.wikimedia.org/wikipedia/commons/b/bc/Jeep_logo.svg',
        'tesla' => 'https://upload.wikimedia.org/wikipedia/commons/b/bd/Tesla_Motors.svg'
    ];
    return isset($logolar[$marka]) ? $logolar[$marka] : false;
}
?>

<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title>OtoForum | Topluluk ve Çözümler</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/dist/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <script>
        let theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    </script>

    <style>
        :root { --bg-gradient: radial-gradient(circle at top right, #e2e8f0, #f8fafc, #ffffff); --text-main: #0f172a; --text-muted: #64748b; --glass-bg: rgba(255, 255, 255, 0.85); --glass-border: rgba(0, 0, 0, 0.05); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05); --input-bg: rgba(255, 255, 255, 0.95); --nav-bg: rgba(255, 255, 255, 0.95); --grid-color: rgba(0, 0, 0, 0.03); --footer-bg: rgba(0, 0, 0, 0.03); }
        [data-theme="dark"] { --bg-gradient: radial-gradient(circle at top right, #1e40af, #0f172a, #030712); --text-main: #f8fafc; --text-muted: #94a3b8; --glass-bg: rgba(15, 23, 42, 0.85); --glass-border: rgba(255, 255, 255, 0.08); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); --input-bg: rgba(0, 0, 0, 0.6); --nav-bg: rgba(3, 7, 18, 0.95); --grid-color: rgba(255, 255, 255, 0.02); --footer-bg: rgba(0, 0, 0, 0.2); }
        
        body { font-family: 'Space Grotesk', sans-serif; background: var(--bg-gradient); background-attachment: fixed; color: var(--text-main); position: relative; }
        .tema-gecis-animasyonu { transition: background 0.5s ease, color 0.5s ease; }
        body::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: linear-gradient(var(--grid-color) 1px, transparent 1px), linear-gradient(90deg, var(--grid-color) 1px, transparent 1px); background-size: 40px 40px; z-index: -1; pointer-events: none; }
        
        .glass { background: var(--glass-bg); border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); transition: all 0.3s ease; }
        .nav-glass { background: var(--nav-bg); border-bottom: 1px solid var(--glass-border); }
        .input-box { background: var(--input-bg); border: 1px solid var(--glass-border); }
        
        .popular-neon-border { border-top: 4px solid #eab308; box-shadow: 0 -10px 40px -10px rgba(234, 179, 8, 0.2); }
        .popular-card:hover { transform: translateY(-8px); border-color: rgba(234, 179, 8, 0.4); box-shadow: 0 20px 40px -10px rgba(234, 179, 8, 0.25); }
        
        .topic-row:hover { transform: translateX(5px); border-color: rgba(59, 130, 246, 0.3); background: rgba(59, 130, 246, 0.05); }

        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 8s ease-in-out 4s infinite; }
        
        @keyframes float { 0% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-20px) rotate(5deg); } 100% { transform: translateY(0px) rotate(0deg); } }
        
        /* Dosya yükleme butonu içi gizli input ayarı */
        input[type="file"]::file-selector-button { display: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col selection:bg-blue-500 selection:text-white">

    <nav class="sticky top-0 z-50 nav-glass px-6 py-4 mb-10">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="bg-blue-600 p-2 rounded-xl rotate-3 group-hover:rotate-0 transition-transform duration-300 shadow-lg shadow-blue-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <span class="text-2xl font-black tracking-tighter uppercase italic text-[var(--text-main)]">Oto<span class="text-blue-500">AI</span></span>
            </a>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-bold uppercase tracking-widest">
                <a href="index.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Teşhis</a>
                <a href="gecmis.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Geçmiş</a>
                <a href="forum.php" class="text-blue-500 transition hover:scale-105">Forum</a>
                <a href="garajim.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Garajım</a>
            </div>

            <div class="flex items-center gap-4">
                <button id="theme-toggle" class="w-10 h-10 flex items-center justify-center rounded-xl glass hover:scale-110 hover:bg-blue-500/10 transition-all text-[var(--text-main)]">
                    <div id="theme-icon"></div>
                </button>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-4 pl-4 border-l border-[var(--glass-border)]">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] text-[var(--text-muted)] font-bold uppercase leading-none mb-1">Oturum Açık</p>
                            <p class="text-sm font-black text-[var(--text-main)]"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        </div>
                        <a href="cikis.php" class="flex items-center gap-2 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-xl transition-all duration-300 font-bold text-sm shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-7xl mx-auto px-6 pb-20 relative w-full">
        
        <div class="absolute top-10 left-10 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]">
            <svg class="w-60 h-60" fill="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-16 relative z-10">
            <div>
                <h1 class="text-4xl md:text-6xl font-black tracking-tighter italic uppercase text-[var(--text-main)]">
                    Topluluk <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400 pr-2">Garajı</span>
                </h1>
                <p class="text-[var(--text-muted)] mt-2 text-sm md:text-lg font-light">Binlerce araç sahibi ve onaylı usta burada yardımlaşıyor.</p>
            </div>
            
            <a href="yeni_konu.php" class="w-full md:w-auto bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-[2rem] font-black uppercase tracking-widest text-sm transition-all shadow-xl shadow-blue-600/30 flex items-center justify-center gap-3 hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Detaylı Konu Aç
            </a>
        </div>

        <!-- POPÜLER KONULAR -->
        <div class="mb-20 relative z-10">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-full bg-yellow-500/20 flex items-center justify-center text-yellow-500 animate-pulse">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.091 8.164 16.091 12c0 1.255-.386 2.453-1.091 3.5.705-1.047 1.091-2.245 1.091-3.5 0-3.836-2.091-7-4.105-9 2.486 2 2.986 5 2.986 7 0-1-1-3-3-4 1 2 2 5 2.986 7a8.034 8.034 0 01-1.091 3.5 7.903 7.903 0 011.091-3.5c0 3.836 2.091 7 4.105 9z"></path></svg>
                </div>
                <h2 class="text-xl font-black uppercase tracking-widest text-[var(--text-main)]">Trend <span class="text-yellow-500">Tartışmalar</span></h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach($populerler as $p): ?>
                    <?php 
                        $tespit_edilen_marka = markaTespitEt($p['title']);
                        $logo_url = $tespit_edilen_marka ? logoBul($tespit_edilen_marka) : false;
                    ?>
                    <a href="konu_detay.php?id=<?php echo $p['id']; ?>" class="glass p-8 rounded-[2.5rem] popular-neon-border popular-card relative overflow-hidden group flex flex-col h-full block">
                        
                        <div class="absolute top-6 right-6">
                            <?php if($logo_url): ?>
                                <div class="w-12 h-12 bg-white/95 rounded-xl flex items-center justify-center border border-[var(--glass-border)] shadow-md p-1.5 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <img src="<?php echo $logo_url; ?>" class="w-full h-full object-contain" alt="Marka">
                                </div>
                            <?php else: ?>
                                <div class="text-5xl opacity-10 text-[var(--text-main)] group-hover:scale-110 transition-transform">
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.091 8.164 16.091 12c0 1.255-.386 2.453-1.091 3.5.705-1.047 1.091-2.245 1.091-3.5 0-3.836-2.091-7-4.105-9 2.486 2 2.986 5 2.986 7 0-1-1-3-3-4 1 2 2 5 2.986 7a8.034 8.034 0 01-1.091 3.5 7.903 7.903 0 011.091-3.5c0 3.836 2.091 7 4.105 9z"></path></svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4 mt-2">
                            <span class="text-[10px] bg-yellow-500/10 text-yellow-500 px-4 py-1.5 rounded-full font-black uppercase tracking-widest border border-yellow-500/20">Trend</span>
                        </div>
                        
                        <h4 class="font-bold text-xl text-[var(--text-main)] mt-2 mb-6 line-clamp-3 leading-snug group-hover:text-yellow-500 transition-colors flex-grow pr-10">
                            <?php echo htmlspecialchars($p['title']); ?>
                        </h4>
                        
                        <div class="flex justify-between items-center text-[11px] font-bold text-[var(--text-muted)] uppercase tracking-widest pt-4 border-t border-[var(--glass-border)]">
                            <span class="flex items-center gap-1.5"><svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path></svg> <?php echo htmlspecialchars($p['username']); ?></span>
                            <span class="flex items-center gap-1.5"><svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7z"></path></svg> <?php echo $p['comment_count']; ?> Yanıt</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TÜM KONULAR LİSTESİ VE HIZLI RESİM EKLEME -->
        <div class="relative z-10 glass p-4 md:p-8 rounded-[3rem] shadow-2xl">
            <h2 class="text-sm font-black uppercase tracking-[0.3em] text-blue-500 mb-6 pl-4">Tüm Konular</h2>
            
            <!-- YENİ: Hızlı Konu ve Resim Ekleme Formu -->
            <?php if(isset($_SESSION['user_id'])): ?>
            <form action="" method="POST" enctype="multipart/form-data" class="mb-8 input-box p-3 md:p-4 rounded-[2rem] flex flex-col md:flex-row gap-4 items-center border border-blue-500/30 hover:border-blue-500/60 transition-colors">
                <div class="w-full relative flex items-center pl-4">
                    <svg class="w-5 h-5 text-[var(--text-muted)] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <input type="text" name="title" required placeholder="Hızlıca bir sorun, soru veya araç resmi paylaş..." class="w-full bg-transparent border-none outline-none text-[var(--text-main)] py-2 font-medium placeholder:text-[var(--text-muted)] text-sm md:text-base">
                </div>
                <div class="flex items-center gap-3 shrink-0 w-full md:w-auto justify-end px-2 pb-2 md:px-0 md:pb-0">
                    <label class="cursor-pointer bg-[var(--glass-border)] hover:bg-blue-500/10 text-[var(--text-main)] hover:text-blue-500 px-5 py-3 rounded-2xl transition-all flex items-center gap-2 font-bold text-xs uppercase border border-transparent hover:border-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Resim
                        <input type="file" name="post_image" accept="image/*" class="hidden" onchange="this.parentElement.style.color='#3b82f6'; this.parentElement.style.borderColor='rgba(59, 130, 246, 0.4)';">
                    </label>
                    <button type="submit" name="hizli_konu_ekle" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-blue-500/30 flex items-center gap-2">
                        Gönder <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <div class="flex flex-col gap-4">
                <?php foreach($konular as $konu): ?>
                    <?php 
                        $tespit_edilen_marka = markaTespitEt($konu['title']);
                        $logo_url = $tespit_edilen_marka ? logoBul($tespit_edilen_marka) : false;
                    ?>
                    <!-- YENİ: Yapı bozulmadan iç içe link (a) hatalarını önlemek için div'e çevrildi[cite: 3] -->
                    <div class="input-box p-4 md:p-6 rounded-[2rem] flex flex-col md:flex-row justify-between items-center gap-6 topic-row group transition-all">
                        
                        <div class="flex items-center gap-5 w-full cursor-pointer" onclick="window.location.href='konu_detay.php?id=<?php echo $konu['id']; ?>'">
                            
                            <!-- Eğer konuda resim varsa onu göster, yoksa marka logosu, yoksa standart ikon[cite: 3] -->
                            <?php if(!empty($konu['image_path'])): ?>
                                <div class="w-14 h-14 bg-white/95 rounded-2xl flex items-center justify-center border border-[var(--glass-border)] shrink-0 overflow-hidden shadow-sm group-hover:shadow-md transition-shadow">
                                    <img src="<?php echo htmlspecialchars($konu['image_path']); ?>" class="w-full h-full object-cover" alt="Kullanıcı Görseli">
                                </div>
                            <?php elseif($logo_url): ?>
                                <div class="w-14 h-14 bg-white/95 rounded-2xl flex items-center justify-center border border-[var(--glass-border)] shrink-0 p-2 shadow-sm group-hover:shadow-md transition-shadow">
                                    <img src="<?php echo $logo_url; ?>" class="w-full h-full object-contain" alt="Marka">
                                </div>
                            <?php else: ?>
                                <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-500 shrink-0 border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                            <?php endif; ?>
                            
                            <div class="w-full">
                                <h3 class="text-lg md:text-xl font-bold text-[var(--text-main)] group-hover:text-blue-500 transition-colors mb-2 line-clamp-1 pr-4">
                                    <?php echo htmlspecialchars($konu['title']); ?>
                                </h3>
                                <div class="flex flex-wrap items-center gap-2 md:gap-4 text-[10px] md:text-[11px] font-bold text-[var(--text-muted)] uppercase tracking-widest">
                                    <span class="text-blue-500 flex items-center gap-1.5"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg> <?php echo htmlspecialchars($konu['username']); ?></span>
                                    <span class="hidden md:inline opacity-30">|</span>
                                    <span class="flex items-center gap-1.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> <?php echo date('d.m.Y', strtotime($konu['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                            <!-- YENİ: Sil Butonu (Sadece konuyu açan görebilir)[cite: 3] -->
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $konu['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('Bu konuyu ve bağlı tüm yorumları silmek istediğine emin misin?');" class="m-0 p-0">
                                    <input type="hidden" name="delete_post_id" value="<?php echo $konu['id']; ?>">
                                    <button type="submit" class="h-12 w-12 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-xl font-black text-sm transition-all flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <a href="konu_detay.php?id=<?php echo $konu['id']; ?>" class="h-12 px-8 bg-[var(--glass-border)] group-hover:bg-blue-500 group-hover:text-white text-[var(--text-main)] rounded-xl font-black text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-2 flex-grow md:flex-grow-0">
                                İncele 
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if(count($konular) == 0): ?>
                <div class="text-center p-12 text-[var(--text-muted)]">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    <p class="font-bold text-lg">Henüz hiç konu açılmamış.</p>
                    <p class="text-sm">İlk konuyu sen başlat!</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <footer class="border-t border-[var(--glass-border)] py-12 mt-auto" style="background: var(--footer-bg);">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-sm font-bold text-[var(--text-muted)] uppercase tracking-widest flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                &copy; 2026 OtoAI - Giresun Üniversitesi Geliştirme Projesi
            </div>
        </div>
    </footer>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlEl = document.documentElement;

        function updateIcon(theme) {
            if (theme === 'dark') {
                themeIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>';
            } else {
                themeIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';
            }
        }
        updateIcon(htmlEl.getAttribute('data-theme'));

        themeToggleBtn.addEventListener('click', () => {
            document.body.classList.add('tema-gecis-animasyonu');
            const newTheme = htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            themeIcon.style.opacity = 0;
            setTimeout(() => {
                htmlEl.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
                themeIcon.style.opacity = 1;
            }, 150);
            setTimeout(() => document.body.classList.remove('tema-gecis-animasyonu'), 600);
        });
    </script>
</body>
</html>