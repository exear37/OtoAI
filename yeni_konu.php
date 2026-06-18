<?php 
session_start();
include 'baglan.php';

// Giriş yapmamışsa forumda konu açamaz, kapıdan döndürelim
if(!isset($_SESSION['user_id'])) { 
    header("Location: giris.php"); 
    exit(); 
}

if ($_POST) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $uid = $_SESSION['user_id'];
    $image_path = null;

    // RESİM YÜKLEME İŞLEMİ
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $izin_verilenler = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $uzanti = strtolower(pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($uzanti, $izin_verilenler)) {
            $yeni_ad = uniqid('post_') . '.' . $uzanti;
            $hedef = 'uploads/' . $yeni_ad;
            
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], $hedef)) {
                $image_path = $hedef;
            }
        }
    }

    if (!empty($title) && !empty($content)) {
        // SQL Sorgusuna image_path eklendi
        $ekle = $db->prepare("INSERT INTO forum_posts (user_id, title, content, image_path) VALUES (?, ?, ?, ?)");
        if($ekle->execute([$uid, $title, $content, $image_path])) {
            header("Location: forum.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title>Yeni Konu Aç | OtoAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/dist/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <!-- TEMA TİTREMESİNİ ÖNLEYEN KOD -->
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
        .animate-float-delayed { animation: float 8s ease-in-out 4s infinite; }
        
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

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
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ANA İÇERİK -->
    <main class="flex-grow max-w-4xl mx-auto px-6 pb-20 relative w-full flex items-center justify-center">
        
        <!-- Arka Plan Yüzen İkonlar -->
        <div class="absolute top-0 left-0 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]">
            <svg class="w-60 h-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
        <div class="absolute bottom-20 right-0 opacity-[0.03] animate-float-delayed pointer-events-none text-[var(--text-main)]">
            <svg class="w-48 h-48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        </div>

        <div class="glass w-full p-8 md:p-14 rounded-[3rem] shadow-2xl relative z-10 mt-10">
            
            <div class="mb-10 text-center">
                <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center border border-blue-500/20 mx-auto mb-6">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                </div>
                <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tighter italic text-[var(--text-main)]">
                    Yeni <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400">Tartışma</span> Başlat
                </h1>
                <p class="text-[var(--text-muted)] mt-3 text-sm font-light max-w-lg mx-auto">Sorununu veya tecrübeni toplulukla paylaş. Diğer sürücülerden ve ustalardan saniyeler içinde yanıt al.</p>
            </div>

            <!-- multipart/form-data EKLENDİ -->
            <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                <!-- Başlık Kutusu -->
                <div class="input-box flex items-center px-6 py-4 rounded-[1.5rem]">
                    <svg class="w-5 h-5 text-[var(--text-muted)] mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                    <input type="text" name="title" required placeholder="Konu Başlığı (Örn: Rölantide titreme ve güç kaybı)" 
                           class="bg-transparent w-full focus:outline-none text-base md:text-lg text-[var(--text-main)] font-bold placeholder-[var(--text-muted)]">
                </div>

                <!-- İçerik Kutusu -->
                <div class="input-box flex items-start px-6 py-5 rounded-[1.5rem]">
                    <svg class="w-5 h-5 text-[var(--text-muted)] mr-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    <textarea name="content" rows="6" required placeholder="Aracının modelini, yaşadığın sorunu veya tecrübeni buraya detaylıca yaz..."
                              class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)] resize-y min-h-[120px]"></textarea>
                </div>

                <!-- RESİM EKLEME ALANI -->
                <div class="flex items-center gap-4">
                    <label class="w-full cursor-pointer bg-[var(--glass-border)] hover:bg-blue-500/10 text-[var(--text-main)] hover:text-blue-500 py-4 px-6 rounded-2xl transition-all flex items-center justify-center gap-3 font-bold text-xs uppercase border border-dashed border-[var(--glass-border)] hover:border-blue-500/40">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Araç / Arıza Resmi Yükle
                        <input type="file" name="post_image" accept="image/*" class="hidden" onchange="this.parentElement.style.color='#3b82f6'; this.parentElement.style.borderColor='rgba(59, 130, 246, 0.4)';">
                    </label>
                </div>

                <!-- Butonlar -->
                <div class="flex flex-col sm:flex-row gap-4 mt-2">
                    <a href="forum.php" class="w-full sm:w-1/3 bg-[var(--glass-border)] hover:bg-[var(--input-bg)] text-[var(--text-main)] py-4 rounded-[1.5rem] font-bold transition-all uppercase tracking-widest text-xs border border-[var(--glass-border)] flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> İptal
                    </a>
                    <button type="submit" class="w-full sm:w-2/3 bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-[1.5rem] font-black transition-all uppercase tracking-widest text-xs shadow-xl shadow-blue-600/30 flex items-center justify-center gap-2">
                        Konuyu Yayınla <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </div>
            </form>
            
        </div>
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