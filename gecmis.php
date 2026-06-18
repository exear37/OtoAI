<?php 
session_start();
include 'baglan.php';

// Oturum kontrolü
if(!isset($_SESSION['user_id'])) { 
    header("Location: giris.php"); 
    exit(); 
}

$uid = $_SESSION['user_id'];

// GEÇMİŞ KAYIT SİLME İŞLEMİ
if(isset($_GET['sil_id'])) {
    $sil_id = $_GET['sil_id'];
    $sil = $db->prepare("DELETE FROM ai_diagnostics WHERE id = ? AND user_id = ?");
    $sil->execute([$sil_id, $uid]);
    
    header("Location: gecmis.php?durum=silindi");
    exit();
}

// Veritabanı sorgusu AKTİF edildi
$sorgu = $db->prepare("SELECT * FROM ai_diagnostics WHERE user_id = ? ORDER BY created_at DESC");
$sorgu->execute([$uid]);
$gecmis = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geçmiş Teşhisler | OtoAI</title>
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
        }
        
        body { 
            font-family: 'Space Grotesk', sans-serif; 
            background: var(--bg-gradient);
            background-attachment: fixed;
            color: var(--text-main);
            position: relative;
        }

        .tema-gecis-animasyonu { transition: background 0.5s ease, color 0.5s ease; }

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

        .glass { background: var(--glass-bg); border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .nav-glass { background: var(--nav-bg); border-bottom: 1px solid var(--glass-border); }
        .input-box { background: var(--input-bg); border: 1px solid var(--glass-border); }
        .modal-neon-border { border-left: 4px solid #3b82f6; box-shadow: 0 0 40px -10px rgba(59, 130, 246, 0.5); }
        .animate-float { animation: float 8s ease-in-out infinite; }
        
        @keyframes float { 0% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-20px) rotate(5deg); } 100% { transform: translateY(0px) rotate(0deg); } }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--scrollbar-track); }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen selection:bg-blue-500 selection:text-white flex flex-col">

    <nav class="sticky top-0 z-40 nav-glass px-6 py-4 mb-10">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="bg-blue-600 p-2 rounded-xl rotate-3 group-hover:rotate-0 transition-transform duration-300 shadow-lg shadow-blue-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                </div>
                <span class="text-2xl font-black tracking-tighter uppercase italic text-[var(--text-main)]">Oto<span class="text-blue-500">AI</span></span>
            </a>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-bold uppercase tracking-widest">
                <a href="index.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Teşhis</a>
                <a href="gecmis.php" class="text-blue-500 transition hover:scale-105">Geçmiş</a>
                <a href="forum.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Forum</a>
                <a href="garajim.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Garajım</a>
            </div>

            <div class="flex items-center gap-4">
                <button id="theme-toggle" class="w-10 h-10 flex items-center justify-center rounded-xl glass hover:scale-110 hover:bg-blue-500/10 transition-all text-[var(--text-main)]">
                    <div id="theme-icon"></div>
                </button>
            </div>
        </div>
    </nav>

    <div class="flex-grow max-w-6xl mx-auto px-6 pb-20 relative w-full">
        <div class="absolute top-0 left-0 opacity-[0.03] animate-float pointer-events-none text-[var(--text-main)]">
            <svg class="w-48 h-48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>

        <!-- BİLDİRİM KUTUSU (Silme işlemi sonrası) -->
        <?php if(isset($_GET['durum']) && $_GET['durum'] == 'silindi'): ?>
            <div id="bildirim" class="mb-6 p-4 rounded-2xl glass font-bold text-center relative pr-8 flex items-center justify-center text-red-500 border-red-500/30 z-20">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Geçmiş teşhis kaydı başarıyla silindi.
                <button onclick="document.getElementById('bildirim').style.display='none'" class="absolute right-4 top-1/2 -translate-y-1/2 hover:scale-110 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <script>setTimeout(() => document.getElementById('bildirim').style.display='none', 3000);</script>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-10 relative z-10">
            <div>
                <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tighter italic text-[var(--text-main)]">
                    Geçmiş <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400 pr-2">Teşhisler</span>
                </h1>
                <p class="text-[var(--text-muted)] mt-2 text-sm md:text-base font-light">Yapay zeka motoruna yaptırdığınız tüm analiz raporları.</p>
            </div>
            <a href="index.php" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-[2rem] font-black uppercase tracking-widest text-sm transition-all shadow-xl shadow-blue-600/30 flex items-center gap-3 hover:scale-105">
                Yeni Sorgu <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </a>
        </div>

        <div class="glass p-2 md:p-3 rounded-[2.5rem] md:rounded-[3rem] shadow-2xl relative z-10">
            <div class="bg-[var(--input-bg)] rounded-[2rem] overflow-hidden border border-[var(--glass-border)]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[var(--glass-border)] text-blue-500 uppercase text-xs font-black tracking-widest">
                            <tr>
                                <th class="p-6 whitespace-nowrap">Tarih</th>
                                <th class="p-6">Sorun / Şikayet</th>
                                <th class="p-6 text-right whitespace-nowrap">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--glass-border)]">
                            <?php if(count($gecmis) > 0): ?>
                                <?php foreach($gecmis as $g): ?>
                                    <tr class="hover:bg-blue-500/5 transition-colors duration-300 group">
                                        <td class="p-6 text-sm text-[var(--text-muted)] font-medium whitespace-nowrap">
                                            <svg class="w-4 h-4 text-blue-500 inline mr-2 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <?php echo date('d.m.Y H:i', strtotime($g['created_at'])); ?>
                                        </td>
                                        <td class="p-6 font-medium text-[var(--text-main)] text-sm md:text-base">
                                            <?php echo htmlspecialchars($g['issue_description']); ?>
                                        </td>
                                        <td class="p-6 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <!-- RAPORU GÖR BUTONU -->
                                                <button onclick='raporAc(<?php echo json_encode($g["ai_solution"], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>, "<?php echo htmlspecialchars($g["issue_description"], ENT_QUOTES); ?>")' 
                                                        class="text-blue-500 group-hover:text-blue-400 font-bold text-sm flex items-center justify-center gap-2 transition-all bg-blue-500/10 hover:bg-blue-500/20 px-5 py-2.5 rounded-xl border border-blue-500/20">
                                                    Raporu Gör <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </button>
                                                
                                                <!-- YENİ EKLENEN: SİLME BUTONU (SVG İKONLU) -->
                                                <a href="gecmis.php?sil_id=<?php echo $g['id']; ?>" onclick="return confirm('Bu arıza teşhis kaydını kalıcı olarak silmek istediğinize emin misiniz?');" 
                                                   class="text-red-500 bg-red-500/10 hover:bg-red-500 hover:text-white w-10 h-10 flex items-center justify-center rounded-xl transition-all border border-red-500/20">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="p-16 text-center text-[var(--text-muted)]">
                                        <div class="w-20 h-20 bg-[var(--glass-border)] rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                        </div>
                                        <p class="font-bold">Henüz kayıtlı bir arıza teşhisi bulunmuyor.</p>
                                        <p class="text-sm font-light mt-1">İlk analizini yapmak için Yeni Sorgu butonuna tıkla.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="raporModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0, 0, 0, 0.85);">
        <div class="glass modal-neon-border max-w-3xl w-full p-8 md:p-10 rounded-[2.5rem] relative transform scale-95 opacity-0 transition-all duration-300" id="modalContentBox">
            
            <button onclick="raporKapat()" class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center rounded-xl bg-[var(--glass-border)] text-[var(--text-muted)] hover:text-red-500 hover:bg-red-500/10 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="mb-6 pr-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center border border-blue-500/30">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400 uppercase italic pr-2">Yapay Zeka Raporu</h2>
                </div>
                <p id="modalSorun" class="text-[var(--text-muted)] text-sm font-medium border-l-2 border-blue-500/50 pl-3 mt-4"></p>
            </div>

            <div class="input-box p-6 md:p-8 rounded-[1.5rem] max-h-[50vh] overflow-y-auto mb-6 text-[var(--text-main)] leading-relaxed text-sm md:text-base font-light shadow-inner" id="modalIcerik">
            </div>

            <button onclick="raporKapat()" class="w-full bg-[var(--glass-border)] hover:bg-blue-500 hover:text-white text-[var(--text-main)] py-4 rounded-xl font-black transition-all uppercase tracking-widest text-sm border border-[var(--glass-border)]">
                PENCEREYİ KAPAT
            </button>
        </div>
    </div>

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
            const currentTheme = htmlEl.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            themeIcon.style.opacity = 0;
            setTimeout(() => {
                htmlEl.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
                themeIcon.style.opacity = 1;
            }, 150);

            setTimeout(() => {
                document.body.classList.remove('tema-gecis-animasyonu');
            }, 600);
        });

        const modal = document.getElementById('raporModal');
        const modalBox = document.getElementById('modalContentBox');

        function raporAc(icerik, sorun) {
            let formatliIcerik = icerik.replace(/\n/g, "<br>");
            formatliIcerik = formatliIcerik.replace(/\*\*(.*?)\*\*/g, "<strong class='text-blue-400'>$1</strong>");
            
            document.getElementById('modalIcerik').innerHTML = formatliIcerik;
            document.getElementById('modalSorun').innerHTML = "<strong class='text-[var(--text-main)]'>Şikayet:</strong> " + sorun;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modalBox.classList.remove('scale-95', 'opacity-0');
                modalBox.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function raporKapat() {
            modalBox.classList.remove('scale-100', 'opacity-100');
            modalBox.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                raporKapat();
            }
        }
    </script>
</body>
</html>