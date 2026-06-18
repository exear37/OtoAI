<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OtoAI | Akıllı Arıza Tespit Merkezi</title>
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
            --nav-bg: rgba(255, 255, 255, 0.6);
            --footer-bg: rgba(0, 0, 0, 0.03);
            --scrollbar-track: #f1f5f9;
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
            --nav-bg: rgba(3, 7, 18, 0.6);
            --footer-bg: rgba(0, 0, 0, 0.2);
            --scrollbar-track: #030712;
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
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-glass {
            background: var(--nav-bg);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--glass-border);
        }

        .input-box {
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .input-box:focus-within {
            box-shadow: 0 0 30px var(--glow-color);
            transform: scale(1.01);
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.3);
        }

        .step-card {
            position: relative;
            overflow: hidden;
        }
        
        .step-card::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.5s;
            pointer-events: none;
        }

        .step-card:hover::after {
            opacity: 1;
        }

        .animate-float {
            animation: float 8s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float 8s ease-in-out 4s infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        .marquee-container {
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
        }

        .marquee-content {
            display: inline-block;
            animation: marquee 25s linear infinite;
        }

        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--scrollbar-track); }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #2563eb; }

        /* YENİ EKLENEN CSS: Dönen Yükleme Ekranı Çember Animasyonu */
        .loader-ring {
            position: absolute; inset: 0; border: 4px solid transparent; border-top-color: #3b82f6; border-radius: 50%;
            animation: spin-loader 1s linear infinite;
        }
        .loader-ring-2 {
            position: absolute; inset: 8px; border: 4px solid transparent; border-bottom-color: #06b6d4; border-radius: 50%;
            animation: spin-reverse 1.5s linear infinite;
        }
        @keyframes spin-loader { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes spin-reverse { 0% { transform: rotate(360deg); } 100% { transform: rotate(0deg); } }
    </style>
</head>
<body class="min-h-screen selection:bg-blue-500 selection:text-white flex flex-col">

    <nav class="sticky top-0 z-50 nav-glass px-6 py-4 transition-colors duration-500">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="bg-blue-600 p-2 rounded-xl rotate-3 group-hover:rotate-0 transition-all duration-300 shadow-lg shadow-blue-500/30 group-hover:shadow-blue-500/50">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <span class="text-2xl font-black tracking-tighter uppercase italic text-[var(--text-main)]">Oto<span class="text-blue-500">AI</span></span>
            </a>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-bold uppercase tracking-widest">
                <a href="index.php" class="text-blue-500 hover:text-blue-600 transition hover:scale-105">Teşhis</a>
                <a href="gecmis.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Geçmiş</a>
                <a href="forum.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Forum</a>
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
                         <img src="uploads/<?php echo isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : 'default.png'; ?>" 
     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=3b82f6&color=fff'" 
     class="w-10 h-10 rounded-full border-2 border-blue-600 object-cover shadow-lg group-hover:shadow-blue-500/50">
                        </a>

                        <a href="cikis.php" class="flex items-center gap-2 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-xl transition-all duration-300 font-bold text-sm shadow-sm group">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="hidden sm:block">Çıkış</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="hidden sm:flex items-center gap-4 pl-4 border-l border-[var(--glass-border)]">
                        <a href="giris.php" class="text-sm font-bold uppercase text-[var(--text-muted)] hover:text-blue-500 transition">Giriş</a>
                        <a href="kayit.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:scale-105">Kayıt Ol</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto px-6 py-12 md:py-20 relative">
        <div class="absolute top-20 left-10 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]">
            <svg class="w-60 h-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div class="absolute bottom-40 right-10 opacity-[0.03] animate-float-delayed pointer-events-none text-[var(--text-main)]">
            <svg class="w-48 h-48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>

        <div class="relative z-10 text-center mb-16">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass text-blue-500 text-sm font-bold mb-6 animate-pulse">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                </span>
                Gemini 3 Flash Sistemi Aktif
            </div>
            <h2 class="text-5xl md:text-7xl lg:text-8xl font-black mb-6 tracking-tighter leading-tight text-[var(--text-main)] relative inline-block">
                ARACINIZIN <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 via-cyan-400 to-indigo-500 relative">
                    DİJİTAL SESİ
                    <div class="absolute inset-0 bg-blue-500 blur-[80px] opacity-20 -z-10"></div>
                </span>
            </h2>
            <p class="text-[var(--text-muted)] text-lg md:text-2xl max-w-3xl mx-auto font-light leading-relaxed mt-4">
                Yapay zeka motorumuz ile saniyeler içinde profesyonel arıza tespiti yapın. 
                Aracınızın fısıltısını detaylı teknik verilere dönüştürün.
            </p>
        </div>

        <div class="max-w-4xl mx-auto mb-16 relative z-20">
            <div class="glass p-2 md:p-3 rounded-[2.5rem] md:rounded-[3rem] shadow-2xl relative">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-[3rem] blur opacity-20 group-hover:opacity-40 transition duration-1000 -z-10"></div>
                
                <form action="sohbet.php" method="POST" class="flex flex-col md:flex-row items-center gap-2" onsubmit="yukleniyorGoster()">
                    <div class="flex-1 w-full flex items-center px-6 py-4 md:py-5 input-box rounded-[2rem] md:rounded-[2.5rem]">
                        <svg class="w-6 h-6 text-blue-500 mr-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" name="sorun" required 
                               placeholder="Sorunu buraya yazın (Örn: Sabahları araç zor çalışıyor ve titriyor...)" 
                               class="bg-transparent w-full focus:outline-none text-base md:text-lg text-[var(--text-main)] placeholder-[var(--text-muted)]">
                    </div>
                    <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-500 text-white px-10 py-4 md:py-5 rounded-[2rem] md:rounded-[2.5rem] font-black uppercase tracking-widest text-sm transition-all hover:scale-105 active:scale-95 shadow-xl shadow-blue-600/40 flex items-center justify-center gap-3 group">
                        Analiz Başlat 
                        <svg class="w-5 h-5 group-hover:text-yellow-300 transition-colors" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="mb-24 relative z-20">
            <p class="text-center text-sm font-bold text-[var(--text-muted)] uppercase tracking-widest mb-6">Yapay Zekanın Tanıdığı Markalar</p>
            <div class="marquee-container w-full max-w-5xl mx-auto opacity-60">
                <div class="marquee-content flex gap-12 items-center text-3xl text-[var(--text-muted)]">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg> <span class="font-black text-xl uppercase">BMW</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Mercedes</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01L19 11H5l1.5-4.5h11c.66 0 1.21.42 1.42 1.01z"/></svg> <span class="font-black text-xl uppercase">Audi</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Volkswagen</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Toyota</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Ford</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Honda</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Renault</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Fiat</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Peugeot</span>
                    
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">BMW</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Mercedes</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01L19 11H5l1.5-4.5h11c.66 0 1.21.42 1.42 1.01z"/></svg> <span class="font-black text-xl uppercase">Audi</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Volkswagen</span>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg> <span class="font-black text-xl uppercase">Toyota</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 mb-24 relative z-20">
            <div class="glass p-8 md:p-10 rounded-[2.5rem] feature-card">
                <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 border border-blue-500/20">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <h3 class="text-2xl font-black mb-3 uppercase tracking-tight text-[var(--text-main)]">Akıllı Teşhis</h3>
                <p class="text-[var(--text-muted)] leading-relaxed font-light">
                    Milyonlarca teknik veri arasından aracınızın hatasını anında tespit ederiz. Tamamen yapay zeka gücüyle.
                </p>
            </div>

            <div class="glass p-8 md:p-10 rounded-[2.5rem] feature-card">
                <div class="w-14 h-14 bg-purple-500/10 rounded-2xl flex items-center justify-center mb-6 border border-purple-500/20">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-black mb-3 uppercase tracking-tight text-[var(--text-main)]">Usta Onayı</h3>
                <p class="text-[var(--text-muted)] leading-relaxed font-light">
                    Yapay zekanın analizlerini forumdaki ustalarımızla saniyeler içinde paylaşıp teyit alabilirsiniz.
                </p>
            </div>

            <div class="glass p-8 md:p-10 rounded-[2.5rem] feature-card">
                <div class="w-14 h-14 bg-green-500/10 rounded-2xl flex items-center justify-center mb-6 border border-green-500/20">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM17 13v1.8c0 1.1-.9 2-2 2H9c-1.1 0-2-.9-2-2V13m10-5V6.2c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2V8m10 0h2a2 2 0 012 2v3m-14 0H3a2 2 0 01-2-2V10a2 2 0 012-2h2"></path></svg>
                </div>
                <h3 class="text-2xl font-black mb-3 uppercase tracking-tight text-[var(--text-main)]">Net Maliyet</h3>
                <p class="text-[var(--text-muted)] leading-relaxed font-light">
                    Sürpriz faturalara son. Olası parça ve tahmini işçilik maliyetlerini önceden görerek bütçenizi ayarlayın.
                </p>
            </div>
        </div>

        <div class="max-w-6xl mx-auto relative z-20 mb-10">
            <h2 class="text-3xl md:text-4xl font-black text-center mb-12 uppercase tracking-tighter text-[var(--text-main)]">Nasıl Çalışır?</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="glass p-6 rounded-3xl step-card text-center group">
                    <div class="w-16 h-16 mx-auto bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-black mb-4 shadow-lg shadow-blue-500/40 group-hover:scale-110 transition-transform">1</div>
                    <h4 class="font-bold text-lg mb-2 text-[var(--text-main)]">Sorunu Yaz</h4>
                    <p class="text-sm text-[var(--text-muted)]">Aracındaki belirtileri arama kutusuna detaylıca gir.</p>
                </div>
                <div class="glass p-6 rounded-3xl step-card text-center group">
                    <div class="w-16 h-16 mx-auto bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-black mb-4 shadow-lg shadow-blue-500/40 group-hover:scale-110 transition-transform">2</div>
                    <h4 class="font-bold text-lg mb-2 text-[var(--text-main)]">AI Analizi</h4>
                    <p class="text-sm text-[var(--text-muted)]">Gemini saniyeler içinde binlerce ihtimali tarar.</p>
                </div>
                <div class="glass p-6 rounded-3xl step-card text-center group">
                    <div class="w-16 h-16 mx-auto bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-black mb-4 shadow-lg shadow-blue-500/40 group-hover:scale-110 transition-transform">3</div>
                    <h4 class="font-bold text-lg mb-2 text-[var(--text-main)]">Rapor Al</h4>
                    <p class="text-sm text-[var(--text-muted)]">Olası arızalar ve çözüm yolları önüne listelenir.</p>
                </div>
                <div class="glass p-6 rounded-3xl step-card text-center group">
                    <div class="w-16 h-16 mx-auto bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-black mb-4 shadow-lg shadow-blue-500/40 group-hover:scale-110 transition-transform">4</div>
                    <h4 class="font-bold text-lg mb-2 text-[var(--text-main)]">Foruma Sor</h4>
                    <p class="text-sm text-[var(--text-muted)]">İstersen sonucu direkt toplulukla paylaş.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-[var(--glass-border)] py-12 mt-auto" style="background: var(--footer-bg);">
        <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
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

    <!-- YAPAY ZEKA DÜŞÜNÜYOR EKRANI -->
    <div id="loadingOverlay" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 transition-all duration-500 opacity-0" style="background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(10px);">
        <div class="text-center transform transition-all duration-500 scale-90" id="loadingContent">
            <div class="relative w-36 h-36 mx-auto mb-8">
                <div class="absolute inset-0 bg-blue-500/20 rounded-full blur-xl animate-pulse"></div>
                <div class="absolute inset-0 border-4 border-[var(--glass-border)] rounded-full"></div>
                <div class="loader-ring"></div>
                <div class="loader-ring-2"></div>
                <div class="absolute inset-0 flex items-center justify-center text-blue-500">
                    <svg class="w-12 h-12 text-blue-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
            </div>
            <h2 class="text-3xl md:text-4xl font-black uppercase tracking-widest text-white mb-3">Gemini <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400">Analiz Ediyor</span></h2>
            <p class="text-blue-200 text-sm md:text-base font-medium animate-pulse tracking-widest uppercase">Milyonlarca veri taranıyor, lütfen bekleyin...</p>
        </div>
    </div>

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

        // YÜKLEME EKRANI FONKSİYONU
        function yukleniyorGoster() {
            const overlay = document.getElementById('loadingOverlay');
            const content = document.getElementById('loadingContent');
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
                content.classList.remove('scale-90');
                content.classList.add('scale-100');
            }, 50);
        }
    </script>
</body>
</html>