<?php
session_start();
include 'baglan.php';

// Güvenlik: Admin değilse burayı görmesin[cite: 4]
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Tüm kullanıcıları çekiyoruz
$users = $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetim Paneli | OtoAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-10">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <h1 class="text-3xl font-black text-red-500 uppercase italic">Admin Yönetim Paneli</h1>
        </div>
        
        <div class="bg-slate-800 rounded-3xl overflow-hidden shadow-2xl border border-white/5">
            <table class="w-full text-left">
                <thead class="bg-slate-700 text-gray-400 uppercase text-xs">
                    <tr>
                        <th class="p-6">Kullanıcı Adı</th>
                        <th class="p-6">Mevcut Rol</th>
                        <th class="p-6">Yetki Değiştir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td class="p-6 font-bold"><?php echo $u['username']; ?></td>
                            <td class="p-6">
                                <span class="px-3 py-1 rounded-full text-xs font-bold 
                                    <?php echo $u['role'] == 'admin' ? 'bg-red-500/20 text-red-400' : ($u['role'] == 'usta' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400'); ?>">
                                    <?php echo strtoupper($u['role']); ?>
                                </span>
                            </td>
                            <td class="p-6 flex gap-2">
                                <a href="islem_rol_degis.php?id=<?php echo $u['id']; ?>&rol=usta" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    Usta Yap
                                </a>
                                <a href="islem_rol_degis.php?id=<?php echo $u['id']; ?>&rol=user" class="bg-slate-600 hover:bg-slate-500 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    User Yap
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <br><a href="index.php" class="text-gray-500 hover:text-white flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Siteye Dön
        </a>
    </div>
</body>
</html>