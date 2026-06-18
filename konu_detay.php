<?php 
session_start();
include 'baglan.php';

// URL'den ID'yi alıyoruz
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Konu Detaylarını, Yazarı ve Yazarın Rolünü Çekiyoruz
$sorgu = $db->prepare("SELECT forum_posts.*, users.username, users.role 
                       FROM forum_posts 
                       JOIN users ON forum_posts.user_id = users.id 
                       WHERE forum_posts.id = ?");
$sorgu->execute([$post_id]);
$konu = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$konu) { 
    die("<div style='background:#1e293b; color:white; padding:50px; text-align:center; font-family:sans-serif; font-size:24px;'>Konu bulunamadı veya silinmiş.</div>"); 
}

// 2. Yorum Yapma İşlemi 
if ($_POST && isset($_SESSION['user_id'])) {
    $yorum = trim($_POST['comment']);
    $uid = $_SESSION['user_id'];
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    $image_path = null;

    // Resim Yükleme Kontrolü
    if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
        $izin_verilenler = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $uzanti = strtolower(pathinfo($_FILES['comment_image']['name'], PATHINFO_EXTENSION));
        if (in_array($uzanti, $izin_verilenler)) {
            $yeni_ad = uniqid('comm_') . '.' . $uzanti;
            $hedef = 'uploads/' . $yeni_ad;
            if (move_uploaded_file($_FILES['comment_image']['tmp_name'], $hedef)) {
                $image_path = $hedef;
            }
        }
    }
    
    if(!empty($yorum) || $image_path) {
        // Tablo yapına image_path ve parent_id sütunlarını eklediğini varsayıyoruz
        $ekle = $db->prepare("INSERT INTO forum_comments (post_id, user_id, comment, image_path, parent_id) VALUES (?, ?, ?, ?, ?)");
        if($ekle->execute([$post_id, $uid, $yorum, $image_path, $parent_id])) {
            header("Location: konu_detay.php?id=" . $post_id); 
            exit();
        }
    }
}

// 3. Yorumları, Yazarların Rollererini ve Beğeni Sayılarını Çekiyoruz
$yorum_sorgu = $db->prepare("SELECT forum_comments.*, users.username, users.role, 
    (SELECT COUNT(*) FROM comment_likes WHERE comment_id = forum_comments.id) as like_count 
    FROM forum_comments 
    JOIN users ON forum_comments.user_id = users.id 
    WHERE post_id = ? ORDER BY parent_id ASC, created_at ASC");
$yorum_sorgu->execute([$post_id]);
$yorumlar = $yorum_sorgu->fetchAll(PDO::FETCH_ASSOC);

// Yorumları hiyerarşik göstermek için gruplayalım
$ana_yorumlar = array_filter($yorumlar, function($y) { return $y['parent_id'] == 0; });
$yanitlar = array_filter($yorumlar, function($y) { return $y['parent_id'] != 0; });
?>

<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title><?php echo htmlspecialchars($konu['title']); ?> | OtoAI Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/dist/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <script>
        let theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    </script>

    <style>
        :root {
            --bg-gradient: radial-gradient(circle at top right, #e2e8f0, #f8fafc, #ffffff);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.05);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
            --input-bg: rgba(255, 255, 255, 0.95);
            --nav-bg: rgba(255, 255, 255, 0.95);
            --scrollbar-track: #f1f5f9;
            --grid-color: rgba(0, 0, 0, 0.03);
            --footer-bg: rgba(0, 0, 0, 0.03);
        }

        [data-theme="dark"] {
            --bg-gradient: radial-gradient(circle at top right, #1e40af, #0f172a, #030712);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(15, 23, 42, 0.85);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            --input-bg: rgba(0, 0, 0, 0.6);
            --nav-bg: rgba(3, 7, 18, 0.95);
            --scrollbar-track: #030712;
            --grid-color: rgba(255, 255, 255, 0.02);
            --footer-bg: rgba(0, 0, 0, 0.2);
        }
        
        body { font-family: 'Space Grotesk', sans-serif; background: var(--bg-gradient); background-attachment: fixed; color: var(--text-main); position: relative; }
        .tema-gecis-animasyonu { transition: background 0.5s ease, color 0.5s ease; }
        body::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: linear-gradient(var(--grid-color) 1px, transparent 1px), linear-gradient(90deg, var(--grid-color) 1px, transparent 1px); background-size: 40px 40px; z-index: -1; pointer-events: none; }
        
        .glass { background: var(--glass-bg); border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); transition: all 0.3s ease; }
        .nav-glass { background: var(--nav-bg); border-bottom: 1px solid var(--glass-border); }
        .input-box { background: var(--input-bg); border: 1px solid var(--glass-border); transition: all 0.3s ease; }
        .input-box:focus-within { box-shadow: 0 0 20px rgba(59, 130, 246, 0.15); border-color: rgba(59, 130, 246, 0.5); }

        .animate-float { animation: float 8s ease-in-out infinite; }
        
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        /* Yorum zinciri sol çizgi */
        .thread-line { position: absolute; left: 2rem; top: 0; bottom: 0; width: 2px; background: var(--glass-border); z-index: 0; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--scrollbar-track); }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }

        input[type="file"]::file-selector-button { display: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col selection:bg-blue-500 selection:text-white">

    <!-- NAVBAR -->
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
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="admin.php" class="text-red-500 hover:text-red-600 transition border-l border-[var(--glass-border)] pl-8 hover:scale-105">Panel</a>
                <?php endif; ?>
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
                        <a href="hesap.php" class="hover:scale-110 transition-transform shrink-0 relative group">
                             <img src="uploads/default.png" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=3b82f6&color=fff'" class="w-10 h-10 rounded-full border-2 border-blue-600 object-cover shadow-lg">
                        </a>
                        <a href="cikis.php" class="flex items-center gap-2 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-xl transition-all duration-300 font-bold text-sm shadow-sm group">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="hidden sm:block">Çıkış</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="hidden sm:flex items-center gap-4 pl-4 border-l border-[var(--glass-border)]">
                        <a href="giris.php" class="text-sm font-bold uppercase text-[var(--text-muted)] hover:text-blue-500 transition">Giriş</a>
                        <a href="kayit.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-600/30 hover:scale-105">Kayıt Ol</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ANA İÇERİK -->
    <main class="flex-grow max-w-5xl mx-auto px-6 pb-20 w-full relative">
        
        <!-- Arka Plan İkonu -->
        <div class="absolute top-0 right-0 opacity-[0.02] animate-float pointer-events-none text-[var(--text-main)]">
            <svg class="w-80 h-80" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"></path></svg>
        </div>

        <a href="forum.php" class="inline-flex items-center gap-2 text-[var(--text-muted)] hover:text-blue-500 font-bold uppercase tracking-widest text-xs transition-all mb-8 group bg-[var(--glass-border)] px-4 py-2 rounded-lg">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Foruma Dön
        </a>

        <!-- ANA KONU KARTI -->
        <div class="glass p-8 md:p-12 rounded-[3rem] shadow-2xl mb-12 relative z-10 border-t-4 border-t-blue-500">
            <div class="flex justify-between items-start gap-4 mb-8 flex-wrap">
                <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tighter text-[var(--text-main)] leading-tight flex-1">
                    <?php echo htmlspecialchars($konu['title']); ?>
                </h1>
                <span class="bg-blue-500/10 text-blue-500 border border-blue-500/20 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest shrink-0">
                    Konu Başlığı
                </span>
            </div>
            
            <!-- Konu İçeriği -->
            <div class="input-box p-6 md:p-8 rounded-[2rem] text-[var(--text-main)] leading-relaxed text-base md:text-lg font-light mb-10 shadow-inner">
                <?php echo nl2br(htmlspecialchars($konu['content'])); ?>
                <?php if(!empty($konu['image_path'])): ?>
                    <div class="mt-6 rounded-2xl overflow-hidden border border-[var(--glass-border)]">
                        <img src="<?php echo htmlspecialchars($konu['image_path']); ?>" class="w-full h-auto max-h-[500px] object-cover" alt="Konu Resmi">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Yazar Bilgileri -->
            <div class="flex items-center gap-4 pt-6 border-t border-[var(--glass-border)]">
                <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/30">
                    <?php echo strtoupper(substr($konu['username'], 0, 1)); ?>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <p class="text-[var(--text-main)] font-black text-lg"><?php echo htmlspecialchars($konu['username']); ?></p>
                        
                        <!-- ROZETLER[cite: 10] -->
                        <?php if($konu['role'] == 'usta'): ?>
                            <span class="bg-purple-500/10 text-purple-500 border border-purple-500/30 text-[10px] px-2.5 py-1 rounded-lg font-black uppercase tracking-widest flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Usta
                            </span>
                        <?php elseif($konu['role'] == 'admin'): ?>
                            <span class="bg-red-500/10 text-red-500 border border-red-500/30 text-[10px] px-2.5 py-1 rounded-lg font-black uppercase tracking-widest">
                                Admin
                            </span>
                        <?php else: ?>
                            <span class="bg-slate-500/10 text-slate-500 border border-slate-500/30 text-[10px] px-2.5 py-1 rounded-lg font-black uppercase tracking-widest">
                                Üye
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest flex items-center gap-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <?php echo date('d.m.Y H:i', strtotime($konu['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- YORUMLAR (TOPLULUK CEVAPLARI) BAŞLIĞI -->
        <div class="flex items-center gap-3 mb-8 relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-500 border border-blue-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
            <h2 class="text-2xl font-black uppercase tracking-widest text-[var(--text-main)]">
                Topluluk <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400">Cevapları</span> (<?php echo count($yorumlar); ?>)
            </h2>
        </div>
        
        <!-- YORUMLAR LİSTESİ -->
        <div class="relative mb-16 z-10">
            <!-- Zincir Çizgisi -->
            <?php if(count($yorumlar) > 0): ?>
                <div class="thread-line hidden md:block"></div>
            <?php endif; ?>

            <div class="space-y-6">
                <?php foreach($ana_yorumlar as $y): ?>
                    <div class="glass p-6 md:p-8 rounded-[2rem] md:ml-12 relative transition-transform hover:-translate-y-1">
                        
                        <!-- Zincir Noktası -->
                        <div class="hidden md:flex absolute -left-[3rem] top-10 w-4 h-4 rounded-full bg-blue-500 ring-4 ring-[var(--bg-gradient)] shadow-lg shadow-blue-500/30"></div>
                        
                        <!-- Yorum Üst Bilgi -->
                        <div class="flex flex-wrap items-center gap-3 mb-4 pb-4 border-b border-[var(--glass-border)]">
                            <div class="w-8 h-8 rounded-full bg-[var(--glass-border)] flex items-center justify-center font-black text-sm text-[var(--text-main)]">
                                <?php echo strtoupper(substr($y['username'], 0, 1)); ?>
                            </div>
                            <span class="font-black text-[var(--text-main)]"><?php echo htmlspecialchars($y['username']); ?></span>
                            
                            <!-- ROZETLER -->
                            <?php if($y['role'] == 'usta'): ?>
                                <span class="bg-purple-500/10 text-purple-500 border border-purple-500/30 text-[9px] px-2 py-0.5 rounded-lg font-black uppercase tracking-widest flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg> Usta
                                </span>
                            <?php elseif($y['role'] == 'admin'): ?>
                                <span class="bg-red-500/10 text-red-500 border border-red-500/30 text-[9px] px-2 py-0.5 rounded-lg font-black uppercase tracking-widest">
                                    Admin
                                </span>
                            <?php endif; ?>

                            <span class="text-[10px] font-bold text-[var(--text-muted)] ml-auto uppercase tracking-widest">
                                <?php echo date('d.m.Y H:i', strtotime($y['created_at'])); ?>
                            </span>
                        </div>
                        
                        <!-- Yorum İçeriği -->
                        <p class="text-[var(--text-main)] font-light leading-relaxed mb-4">
                            <?php echo nl2br(htmlspecialchars($y['comment'])); ?>
                        </p>
                        
                        <?php if(!empty($y['image_path'])): ?>
                            <div class="mb-6 rounded-xl overflow-hidden border border-[var(--glass-border)] max-w-sm">
                                <img src="<?php echo htmlspecialchars($y['image_path']); ?>" class="w-full h-auto cursor-pointer hover:opacity-90 transition-opacity" onclick="window.open(this.src)">
                            </div>
                        <?php endif; ?>
                        
                        <!-- BUTONLAR -->
                        <div class="flex items-center gap-3">
                            <a href="islem_like.php?id=<?php echo $y['id']; ?>&post_id=<?php echo $post_id; ?>" 
                               class="text-xs bg-[var(--glass-border)] hover:bg-blue-500 text-[var(--text-main)] hover:text-white px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 border border-[var(--glass-border)]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.708C19.712 10 20.5 10.788 20.5 11.75v1.25c0 .337-.094.652-.258.918l-3.333 5.334A2.5 2.5 0 0114.792 20.5H7.5V10h1.833l3.542-5.903a1.5 1.5 0 012.35 1.408L14.333 10H14z"></path></svg> 
                                Faydalı (<?php echo $y['like_count']; ?>)
                            </a>
                            <button onclick="yanitla(<?php echo $y['id']; ?>, '<?php echo $y['username']; ?>')" 
                                    class="text-xs bg-[var(--glass-border)] hover:bg-green-500/10 text-[var(--text-main)] hover:text-green-500 px-5 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 border border-transparent hover:border-green-500/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg> Yanıtla
                            </button>
                        </div>
                    </div>

                    <!-- YANITLAR DÖNGÜSÜ -->
                    <?php 
                    $bu_yanitlar = array_filter($yanitlar, function($yanit) use ($y) { return $yanit['parent_id'] == $y['id']; });
                    foreach($bu_yanitlar as $yanit): 
                    ?>
                        <div class="glass p-5 md:p-6 rounded-[2rem] ml-16 md:ml-24 relative border-l-4 border-l-blue-500/30">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-black text-xs"><?php echo htmlspecialchars($yanit['username']); ?></span>
                                <span class="text-[8px] uppercase font-bold text-[var(--text-muted)]"><?php echo date('d.m.Y H:i', strtotime($yanit['created_at'])); ?></span>
                            </div>
                            <p class="text-sm font-light text-[var(--text-main)]"><?php echo nl2br(htmlspecialchars($yanit['comment'])); ?></p>
                            <?php if(!empty($yanit['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($yanit['image_path']); ?>" class="rounded-lg max-w-[150px] mt-2 border border-[var(--glass-border)]">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                <?php endforeach; ?>

                <?php if(count($ana_yorumlar) == 0): ?>
                    <div class="glass p-10 rounded-[2rem] text-center border-dashed border-2 border-[var(--glass-border)]">
                        <svg class="w-12 h-12 mx-auto mb-4 text-[var(--text-muted)] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <p class="text-[var(--text-main)] font-bold">Bu konuya henüz cevap yazılmamış.</p>
                        <p class="text-[var(--text-muted)] text-sm mt-1">İlk cevabı sen yazarak topluluğa yardımcı olabilirsin.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- YORUM YAPMA FORMU -->
        <?php if(isset($_SESSION['user_id'])): ?>
            <div id="yorumKutusu" class="glass p-8 md:p-12 rounded-[3rem] shadow-2xl relative z-10 border-t-4 border-t-green-500">
                
                <!-- Yanıt Bilgisi (JavaScript ile açılır) -->
                <div id="replyInfo" class="hidden mb-6 bg-blue-500/10 p-4 rounded-2xl flex items-center justify-between border border-blue-500/20">
                    <span class="text-xs font-bold text-blue-500 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        <span id="replyUser"></span> kullanıcısına yanıt veriyorsun...
                    </span>
                    <button onclick="yanitVazgec()" class="text-red-500 text-xs font-black uppercase tracking-widest hover:underline">Vazgeç</button>
                </div>

                <h3 class="text-2xl font-black mb-6 uppercase tracking-widest text-[var(--text-main)] flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Fikrini Paylaş
                </h3>
                
                <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                    <input type="hidden" name="parent_id" id="parent_id" value="0">
                    <div class="input-box p-2 rounded-[2rem]">
                        <textarea name="comment" rows="5" required placeholder="Konu sahibine yardımcı olacak tecrübeni veya çözümünü buraya yaz..."
                                  class="w-full bg-transparent p-4 focus:outline-none text-[var(--text-main)] placeholder-[var(--text-muted)] resize-y min-h-[120px]"></textarea>
                    </div>
                    
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <label class="cursor-pointer bg-[var(--glass-border)] hover:text-blue-500 px-8 py-4 rounded-2xl transition-all flex items-center gap-2 font-bold text-xs uppercase border border-transparent">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> 
                            Resim Ekle
                            <input type="file" name="comment_image" accept="image/*" class="hidden" onchange="this.parentElement.style.color='#3b82f6';">
                        </label>

                        <button type="submit" class="w-full md:w-auto bg-green-600 hover:bg-green-500 text-white px-10 py-4 rounded-2xl font-black transition-all uppercase tracking-widest text-sm shadow-xl shadow-green-600/30 flex items-center justify-center gap-2 hover:scale-105 active:scale-95">
                            Cevabı Gönder <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- GİRİŞ YAPMAMIŞ KULLANICILAR İÇİN UYARI -->
            <div class="glass p-10 rounded-[3rem] text-center relative z-10 overflow-hidden group">
                <div class="absolute inset-0 bg-yellow-500/5 group-hover:bg-yellow-500/10 transition-colors"></div>
                <svg class="w-12 h-12 mx-auto mb-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <h3 class="text-xl font-black text-[var(--text-main)] mb-2">Cevap Yazmak İçin Giriş Yapmalısın</h3>
                <p class="text-[var(--text-muted)] mb-6">Topluluğa katılmak ve tecrübelerini paylaşmak için hemen garaja gir.</p>
                <a href="giris.php" class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-400 text-[var(--text-main)] px-8 py-3.5 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-yellow-500/30 hover:scale-105">
                    Giriş Yap <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER -->
    <footer class="border-t border-[var(--glass-border)] py-12 mt-auto" style="background: var(--footer-bg);">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-sm font-bold text-[var(--text-muted)] uppercase tracking-widest flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                &copy; 2026 OtoAI - Giresun Üniversitesi Geliştirme Projesi
            </div>
            <div class="flex gap-6 text-[var(--text-muted)]">
                <a href="#" class="hover:text-blue-500 hover:scale-125 transition-all"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg></a>
                <a href="#" class="hover:text-blue-500 hover:scale-125 transition-all"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.761 0 5-2.239 5-5v-14c0-2.761-2.239-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg></a>
            </div>
        </div>
    </footer>

    <script>
        // Yanıtla Butonu Fonksiyonu
        function yanitla(id, user) {
            document.getElementById('parent_id').value = id;
            document.getElementById('replyUser').innerText = user;
            document.getElementById('replyInfo').classList.remove('hidden');
            document.getElementById('yorumKutusu').scrollIntoView({ behavior: 'smooth' });
        }

        function yanitVazgec() {
            document.getElementById('parent_id').value = '0';
            document.getElementById('replyInfo').classList.add('hidden');
        }

        // TEMA YÖNETİM JS
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlEl = document.documentElement;

        function updateIcon(theme) {
            if (theme === 'dark') {
                themeIcon.innerHTML = '<svg class="w-5 h-5 text-yellow-400 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>';
            } else {
                themeIcon.innerHTML = '<svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';
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