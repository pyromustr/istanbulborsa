<?php
require_once 'config.php';

echo "<h2>Assets Tablosuna Kripto Para Ekleme</h2>";

$assets_to_insert = [
    ['BTC', 'Bitcoin', 43250.00, 'fab fa-bitcoin'],
    ['ETH', 'Ethereum', 2650.00, 'fab fa-ethereum'],
    ['USDT', 'Tether', 1.00, 'fas fa-dollar-sign'],
    ['SOL', 'Solana', 98.50, 'fas fa-sun'],
    ['ADA', 'Cardano', 0.48, 'fas fa-heart'],
    ['XRP', 'Ripple', 0.52, 'fas fa-water'],
    ['XAUUSD', 'Gold', 2025.00, 'fas fa-coins']
];

try {
    // Önce mevcut kayıtları kontrol et
    $sql = "SELECT COUNT(*) as count FROM assets";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Mevcut asset sayısı: " . $result['count'] . "</p>";
    
    foreach ($assets_to_insert as $asset) {
        $symbol = $asset[0];
        $name = $asset[1];
        $price = $asset[2];
        $icon = $asset[3];
        
        // Asset zaten var mı kontrol et
        $sql = "SELECT COUNT(*) as count FROM assets WHERE asset_symbol = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$symbol]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exists['count'] == 0) {
            // Asset yoksa ekle
            $sql = "INSERT INTO assets (asset_symbol, asset_name, current_price, icon_class) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$symbol, $name, $price, $icon]);
            echo "<p style='color: green;'>✅ $symbol ($name) eklendi - $" . number_format($price, 2) . "</p>";
        } else {
            // Asset varsa güncelle
            $sql = "UPDATE assets SET asset_name = ?, current_price = ?, icon_class = ? WHERE asset_symbol = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $price, $icon, $symbol]);
            echo "<p style='color: blue;'>🔄 $symbol ($name) güncellendi - $" . number_format($price, 2) . "</p>";
        }
    }
    
    echo "<h3>İşlem Tamamlandı!</h3>";
    echo "<p><a href='check_assets.php'>Assets tablosunu kontrol et</a></p>";
    echo "<p><a href='wallet.php'>Wallet sayfasına git</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Hata: " . $e->getMessage() . "</p>";
}
?> 