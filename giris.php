<<?php
include 'baglan.php';
session_start(); 

$hata_mesaji = "";

if ($_POST) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sorgu = $db->prepare("SELECT * FROM users WHERE username = ?");
    $sorgu->execute([$user]);
    $kullanici = $sorgu->fetch();

    if ($kullanici && password_verify($pass, $kullanici['password'])) {
        // --- BAŞARILI GİRİŞ: BİLGİLERİ CEBE ATALIM ---
        $_SESSION['user_id'] = $kullanici['id']; 
        $_SESSION['username'] = $kullanici['username'];
        
        // KRİTİK SATIR: Veritabanındaki rolü oturuma kaydediyoruz
        $_SESSION['role'] = $kullanici['role']; 
        $_SESSION['profile_pic'] = $kullanici['profile_pic']; // YENİ: Resim yolunu kaydettik
        
        header("Location: index.php"); // Ana sayfaya uçur
        exit(); // Kodun devam etmesini durdur
    } else {
        $hata_mesaji = "<div class='p-4 mb-6 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-500 font-bold text-center text-sm'>
                            <i class='fa-solid fa-triangle-exclamation mb-1 text-lg'></i><br>Hatalı kullanıcı adı veya şifre!
                        </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | OtoAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/dist/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-gradient: radial-gradient(circle at top right, #e2e8f0, #f8fafc, #ffffff);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(0, 0, 0, 0.05);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
            --input-bg: rgba(255, 255, 255, 0.9);
            --grid-color: rgba(0, 0, 0, 0.03);
            --glow-color: rgba(59, 130, 246, 0.15);
        }

        [data-theme="dark"] {
            --bg-gradient: radial-gradient(circle at top right, #1e40af, #0f172a, #030712);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.02);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            --input-bg: rgba(0, 0, 0, 0.4);
            --grid-color: rgba(255, 255, 255, 0.02);
            --glow-color: rgba(59, 130, 246, 0.25);
        }
        
        body { 
            font-family: 'Space Grotesk', sans-serif; 
            background: var(--bg-gradient);
            background-attachment: fixed;
            color: var(--text-main);
            transition: background 0.5s ease, color 0.5s ease;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                linear-gradient(var(--grid-color) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
            pointer-events: none;
        }

        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
        }

        .input-box {
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .input-box:focus-within {
            box-shadow: 0 0 20px var(--glow-color);
            border-color: rgba(59, 130, 246, 0.5);
        }

        .animate-float {
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 selection:bg-blue-500 selection:text-white">

    <!-- Tema Butonu -->
    <button id="theme-toggle" class="absolute top-6 right-6 w-12 h-12 flex items-center justify-center rounded-2xl glass hover:scale-110 hover:bg-blue-500/10 transition-all text-[var(--text-main)] z-50">
        <div id="theme-icon"></div>
    </button>

    <!-- Arka Plan İkonları -->
    <div class="absolute top-20 left-10 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]">
        <svg class="w-60 h-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
    </div>
    <div class="absolute bottom-20 right-10 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]" style="animation-delay: -4s;">
        <svg class="w-48 h-48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    </div>

    <!-- Giriş Formu Kartı -->
    <div class="glass p-8 md:p-12 rounded-[3rem] w-full max-w-md relative z-10 shadow-2xl">
        
        <!-- Logo -->
        <div class="text-center mb-10">
            <a href="index.php" class="inline-flex items-center justify-center gap-3 group mb-2">
                <div class="bg-blue-600 p-3 rounded-2xl rotate-3 group-hover:rotate-0 transition-all duration-300 shadow-lg shadow-blue-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
            </a>
            <h1 class="text-3xl font-black tracking-tighter uppercase text-[var(--text-main)] mt-4">Tekrar <br><span class="text-blue-500">Hoş Geldin</span></h1>
            <p class="text-[var(--text-muted)] text-sm mt-2 font-light">Sisteme giriş yap ve analizlere devam et.</p>
        </div>

        <?php echo $hata_mesaji; ?>

        <form method="POST" class="flex flex-col gap-5">
            <!-- Kullanıcı Adı -->
            <div class="input-box flex items-center px-5 py-4 rounded-2xl">
                <svg class="w-5 h-5 text-[var(--text-muted)] mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <input type="text" name="username" placeholder="Kullanıcı Adı" required 
                       class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)]">
            </div>

            <!-- Şifre -->
            <div class="input-box flex items-center px-5 py-4 rounded-2xl">
                <svg class="w-5 h-5 text-[var(--text-muted)] mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <input type="password" name="password" placeholder="Şifre" required 
                       class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)]">
            </div>
            
            <div class="text-right -mt-2">
                 <a href="sifre_yenile.php" class="text-blue-500 hover:text-blue-400 text-xs font-bold uppercase tracking-widest transition-all">Şifremi Unuttum?</a>
            </div>

            <!-- Giriş Butonu -->
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-sm transition-all hover:scale-105 active:scale-95 shadow-xl shadow-blue-600/40 mt-2 flex items-center justify-center gap-2">
                Garaja Gir <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </button>
        </form>

        <div class="text-center mt-8 pt-6 border-t border-[var(--glass-border)]">
            <p class="text-[var(--text-muted)] text-sm">
                Henüz hesabın yok mu? <br>
                <a href="kayit.php" class="text-blue-500 font-bold hover:text-blue-400 hover:underline transition-all uppercase tracking-wider text-xs mt-2 inline-block">Hemen Kayıt Ol</a>
            </p>
        </div>
    </div>

    <!-- Tema JS -->
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlEl = document.documentElement;

        function getPreferredTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                return savedTheme;
            }
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        function applyTheme(theme) {
            htmlEl.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
            if (theme === 'dark') {
                themeIcon.innerHTML = '<svg class="w-5 h-5 text-yellow-400 transition-transform transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>';
            } else {
                themeIcon.innerHTML = '<svg class="w-5 h-5 text-slate-700 transition-transform transform rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';
            }
        }

        applyTheme(getPreferredTheme());

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = htmlEl.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            themeIcon.style.opacity = 0;
            setTimeout(() => {
                applyTheme(newTheme);
                themeIcon.style.opacity = 1;
            }, 150);
        });
    </script>
</body>
</html>