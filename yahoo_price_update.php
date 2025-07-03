<?php
require_once 'config.php';

function getYahooPrice($symbol) {
    $url = "https://query1.finance.yahoo.com/v7/finance/quote?symbols=" . urlencode($symbol);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $json = curl_exec($ch);
    if (curl_errno($ch)) {
        echo date('Y-m-d H:i:s') . " - cURL hata: " . curl_error($ch) . "\n";
        curl_close($ch);
        return null;
    }
    curl_close($ch);
    if ($json === false) return null;
    $data = json_decode($json, true);
    if (isset($data['quoteResponse']['result'][0]['regularMarketPrice'])) {
        return $data['quoteResponse']['result'][0]['regularMarketPrice'];
    }
    return null;
}

// Tüm hisseleri çek
$sql = "SELECT asset_symbol FROM assets";
$stmt = $conn->prepare($sql);
$stmt->execute();
$symbols = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($symbols as $symbol) {
    // BIST hisseleri için .IS ekle
    $yahooSymbol = $symbol . '.IS';
    $price = getYahooPrice($yahooSymbol);
    if ($price !== null) {
        try {
            $sql = "UPDATE assets SET current_price = ? WHERE asset_symbol = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$price, $symbol]);
            echo date('Y-m-d H:i:s') . " - $symbol fiyatı güncellendi: $price\n";
        } catch (Exception $e) {
            echo date('Y-m-d H:i:s') . " - $symbol için hata: " . $e->getMessage() . "\n";
        }
    } else {
        echo date('Y-m-d H:i:s') . " - $symbol fiyatı alınamadı!\n";
    }
} 