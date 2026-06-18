<?php
include 'baglan.php';

$mesaj = "";

if ($_POST) {
    $user = $_POST['username'];
    $mail = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $ekle = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($ekle->execute([$user, $mail, $pass])) {
        $mesaj = "<div class='p-4 mb-6 rounded-2xl bg-green-500/10 border border-green-500/30 text-green-500 font-bold text-center flex flex-col gap-2'>
                    <span><svg class='w-8 h-8 mx-auto mb-1' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg><br>Kayıt Başarılı!</span>
                    <a href='giris.php' class='bg-green-500 text-white px-4 py-2 rounded-xl text-sm hover:bg-green-600 transition'>Hemen Giriş Yap</a>
                  </div>";
    } else {
        $mesaj = "<div class='p-4 mb-6 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-500 font-bold text-center'>
                    <svg class='w-5 h-5 inline mr-2' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path></svg> Bir hata oluştu, tekrar deneyin.
                  </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol | OtoAI</title>
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
        <svg class="w-60 h-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
    </div>
    <div class="absolute bottom-20 right-10 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]" style="animation-delay: -4s;">
        <svg class="w-48 h-48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
    </div>

    <!-- Kayıt Formu Kartı -->
    <div class="glass p-8 md:p-12 rounded-[3rem] w-full max-w-md relative z-10 shadow-2xl">
        
        <!-- Logo -->
        <div class="text-center mb-10">
            <a href="index.php" class="inline-flex items-center justify-center gap-3 group mb-2">
                <div class="bg-blue-600 p-3 rounded-2xl rotate-3 group-hover:rotate-0 transition-all duration-300 shadow-lg shadow-blue-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
            </a>
            <h1 class="text-3xl font-black tracking-tighter uppercase text-[var(--text-main)] mt-4">Dijital Garaja <br><span class="text-blue-500">Katıl</span></h1>
            <p class="text-[var(--text-muted)] text-sm mt-2 font-light">Yapay zeka ile aracını analiz etmeye başla.</p>
        </div>

        <?php echo $mesaj; ?>

        <form method="POST" class="flex flex-col gap-5">
            <!-- Kullanıcı Adı -->
            <div class="input-box flex items-center px-5 py-4 rounded-2xl">
                <svg class="w-5 h-5 text-[var(--text-muted)] mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <input type="text" name="username" placeholder="Kullanıcı Adı" required 
                       class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)]">
            </div>

            <!-- E-posta -->
            <div class="input-box flex items-center px-5 py-4 rounded-2xl">
                <svg class="w-5 h-5 text-[var(--text-muted)] mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <input type="email" name="email" placeholder="E-posta Adresi" required 
                       class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)]">
            </div>

            <!-- Şifre -->
            <div class="input-box flex items-center px-5 py-4 rounded-2xl">
                <svg class="w-5 h-5 text-[var(--text-muted)] mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <input type="password" name="password" placeholder="Şifre" required 
                       class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)]">
            </div>

            <!-- Kayıt Butonu -->
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-sm transition-all hover:scale-105 active:scale-95 shadow-xl shadow-blue-600/40 mt-2 flex items-center justify-center gap-2">
                Hesap Oluştur <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <div class="text-center mt-8 pt-6 border-t border-[var(--glass-border)]">
            <p class="text-[var(--text-muted)] text-sm">
                Zaten hesabın var mı? <br>
                <a href="giris.php" class="text-blue-500 font-bold hover:text-blue-400 hover:underline transition-all uppercase tracking-wider text-xs mt-2 inline-block">Buradan Giriş Yap</a>
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