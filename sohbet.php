<?php 
session_start();
include 'baglan.php';

if(!isset($_SESSION['user_id'])) { 
    header("Location: giris.php"); 
    exit(); 
}

$uid = $_SESSION['user_id'];
$api_key = "Kendi API ANAHATIRNIZ"; // Kendi API anahtarın

// Eğer sohbet geçmişi yoksa oluştur
if(!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// === API'YE İSTEK ATMA FONKSİYONU ===
function geminiSoruSor($api_key, $yeni_mesaj, $db, $uid) {
    // Geliştirme: Kullanıcının TÜM araçlarını garajdan çek[cite: 11]
    $arac_sorgu = $db->prepare("SELECT * FROM garaj WHERE user_id = ? ORDER BY id DESC");
    $arac_sorgu->execute([$uid]);
    $araclar = $arac_sorgu->fetchAll(PDO::FETCH_ASSOC);

    $garaj_ozeti = "";
    if (!empty($araclar)) {
        $garaj_ozeti = "Kullanıcının garajındaki araçlar: ";
        foreach($araclar as $index => $a) {
            $n = $index + 1;
            $garaj_ozeti .= "$n. Araç: {$a['yil']} {$a['marka']} {$a['model']} ({$a['motor']}), KM: {$a['km']}, Değişenler: {$a['degisenler']}, Eski Sorunlar: {$a['eski_sorunlar']}. ";
        }
    }

    // Gemini API için içerik dizisini oluştur
    $gemini_contents = [];

    // Eğer ilk mesajsa, yapay zekaya tüm garajı tanıtalım (Sistem Prompt)
    if(empty($_SESSION['chat_history'])) {
        $ilk_komut = "Sen uzman bir otomotiv mekanik ustasısın. Kısa, net ve teknik bilgi ver. $garaj_ozeti Kullanıcının sorusu: " . $yeni_mesaj;
        $gemini_contents[] = ["role" => "user", "parts" => [["text" => $ilk_komut]]];
    } else {
        // Önceki sohbet geçmişini Gemini'nin anlayacağı formata çevir[cite: 12]
        foreach($_SESSION['chat_history'] as $chat) {
            $role = ($chat['role'] == 'user') ? 'user' : 'model';
            $gemini_contents[] = ["role" => $role, "parts" => [["text" => $chat['message']]]];
        }
        // Son mesajı ekle[cite: 12]
        $gemini_contents[] = ["role" => "user", "parts" => [["text" => $yeni_mesaj]]];
    }

    $payload = ["contents" => $gemini_contents];
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key=" . $api_key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code === 200 && isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return $result['candidates'][0]['content']['parts'][0]['text'];
    } else {
        return "Sistemde anlık bir yoğunluk var, lütfen tekrar deneyin. (Hata Kodu: $http_code)";
    }
}

// 1. İLK SORU
if(isset($_POST['sorun'])) {
    $ilk_mesaj = trim($_POST['sorun']);
    $ai_cevap = geminiSoruSor($api_key, $ilk_mesaj, $db, $uid);
    $_SESSION['chat_history'][] = ['role' => 'user', 'message' => $ilk_mesaj];
    $_SESSION['chat_history'][] = ['role' => 'ai', 'message' => $ai_cevap];
    header("Location: sohbet.php");
    exit();
}

// 2. SOHBETİN DEVAMI
if(isset($_POST['yeni_mesaj'])) {
    $devam_mesaji = trim($_POST['yeni_mesaj']);
    $ai_cevap = geminiSoruSor($api_key, $devam_mesaji, $db, $uid);
    $_SESSION['chat_history'][] = ['role' => 'user', 'message' => $devam_mesaji];
    $_SESSION['chat_history'][] = ['role' => 'ai', 'message' => $ai_cevap];
    header("Location: sohbet.php");
    exit();
}

// 3. SOHBETİ BİTİR VE KAYDET
if(isset($_GET['bitir'])) {
    if(!empty($_SESSION['chat_history'])) {
        $ilk_soru = $_SESSION['chat_history'][0]['message']; 
        $tam_rapor = "";
        foreach($_SESSION['chat_history'] as $chat) {
            if($chat['role'] == 'ai') {
                $tam_rapor .= $chat['message'] . "\n\n";
            }
        }
        $sorgu = $db->prepare("INSERT INTO ai_diagnostics (user_id, issue_description, ai_solution) VALUES (?, ?, ?)");
        $sorgu->execute([$uid, $ilk_soru, trim($tam_rapor)]);
    }
    unset($_SESSION['chat_history']);
    header("Location: gecmis.php");
    exit();
}

function formatliYaz($metin) {
    $metin = htmlspecialchars($metin);
    $metin = preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-blue-400">$1</strong>', $metin);
    return nl2br($metin);
}

$ai_avatar_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-full h-full text-white p-1.5"><path d="M12 2C12.5523 2 13 2.44772 13 3V5.00843C15.8252 5.51868 18.0673 7.57597 18.8258 10.334L20.6276 9.29307L21.6276 11.0251L19.4975 12.2547C19.8273 13.5658 19.4674 15.0051 18.5281 16.0371L20.2602 17.0371L19.2602 18.7692L17.2721 17.6212C15.9388 18.767 14.0722 19.2312 12.25 18.8147V21C12.25 21.5523 11.8023 22 11.25 22C10.6977 22 10.25 21.5523 10.25 21V18.8147C8.42777 19.2312 6.56116 18.767 5.22788 17.6212L3.23979 18.7692L2.23979 17.0371L3.97194 16.0371C3.03264 15.0051 2.67268 13.5658 3.00252 12.2547L0.872412 11.0251L1.87241 9.29307L3.67425 10.334C4.43265 7.57597 6.67484 5.51868 9.5 5.00843V3C9.5 2.44772 9.94772 2 10.5 2H12ZM11.25 7C8.62665 7 6.5 9.12665 6.5 11.75C6.5 14.3734 8.62665 16.5 11.25 16.5C13.8734 16.5 16 14.3734 16 11.75C16 9.12665 13.8734 7 11.25 7ZM11.25 9.5C12.4926 9.5 13.5 10.5074 13.5 11.75C13.5 12.9926 12.4926 14 11.25 14C10.0074 14 9 12.9926 9 11.75C9 10.5074 10.0074 9.5 11.25 9.5Z"></path></svg>';
?>

<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OtoAI | Canlı Analiz Asistanı</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/dist/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700;900&display=swap" rel="stylesheet">
    <script>
        let theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    </script>
    <style>
        :root { --bg-gradient: radial-gradient(circle at top right, #e2e8f0, #f8fafc, #ffffff); --text-main: #0f172a; --text-muted: #64748b; --glass-bg: rgba(255, 255, 255, 0.85); --glass-border: rgba(0, 0, 0, 0.05); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05); --input-bg: rgba(255, 255, 255, 0.95); --nav-bg: rgba(255, 255, 255, 0.95); --grid-color: rgba(0, 0, 0, 0.03); }
        [data-theme="dark"] { --bg-gradient: radial-gradient(circle at top right, #1e40af, #0f172a, #030712); --text-main: #f8fafc; --text-muted: #94a3b8; --glass-bg: rgba(15, 23, 42, 0.85); --glass-border: rgba(255, 255, 255, 0.08); --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); --input-bg: rgba(0, 0, 0, 0.6); --nav-bg: rgba(3, 7, 18, 0.95); --grid-color: rgba(255, 255, 255, 0.02); }
        body { font-family: 'Space Grotesk', sans-serif; background: var(--bg-gradient); background-attachment: fixed; color: var(--text-main); height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        body::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: linear-gradient(var(--grid-color) 1px, transparent 1px), linear-gradient(90deg, var(--grid-color) 1px, transparent 1px); background-size: 40px 40px; z-index: -1; pointer-events: none; }
        .glass { background: var(--glass-bg); border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); backdrop-filter: blur(15px); }
        .nav-glass { background: var(--nav-bg); border-bottom: 1px solid var(--glass-border); backdrop-filter: blur(15px); }
        .bubble-user { background: #3b82f6; color: white; border-bottom-right-radius: 0; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4); }
        .bubble-ai { background: var(--glass-bg); color: var(--text-main); border: 1px solid var(--glass-border); border-bottom-left-radius: 0; box-shadow: var(--glass-shadow); line-height: 1.7; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
    </style>
</head>
<body>

    <nav class="nav-glass px-6 py-4 flex-none z-50 relative">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 flex items-center justify-center shadow-lg shrink-0 border border-blue-500/50">
                    <?php echo $ai_avatar_svg; ?>
                </div>
                <div>
                    <h1 class="font-black tracking-tighter uppercase italic text-[var(--text-main)] leading-none">Oto<span class="text-blue-500">AI</span> <span class="text-xs tracking-widest font-bold ml-1 opacity-50">ASİSTAN</span></h1>
                    <p class="text-[10px] text-green-500 font-bold uppercase tracking-widest flex items-center gap-1 mt-1">
                        <svg class="w-2 h-2 animate-pulse fill-current" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"></circle></svg> Gemini Aktif
                    </p>
                </div>
            </div>
            <div class="flex gap-4">
                <a href="sohbet.php?bitir=1" onclick="return confirm('Sohbeti bitirip geçmişe kaydetmek istiyor musun?');" class="bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4H8m0 0l3 3m-3-3l3-3"></path></svg> Bitir ve Kaydet
                </a>
            </div>
        </div>
    </nav>

    <main id="chat-container" class="flex-grow max-w-5xl w-full mx-auto p-4 md:p-6 overflow-y-auto flex flex-col gap-6 pb-32">
        
        <?php if(empty($_SESSION['chat_history'])): ?>
            <div class="flex items-end gap-3 max-w-[85%] md:max-w-[75%]">
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 border border-blue-500/50 flex items-center justify-center shrink-0">
                    <?php echo $ai_avatar_svg; ?>
                </div>
                <div class="bubble-ai p-4 md:p-5 rounded-3xl text-sm md:text-base">
                    Merhaba <?php echo htmlspecialchars($_SESSION['username']); ?>. Ben OtoAI. Lütfen sorunu yazın, veri tabanlarını tarayalım.
                </div>
            </div>
        <?php else: ?>
            <?php foreach($_SESSION['chat_history'] as $chat): ?>
                <?php if($chat['role'] == 'user'): ?>
                    <div class="flex flex-col items-end w-full">
                        <div class="bubble-user p-4 md:p-5 rounded-3xl text-sm md:text-base max-w-[85%] md:max-w-[75%]">
                            <?php echo htmlspecialchars($chat['message']); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-end gap-3 max-w-[85%] md:max-w-[75%]">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 border border-blue-500/50 flex items-center justify-center shrink-0 shadow-md">
                            <?php echo $ai_avatar_svg; ?>
                        </div>
                        <div class="bubble-ai p-4 md:p-5 rounded-3xl text-sm md:text-base">
                            <?php echo formatliYaz($chat['message']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <div id="ai-typing" class="hidden items-end gap-3 max-w-[85%] md:max-w-[75%]">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 border border-blue-500/50 flex items-center justify-center shrink-0 shadow-md animate-pulse">
                <?php echo $ai_avatar_svg; ?>
            </div>
            <div class="bubble-ai p-4 rounded-3xl flex items-center gap-2">
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></span>
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
            </div>
        </div>

    </main>

    <div class="glass flex-none p-4 pb-8 md:pb-6 relative z-50 border-t border-[var(--glass-border)]">
        <div class="max-w-5xl mx-auto">
            <form action="sohbet.php" method="POST" class="relative flex items-center" onsubmit="gonderiliyorEfekti()">
                <input type="text" name="yeni_mesaj" required autocomplete="off" placeholder="Sorunu detaylandır veya maliyetini sor..." 
                       class="w-full bg-[var(--input-bg)] border border-[var(--glass-border)] text-[var(--text-main)] rounded-full pl-6 pr-16 py-4 focus:outline-none">
                <button type="submit" id="gonderBtn" class="absolute right-2 top-1/2 -translate-y-1/2 w-11 h-11 bg-blue-600 hover:bg-blue-500 text-white rounded-full flex items-center justify-center transition-transform pr-0.5">
                    <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                </button>
            </form>
            <p class="text-center text-[10px] text-[var(--text-muted)] mt-3 uppercase font-bold tracking-widest">İşlemi yapmadan önce her zaman bir ustaya danışın.</p>
        </div>
    </div>

    <script>
        const chatContainer = document.getElementById('chat-container');
        chatContainer.scrollTop = chatContainer.scrollHeight;

        function gonderiliyorEfekti() {
            document.getElementById('gonderBtn').innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            document.getElementById('ai-typing').classList.remove('hidden');
            document.getElementById('ai-typing').classList.add('flex');
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    </script>
</body>
</html>