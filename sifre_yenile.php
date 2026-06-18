<?php
include 'baglan.php';

$mesaj = "";

if ($_POST) {
    $user = trim($_POST['username']);
    $mail = trim($_POST['email']);
    $new_pass = $_POST['new_password'];

    // Kullanıcı adı ve e-posta eşleşiyor mu diye veritabanına bakıyoruz
    $kontrol = $db->prepare("SELECT id FROM users WHERE username = ? AND email = ?");
    $kontrol->execute([$user, $mail]);
    $kullanici = $kontrol->fetch(PDO::FETCH_ASSOC);

    if ($kullanici) {
        // Eşleşti! Yeni şifreyi şifreleyip (hash) veritabanına kaydediyoruz
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $guncelle = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($guncelle->execute([$hashed_pass, $kullanici['id']])) {
            $mesaj = "
            <div class='relative p-4 mb-6 rounded-2xl bg-green-500/10 border border-green-500/30 text-green-500 font-bold text-center flex flex-col gap-2 pr-8'>
                <button onclick=\"this.parentElement.style.display='none'\" class='absolute top-3 right-3 hover:scale-110 transition-all'>
                    <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'></path></svg>
                </button>
                <span><i class='fa-solid fa-circle-check text-2xl mb-1'></i><br>Şifren Başarıyla Güncellendi!</span>
                <a href='giris.php' class='bg-green-500 text-white px-6 py-2.5 rounded-xl text-sm hover:bg-green-600 transition inline-block mx-auto mt-2 font-black tracking-widest uppercase'>Hemen Giriş Yap</a>
            </div>";
        }
    } else {
        // Eşleşmedi!
        $mesaj = "
        <div class='relative p-4 mb-6 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-500 font-bold text-center pr-8'>
            <button onclick=\"this.parentElement.style.display='none'\" class='absolute top-3 right-3 hover:scale-110 transition-all'>
                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'></path></svg>
            </button>
            <i class='fa-solid fa-triangle-exclamation'></i> Kullanıcı adı veya E-posta sistemdekiyle uyuşmuyor!
        </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title>Şifre Yenile | OtoAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/dist/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <script>
        let theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    </script>
    <style>
        :root { --bg-gradient: radial-gradient(circle at top right, #e2e8f0, #f8fafc, #ffffff); --text-main: #0f172a; --text-muted: #64748b; --glass-bg: rgba(255, 255, 255, 0.85); --glass-border: rgba(0, 0, 0, 0.05); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05); --input-bg: rgba(255, 255, 255, 0.95); --grid-color: rgba(0, 0, 0, 0.03); --glow-color: rgba(59, 130, 246, 0.15); }
        [data-theme="dark"] { --bg-gradient: radial-gradient(circle at top right, #1e40af, #0f172a, #030712); --text-main: #f8fafc; --text-muted: #94a3b8; --glass-bg: rgba(15, 23, 42, 0.85); --glass-border: rgba(255, 255, 255, 0.08); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); --input-bg: rgba(0, 0, 0, 0.4); --grid-color: rgba(255, 255, 255, 0.02); --glow-color: rgba(59, 130, 246, 0.25); }
        body { font-family: 'Space Grotesk', sans-serif; background: var(--bg-gradient); background-attachment: fixed; color: var(--text-main); position: relative; }
        .tema-gecis-animasyonu { transition: background 0.5s ease, color 0.5s ease; }
        body::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: linear-gradient(var(--grid-color) 1px, transparent 1px), linear-gradient(90deg, var(--grid-color) 1px, transparent 1px); background-size: 40px 40px; z-index: -1; pointer-events: none; }
        .glass { background: var(--glass-bg); border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); }
        .input-box { background: var(--input-bg); border: 1px solid var(--glass-border); transition: all 0.3s ease; }
        .input-box:focus-within { box-shadow: 0 0 20px var(--glow-color); border-color: rgba(59, 130, 246, 0.5); }
        .animate-float { animation: float 8s ease-in-out infinite; }
        @keyframes float { 0% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-20px) rotate(5deg); } 100% { transform: translateY(0px) rotate(0deg); } }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 selection:bg-blue-500 selection:text-white">

    <button id="theme-toggle" class="absolute top-6 right-6 w-12 h-12 flex items-center justify-center rounded-2xl glass hover:scale-110 hover:bg-blue-500/10 transition-all text-[var(--text-main)] z-50">
        <i id="theme-icon" class="fa-solid fa-moon text-xl"></i>
    </button>

    <div class="absolute top-20 left-10 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]"><i class="fa-solid fa-unlock-keyhole text-[15rem]"></i></div>
    <div class="absolute bottom-20 right-10 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]" style="animation-delay: -4s;"><i class="fa-solid fa-shield-halved text-[12rem]"></i></div>

    <div class="glass p-8 md:p-12 rounded-[3rem] w-full max-w-md relative z-10 shadow-2xl">
        <div class="text-center mb-10">
            <a href="index.php" class="inline-flex items-center justify-center gap-3 group mb-2">
                <div class="bg-blue-600 p-3 rounded-2xl rotate-3 group-hover:rotate-0 transition-all duration-300 shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-microchip text-white text-3xl"></i>
                </div>
            </a>
            <h1 class="text-3xl font-black tracking-tighter uppercase text-[var(--text-main)] mt-4">Şifreni <br><span class="text-blue-500">Sıfırla</span></h1>
            <p class="text-[var(--text-muted)] text-sm mt-2 font-light">Hesap bilgilerini gir ve yeni şifreni oluştur.</p>
        </div>

        <?php echo $mesaj; ?>

        <form method="POST" class="flex flex-col gap-5">
            <div class="input-box flex items-center px-5 py-4 rounded-2xl">
                <i class="fa-solid fa-user text-[var(--text-muted)] mr-4"></i>
                <input type="text" name="username" placeholder="Kullanıcı Adı" required class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)]">
            </div>
            
            <div class="input-box flex items-center px-5 py-4 rounded-2xl">
                <i class="fa-solid fa-envelope text-[var(--text-muted)] mr-4"></i>
                <input type="email" name="email" placeholder="Kayıtlı E-posta Adresi" required class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)]">
            </div>

            <div class="input-box flex items-center px-5 py-4 rounded-2xl border-t-2 border-t-blue-500/30 mt-2">
                <i class="fa-solid fa-key text-[var(--text-muted)] mr-4"></i>
                <input type="password" name="new_password" placeholder="Yeni Şifren" required class="bg-transparent w-full focus:outline-none text-base text-[var(--text-main)] placeholder-[var(--text-muted)]">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-sm transition-all hover:scale-105 active:scale-95 shadow-xl shadow-blue-600/40 mt-2">
                Şifreyi Güncelle <i class="fa-solid fa-check ml-2"></i>
            </button>
        </form>

        <div class="text-center mt-8 pt-6 border-t border-[var(--glass-border)]">
            <a href="giris.php" class="text-[var(--text-muted)] font-bold hover:text-blue-500 transition-all uppercase tracking-wider text-xs inline-block">
                <i class="fa-solid fa-arrow-left mr-1"></i> Giriş Ekranına Dön
            </a>
        </div>
    </div>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlEl = document.documentElement;

        function updateIcon(theme) { themeIcon.className = theme === 'dark' ? 'fa-solid fa-sun text-yellow-400 text-xl transition-transform transform rotate-180' : 'fa-solid fa-moon text-slate-700 text-xl transition-transform transform rotate-0'; }
        updateIcon(htmlEl.getAttribute('data-theme'));

        themeToggleBtn.addEventListener('click', () => {
            document.body.classList.add('tema-gecis-animasyonu');
            const newTheme = htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            themeIcon.style.opacity = 0;
            setTimeout(() => { htmlEl.setAttribute('data-theme', newTheme); localStorage.setItem('theme', newTheme); updateIcon(newTheme); themeIcon.style.opacity = 1; }, 150);
            setTimeout(() => { document.body.classList.remove('tema-gecis-animasyonu'); }, 600);
        });
    </script>
</body>
</html>