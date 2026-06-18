<?php 
session_start();
include 'baglan.php';

// Oturum kontrolü
if(!isset($_SESSION['user_id'])) { 
    header("Location: giris.php"); 
    exit(); 
}

$uid = $_SESSION['user_id'];
$rol = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// VERİTABANI KONTROLLERİ (Yeni sütunlar eklendi)
$db->query("CREATE TABLE IF NOT EXISTS garaj (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    marka VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    yil INT NOT NULL,
    motor VARCHAR(100) NOT NULL,
    km VARCHAR(50) NULL,
    degisenler TEXT NULL,
    eski_sorunlar TEXT NULL,
    usta_notu TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Mevcut tabloyu güncelleme 
try { $db->query("ALTER TABLE garaj ADD km VARCHAR(50) NULL"); } catch(PDOException $e) {}
try { $db->query("ALTER TABLE garaj ADD degisenler TEXT NULL"); } catch(PDOException $e) {}
try { $db->query("ALTER TABLE garaj ADD eski_sorunlar TEXT NULL"); } catch(PDOException $e) {}

// ARAÇ EKLEME İŞLEMİ 
if(isset($_POST['arac_ekle'])) {
    $marka = trim($_POST['marka']);
    $model = trim($_POST['model']);
    $yil = trim($_POST['yil']);
    $motor = trim($_POST['motor']);
    $km = trim($_POST['km']);
    $degisenler = trim($_POST['degisenler']);
    $eski_sorunlar = trim($_POST['eski_sorunlar']);

    $ekle = $db->prepare("INSERT INTO garaj (user_id, marka, model, yil, motor, km, degisenler, eski_sorunlar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $ekle->execute([$uid, $marka, $model, $yil, $motor, $km, $degisenler, $eski_sorunlar]);
    
    header("Location: garajim.php?durum=eklendi");
    exit();
}

// USTA NOTU KAYDETME İŞLEMİ
if(isset($_POST['usta_notu_kaydet'])) {
    if($rol == 'usta' || $rol == 'admin') {
        $arac_id = $_POST['arac_id'];
        $yeni_not = $_POST['usta_notu'];

        $guncelle = $db->prepare("UPDATE garaj SET usta_notu = ? WHERE id = ?");
        $guncelle->execute([$yeni_not, $arac_id]);
        
        header("Location: garajim.php?durum=not_kaydedildi");
        exit();
    }
}

// ARAÇ SİLME İŞLEMİ
if(isset($_GET['sil_id'])) {
    $sil_id = $_GET['sil_id'];
    $sil = $db->prepare("DELETE FROM garaj WHERE id = ? AND user_id = ?");
    $sil->execute([$sil_id, $uid]);
    
    header("Location: garajim.php?durum=silindi");
    exit();
}

// GARAJDAN ARAÇLARI ÇEK
$sorgu = $db->prepare("SELECT * FROM garaj WHERE user_id = ? ORDER BY id DESC");
$sorgu->execute([$uid]);
$araclar = $sorgu->fetchAll(PDO::FETCH_ASSOC);

// LOGO SİSTEMİ
function logoBul($marka) {
    $marka = strtolower(trim($marka));
    $ilk_kelime = explode(' ', $marka)[0];
    $logolar = [
        'bmw' => 'https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg',
        'mercedes' => 'https://upload.wikimedia.org/wikipedia/commons/9/90/Mercedes-Logo.svg',
        'mercedes-benz' => 'https://upload.wikimedia.org/wikipedia/commons/9/90/Mercedes-Logo.svg',
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
    if(isset($logolar[$marka])) return $logolar[$marka];
    elseif(isset($logolar[$ilk_kelime])) return $logolar[$ilk_kelime];
    else return 'https://ui-avatars.com/api/?name=' . urlencode(strtoupper($ilk_kelime)) . '&background=3b82f6&color=fff&rounded=true&bold=true';
}
?>

<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garajım | OtoAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/dist/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <script>
        let theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    </script>

    <style>
        :root { --bg-gradient: radial-gradient(circle at top right, #e2e8f0, #f8fafc, #ffffff); --text-main: #0f172a; --text-muted: #64748b; --glass-bg: rgba(255, 255, 255, 0.85); --glass-border: rgba(0, 0, 0, 0.05); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05); --input-bg: rgba(255, 255, 255, 0.95); --nav-bg: rgba(255, 255, 255, 0.95); --scrollbar-track: #f1f5f9; --grid-color: rgba(0, 0, 0, 0.03); }
        [data-theme="dark"] { --bg-gradient: radial-gradient(circle at top right, #1e40af, #0f172a, #030712); --text-main: #f8fafc; --text-muted: #94a3b8; --glass-bg: rgba(15, 23, 42, 0.85); --glass-border: rgba(255, 255, 255, 0.08); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); --input-bg: rgba(0, 0, 0, 0.6); --nav-bg: rgba(3, 7, 18, 0.95); --scrollbar-track: #030712; --grid-color: rgba(255, 255, 255, 0.02); }
        body { font-family: 'Space Grotesk', sans-serif; background: var(--bg-gradient); background-attachment: fixed; color: var(--text-main); position: relative; }
        .tema-gecis-animasyonu { transition: background 0.5s ease, color 0.5s ease; }
        body::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: linear-gradient(var(--grid-color) 1px, transparent 1px), linear-gradient(90deg, var(--grid-color) 1px, transparent 1px); background-size: 40px 40px; z-index: -1; pointer-events: none; }
        .glass { background: var(--glass-bg); border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); }
        .nav-glass { background: var(--nav-bg); border-bottom: 1px solid var(--glass-border); }
        .input-box { background: var(--input-bg); border: 1px solid var(--glass-border); }
        .modal-neon-border { border-left: 4px solid #3b82f6; box-shadow: 0 0 40px -10px rgba(59, 130, 246, 0.5); }
        .usta-neon-border { border-left: 4px solid #a855f7; box-shadow: 0 0 40px -10px rgba(168, 85, 247, 0.5); }
        .arac-kart:hover { transform: translateY(-5px); border-color: rgba(59, 130, 246, 0.3); box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.2); }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--scrollbar-track); }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
        input[type="file"]::file-selector-button { display: none; }
    </style>
</head>
<body class="min-h-screen selection:bg-blue-500 selection:text-white">

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
                <a href="gecmis.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Geçmiş</a>
                <a href="forum.php" class="text-[var(--text-muted)] hover:text-blue-500 transition hover:scale-105">Forum</a>
                <a href="garajim.php" class="text-blue-500 transition hover:scale-105">Garajım</a>
            </div>
            <div class="flex items-center gap-4">
                <button id="theme-toggle" class="w-10 h-10 flex items-center justify-center rounded-xl glass hover:scale-110 transition-all text-[var(--text-main)]">
                    <div id="theme-icon"></div>
                </button>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 pb-20 relative">
        
        <?php if(isset($_GET['durum'])): ?>
            <div id="bildirim" class="mb-6 p-4 rounded-2xl glass font-bold text-center relative pr-8 flex items-center justify-center
                <?php echo ($_GET['durum'] == 'eklendi' || $_GET['durum'] == 'not_kaydedildi') ? 'text-green-500 border-green-500/30' : 'text-red-500 border-red-500/30'; ?>">
                
                <?php if($_GET['durum'] == 'eklendi' || $_GET['durum'] == 'not_kaydedildi'): ?>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php else: ?>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                <?php endif; ?>

                <?php 
                    if($_GET['durum'] == 'eklendi') echo 'Araç garaja eklendi!';
                    elseif($_GET['durum'] == 'not_kaydedildi') echo 'Usta notu başarıyla kaydedildi!';
                    else echo 'Araç garajdan silindi!'; 
                ?>
                
                <button onclick="document.getElementById('bildirim').style.display='none'" class="absolute right-4 top-1/2 -translate-y-1/2 hover:scale-110 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <script>setTimeout(() => document.getElementById('bildirim').style.display='none', 3000);</script>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-12 relative z-10">
            <div>
                <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tighter italic text-[var(--text-main)]">
                    Dijital <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400 pr-2">Garajım</span>
                </h1>
                <p class="text-[var(--text-muted)] mt-2 text-sm md:text-base font-light">Araçlarınızı ekleyin, analizleri ve teknik bilgileri kolayca yönetin.</p>
            </div>
            <button onclick="aracEkleAc()" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-[2rem] font-black uppercase tracking-widest text-sm transition-all shadow-xl shadow-blue-600/30 flex items-center gap-3 hover:scale-105">
                Araç Ekle <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </button>
        </div>

        <?php if(count($araclar) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 relative z-10">
                <?php foreach($araclar as $arac): ?>
                    <div class="glass p-8 rounded-[2.5rem] arac-kart relative overflow-hidden transition-all duration-300 flex flex-col h-full pt-10">
                        <div class="absolute -right-6 -bottom-6 text-[10rem] opacity-[0.03] text-[var(--text-main)] pointer-events-none">
                            <svg class="w-60 h-60" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                        </div>
                        
                        <a href="garajim.php?sil_id=<?php echo $arac['id']; ?>" onclick="return confirm('Silinsin mi?');" class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition-all z-20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </a>
                        
                        <div class="w-16 h-16 bg-white/95 rounded-2xl flex items-center justify-center border border-[var(--glass-border)] mb-4 relative z-10 shadow-md p-2">
                            <img src="<?php echo logoBul($arac['marka']); ?>" class="w-full h-full object-contain" alt="Logo">
                        </div>

                        <h3 class="text-2xl font-black uppercase tracking-tight text-[var(--text-main)] mb-1 relative z-10">
                            <?php echo htmlspecialchars($arac['marka']); ?>
                        </h3>
                        <p class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400 mb-6 uppercase relative z-10">
                            <?php echo htmlspecialchars($arac['model']); ?>
                        </p>

                        <div class="grid grid-cols-2 gap-4 mb-6 relative z-10">
                            <div class="input-box p-3 rounded-xl text-center">
                                <p class="text-[10px] uppercase font-bold text-[var(--text-muted)]">Model Yılı</p>
                                <p class="font-black text-[var(--text-main)]"><?php echo htmlspecialchars($arac['yil']); ?></p>
                            </div>
                            <div class="input-box p-3 rounded-xl text-center">
                                <p class="text-[10px] uppercase font-bold text-[var(--text-muted)]">Kilometre</p>
                                <p class="font-black text-[var(--text-main)]"><?php echo htmlspecialchars($arac['km'] ?? '0'); ?> KM</p>
                            </div>
                        </div>

                        <!-- YENİ: Değişenler ve Eski Sorunlar Bölümü -->
                        <div class="space-y-4 mb-6 relative z-10 flex-grow">
                            <div class="bg-red-500/5 p-3 rounded-xl border border-red-500/10">
                                <p class="text-[9px] uppercase font-black text-red-500 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Değişenler
                                </p>
                                <p class="text-xs text-[var(--text-main)] leading-relaxed italic"><?php echo !empty($arac['degisenler']) ? htmlspecialchars($arac['degisenler']) : 'Kayıt yok'; ?></p>
                            </div>
                            <div class="bg-yellow-500/5 p-3 rounded-xl border border-yellow-500/10">
                                <p class="text-[9px] uppercase font-black text-yellow-500 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Eski Sorunlar
                                </p>
                                <p class="text-xs text-[var(--text-main)] leading-relaxed"><?php echo !empty($arac['eski_sorunlar']) ? htmlspecialchars($arac['eski_sorunlar']) : 'Kayıt yok'; ?></p>
                            </div>
                        </div>

                        <?php if($rol == 'usta' || $rol == 'admin'): ?>
                            <button onclick='ustaBilgiAc(<?php echo $arac['id']; ?>, "<?php echo htmlspecialchars($arac['marka'] . ' ' . $arac['model'], ENT_QUOTES); ?>", "<?php echo htmlspecialchars($arac['motor'], ENT_QUOTES); ?>", <?php echo json_encode($arac['usta_notu'] ?? ''); ?>)' class="w-full bg-purple-500/10 hover:bg-purple-500 text-purple-500 hover:text-white py-3 rounded-xl font-bold transition-all uppercase tracking-widest text-xs border border-purple-500/30 flex items-center justify-center gap-2 mt-auto relative z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Usta Notu Ekle
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass p-16 rounded-[3rem] text-center max-w-2xl mx-auto relative z-10">
                <svg class="w-20 h-20 mx-auto mb-6 text-[var(--text-muted)] opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <h3 class="text-2xl font-black uppercase text-[var(--text-main)] mb-2">Garajın Bomboş!</h3>
                <button onclick="aracEkleAc()" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-[2rem] font-black uppercase text-sm mt-8 transition-all shadow-xl shadow-blue-600/30 flex items-center justify-center gap-2 mx-auto">
                    İlk Aracını Ekle <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- ARAÇ EKLEME MODALI -->
    <div id="aracEkleModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0, 0, 0, 0.85);">
        <div class="glass modal-neon-border max-w-lg w-full p-8 md:p-10 rounded-[2.5rem] relative transform scale-95 opacity-0 transition-all duration-300 overflow-y-auto max-h-[90vh]" id="aracModalBox">
            <button onclick="aracEkleKapat()" class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center rounded-xl bg-[var(--glass-border)] text-[var(--text-muted)] hover:text-red-500 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="mb-8 pr-10">
                <h2 class="text-2xl font-black text-[var(--text-main)] uppercase italic">Yeni <span class="text-blue-500">Araç Ekle</span></h2>
            </div>

            <form method="POST" class="flex flex-col gap-4">
                <div class="input-box flex items-center px-5 py-3 rounded-2xl">
                    <svg class="w-5 h-5 text-[var(--text-muted)] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <input type="text" name="marka" placeholder="Marka (BMW, Fiat...)" required class="bg-transparent w-full outline-none text-sm text-[var(--text-main)]">
                </div>
                <div class="input-box flex items-center px-5 py-3 rounded-2xl">
                    <svg class="w-5 h-5 text-[var(--text-muted)] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                    <input type="text" name="model" placeholder="Model (E46, Egea...)" required class="bg-transparent w-full outline-none text-sm text-[var(--text-main)]">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="input-box flex items-center px-5 py-3 rounded-2xl">
                        <svg class="w-5 h-5 text-[var(--text-muted)] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <input type="number" name="yil" placeholder="Yıl" required class="bg-transparent w-full outline-none text-sm text-[var(--text-main)]">
                    </div>
                    <div class="input-box flex items-center px-5 py-3 rounded-2xl">
                        <svg class="w-5 h-5 text-[var(--text-muted)] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM17 13v1.8c0 1.1-.9 2-2 2H9c-1.1 0-2-.9-2-2V13m10-5V6.2c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2V8m10 0h2a2 2 0 012 2v3m-14 0H3a2 2 0 01-2-2V10a2 2 0 012-2h2"></path></svg>
                        <input type="text" name="motor" placeholder="Motor" required class="bg-transparent w-full outline-none text-sm text-[var(--text-main)]">
                    </div>
                </div>
                <div class="input-box flex items-center px-5 py-3 rounded-2xl">
                    <svg class="w-5 h-5 text-[var(--text-muted)] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <input type="text" name="km" placeholder="Mevcut Kilometre" class="bg-transparent w-full outline-none text-sm text-[var(--text-main)]">
                </div>
                <div class="input-box p-4 rounded-2xl">
                    <textarea name="degisenler" rows="2" placeholder="Değişen parçalar varsa belirtin..." class="bg-transparent w-full outline-none text-sm text-[var(--text-main)] resize-none"></textarea>
                </div>
                <div class="input-box p-4 rounded-2xl">
                    <textarea name="eski_sorunlar" rows="2" placeholder="Eski kronik sorunlar veya arıza geçmişi..." class="bg-transparent w-full outline-none text-sm text-[var(--text-main)] resize-none"></textarea>
                </div>

                <button type="submit" name="arac_ekle" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-2xl font-black uppercase text-xs mt-4 shadow-lg">KAYDET</button>
            </form>
        </div>
    </div>

    <!-- USTA BİLGİ MODALI -->
    <div id="ustaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0, 0, 0, 0.85);">
        <div class="glass usta-neon-border max-w-2xl w-full p-8 md:p-10 rounded-[2.5rem] relative transform scale-95 opacity-0 transition-all duration-300" id="ustaModalBox">
            <button onclick="ustaBilgiKapat()" class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center rounded-xl bg-[var(--glass-border)] text-[var(--text-muted)] hover:text-red-500 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="mb-6 pr-10">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <h2 class="text-2xl font-black text-purple-500 uppercase italic">Usta Notları</h2>
                </div>
                <p id="ustaAracIsim" class="text-[var(--text-main)] text-lg font-bold border-l-2 border-purple-500/50 pl-3 mt-4"></p>
            </div>
            <form method="POST">
                <input type="hidden" name="arac_id" id="ustaAracId" value="">
                <div class="mb-6">
                    <textarea name="usta_notu" id="ustaNotIcerik" rows="5" placeholder="Notlarınızı buraya yazın..." class="input-box w-full p-5 rounded-[1.5rem] outline-none text-[var(--text-main)] text-sm resize-none"></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="submit" name="usta_notu_kaydet" class="w-full bg-purple-600 hover:bg-purple-500 text-white py-4 rounded-xl font-black uppercase text-xs shadow-lg transition-all">NOTU KAYDET</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // TEMA YÖNETİMİ
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlEl = document.documentElement;

        function updateIcon(theme) {
            themeIcon.innerHTML = theme === 'dark' 
                ? '<svg class="w-5 h-5 text-yellow-400 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>'
                : '<svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>';
        }
        updateIcon(htmlEl.getAttribute('data-theme'));

        themeToggleBtn.addEventListener('click', () => {
            const newTheme = htmlEl.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            htmlEl.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        // MODAL FONKSİYONLARI
        function aracEkleAc() {
            const m = document.getElementById('aracEkleModal');
            m.classList.remove('hidden'); m.classList.add('flex');
            setTimeout(() => { document.getElementById('aracModalBox').classList.remove('scale-95', 'opacity-0'); document.getElementById('aracModalBox').classList.add('scale-100', 'opacity-100'); }, 10);
        }
        function aracEkleKapat() {
            document.getElementById('aracModalBox').classList.remove('scale-100', 'opacity-100'); document.getElementById('aracModalBox').classList.add('scale-95', 'opacity-0');
            setTimeout(() => { document.getElementById('aracEkleModal').classList.remove('flex'); document.getElementById('aracEkleModal').classList.add('hidden'); }, 300);
        }
        function ustaBilgiAc(id, isim, motor, not) {
            document.getElementById('ustaAracIsim').innerText = isim + " (" + motor + ")";
            document.getElementById('ustaAracId').value = id;
            document.getElementById('ustaNotIcerik').value = not;
            const m = document.getElementById('ustaModal');
            m.classList.remove('hidden'); m.classList.add('flex');
            setTimeout(() => { document.getElementById('ustaModalBox').classList.remove('scale-95', 'opacity-0'); document.getElementById('ustaModalBox').classList.add('scale-100', 'opacity-100'); }, 10);
        }
        function ustaBilgiKapat() {
            document.getElementById('ustaModalBox').classList.remove('scale-100', 'opacity-100'); document.getElementById('ustaModalBox').classList.add('scale-95', 'opacity-0');
            setTimeout(() => { document.getElementById('ustaModal').classList.remove('flex'); document.getElementById('ustaModal').classList.add('hidden'); }, 300);
        }
    </script>
</body>
</html>