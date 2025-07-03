<?php
session_start();
require_once 'config.php';

// Session kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// AJAX işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // AJAX için session kontrolü
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Oturum süresi dolmuş. Lütfen tekrar giriş yapın.']);
        exit();
    }
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'deposit') {
        $deposit_type = $_POST['deposit_type'] ?? '';
        $amount = floatval($_POST['amount']);
        
        // Validasyon kontrolleri
        if (empty($deposit_type)) {
            echo json_encode(['success' => false, 'message' => 'Please select a deposit method!']);
            exit();
        }
        
        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid amount!']);
            exit();
        }
        
        try {
            if ($deposit_type === 'bank') {
                // Banka bilgilerini al
                $bank_name = trim($_POST['bank_name']);
                $account_name = trim($_POST['account_name']);
                $reference_number = trim($_POST['reference_number']);
                
                if (empty($bank_name) || empty($account_name)) {
                    echo json_encode(['success' => false, 'message' => 'Please fill in all bank details!']);
                    exit();
                }
                
                // Wallet transaction kaydet (Pending olarak)
                $sql = "INSERT INTO wallet_transactions (user_id, transaction_type, amount, usd_amount, payment_method, bank_name, account_name, reference_number, status, crypto_symbol, crypto_name) 
                        VALUES (?, 'Deposit', ?, ?, 'Bank Transfer', ?, ?, ?, 'Pending', NULL, NULL)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id, $amount, $amount, $bank_name, $account_name, $reference_number]);
                
            } else if ($deposit_type === 'crypto') {
                $crypto_symbol = 'USDT'; // Sadece USDT kabul ediyoruz
                $crypto_address = trim($_POST['crypto_address']);
                
                if (empty($crypto_address)) {
                    echo json_encode(['success' => false, 'message' => 'Please enter your wallet address!']);
                    exit();
                }
                
                // Kripto fiyatını al
                $sql = "SELECT current_price, asset_name FROM assets WHERE asset_symbol = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$crypto_symbol]);
                $asset = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$asset) {
                    echo json_encode(['success' => false, 'message' => 'Unsupported cryptocurrency!']);
                    exit();
                }
                
                $usd_amount = $amount; // USDT için miktar = USD değeri
                
                // Wallet transaction kaydet (Pending olarak)
                $sql = "INSERT INTO wallet_transactions (user_id, crypto_symbol, crypto_name, transaction_type, amount, usd_amount, crypto_address, payment_method, status) 
                        VALUES (?, ?, ?, 'Deposit', ?, ?, ?, 'Crypto', 'Pending')";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id, $crypto_symbol, $asset['asset_name'], $amount, $usd_amount, $crypto_address]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid deposit method!']);
                exit();
            }
            
            // Transaction ID'yi al
            $transaction_id = $conn->lastInsertId();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Your deposit request has been received! Awaiting approval.',
                'transaction_id' => $transaction_id
            ]);
            exit();
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    if ($action === 'withdraw') {
        $withdraw_type = $_POST['withdraw_type'] ?? '';
        $amount = floatval($_POST['amount']);
        
        // Validasyon kontrolleri
        if (empty($withdraw_type)) {
            echo json_encode(['success' => false, 'message' => 'Please select a withdrawal method!']);
            exit();
        }
        
        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid amount!']);
            exit();
        }
        
        try {
            if ($withdraw_type === 'bank') {
                // Banka bilgilerini al
                $bank_name = trim($_POST['bank_name']);
                $account_name = trim($_POST['account_name']);
                $iban = trim($_POST['iban']);
                
                if (empty($bank_name) || empty($account_name) || empty($iban)) {
                    echo json_encode(['success' => false, 'message' => 'Please fill in all bank details!']);
                    exit();
                }
                
                // Wallet transaction kaydet (Pending olarak)
                $sql = "INSERT INTO wallet_transactions (user_id, transaction_type, amount, usd_amount, payment_method, bank_name, account_name, iban, status, crypto_symbol, crypto_name) 
                        VALUES (?, 'Withdrawal', ?, ?, 'Bank Transfer', ?, ?, ?, 'Pending', NULL, NULL)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id, $amount, $amount, $bank_name, $account_name, $iban]);
                
            } else if ($withdraw_type === 'crypto') {
                $crypto_symbol = 'USDT'; // Sadece USDT kabul ediyoruz
                $crypto_address = trim($_POST['crypto_address']);
                
                if (empty($crypto_address)) {
                    echo json_encode(['success' => false, 'message' => 'Please enter the crypto address!']);
                    exit();
                }
                
                // Kullanıcının bakiyesini kontrol et
                $sql = "SELECT balance FROM user_wallets WHERE user_id = ? AND crypto_symbol = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id, $crypto_symbol]);
                $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($wallet && $wallet['balance'] >= $amount) {
                    // Kripto fiyatını al
                    $sql = "SELECT current_price, asset_name FROM assets WHERE asset_symbol = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$crypto_symbol]);
                    $asset = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($asset) {
                        $usd_amount = $amount; // USDT için miktar = USD değeri
                        
                        // Wallet transaction kaydet (Pending olarak)
                        $sql = "INSERT INTO wallet_transactions (user_id, crypto_symbol, crypto_name, transaction_type, amount, usd_amount, crypto_address, payment_method, status) 
                                VALUES (?, ?, ?, 'Withdrawal', ?, ?, ?, 'Crypto', 'Pending')";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user_id, $crypto_symbol, $asset['asset_name'], $amount, $usd_amount, $crypto_address]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Unsupported cryptocurrency!']);
                        exit();
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Insufficient balance!']);
                    exit();
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid withdrawal method!']);
                exit();
            }
            
            // Transaction ID'yi al
            $transaction_id = $conn->lastInsertId();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Your withdrawal request has been received! Awaiting approval.',
                'transaction_id' => $transaction_id
            ]);
            exit();
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    if ($action === 'confirm_deposit') {
        $transaction_id = intval($_POST['transaction_id']);
        
        try {
            // Transaction bilgilerini al
            $sql = "SELECT * FROM wallet_transactions WHERE id = ? AND user_id = ? AND status = 'Pending'";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$transaction_id, $user_id]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($transaction) {
                // Wallet'a ekle
                $sql = "INSERT INTO user_wallets (user_id, crypto_symbol, crypto_name, balance, usd_equivalent) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE balance = balance + ?, usd_equivalent = usd_equivalent + ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $user_id, 
                    $transaction['crypto_symbol'], 
                    $transaction['crypto_name'], 
                    $transaction['amount'], 
                    $transaction['usd_amount'],
                    $transaction['amount'], 
                    $transaction['usd_amount']
                ]);
                
                // Transaction'ı completed olarak güncelle
                $sql = "UPDATE wallet_transactions SET status = 'Completed' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$transaction_id]);
                
                echo json_encode(['success' => true, 'message' => 'İşlem onaylandı! Bakiyeniz güncellendi.']);
                exit();
            }
            
            echo json_encode(['success' => false, 'message' => 'İşlem bulunamadı!']);
            exit();
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }

    // Para Yatırma ve Çekme Formları

    // Para Yatırma Formu
    if ($action === 'deposit_form') {
        echo '<form id="depositForm">';
        echo '<label for="depositType">Para Yatırma Türü:</label>';
        echo '<select id="depositType" name="depositType">';
        echo '<option value="bank">Banka Havalesi</option>';
        echo '<option value="crypto">Kripto</option>';
        echo '</select>';
        echo '<label for="amount">Miktar:</label>';
        echo '<input type="number" id="amount" name="amount" required>';
        echo '<label for="cryptoAddress">Kripto Adresi (Kripto seçildiğinde):</label>';
        echo '<input type="text" id="cryptoAddress" name="cryptoAddress">';
        echo '<button type="submit">Gönder</button>';
        echo '</form>';
    }

    // Para Çekme Formu
    if ($action === 'withdraw_form') {
        echo '<form id="withdrawForm">';
        echo '<label for="withdrawType">Para Çekme Türü:</label>';
        echo '<select id="withdrawType" name="withdrawType">';
        echo '<option value="bank">Banka Havalesi</option>';
        echo '<option value="crypto">Kripto</option>';
        echo '</select>';
        echo '<label for="amount">Miktar:</label>';
        echo '<input type="number" id="amount" name="amount" required>';
        echo '<label for="cryptoAddress">Kripto Adresi (Kripto seçildiğinde):</label>';
        echo '<input type="text" id="cryptoAddress" name="cryptoAddress">';
        echo '<button type="submit">Gönder</button>';
        echo '</form>';
    }
}

// Kullanıcı bilgilerini getir
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kullanıcı bilgilerini session'a kaydet
if($user) {
    $_SESSION['user_name'] = $user['name'] . ' ' . $user['surname'];
    $_SESSION['user_email'] = $user['email'];
}

// Kullanıcının wallet bakiyelerini getir
$user_wallets = [];
$total_balance = 0;
$free_margin = 0;
$active_positions = 0;
$recent_transactions = [];

try {
    // Wallet bilgilerini getir
    $sql = "SELECT uw.*, a.current_price, a.icon_class FROM user_wallets uw 
            LEFT JOIN assets a ON uw.crypto_symbol = a.asset_symbol 
            WHERE uw.user_id = ? AND uw.balance > 0 
            ORDER BY uw.usd_equivalent DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $user_wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pending durumdaki işlemleri getir
    $sql = "SELECT wt.*, a.icon_class 
            FROM wallet_transactions wt
            LEFT JOIN assets a ON wt.crypto_symbol = a.asset_symbol 
            WHERE wt.user_id = ? AND wt.status = 'Pending' 
            ORDER BY wt.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $pending_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Completed durumdaki işlemlerin toplam miktarını hesapla
    $sql = "SELECT SUM(usd_amount) as total_completed_amount 
            FROM wallet_transactions 
            WHERE user_id = ? AND status = 'Completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $completed_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_completed_amount = $completed_result ? $completed_result['total_completed_amount'] : 0;

    // Toplam bakiye hesapla - artık wallet_transactions tablosundan alıyoruz
    $total_balance = $total_completed_amount;
    
    // Açık pozisyonları getir
    $sql = "SELECT COUNT(*) as active_count FROM positions WHERE user_id = ? AND status = 'Open'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $positions = $stmt->fetch(PDO::FETCH_ASSOC);
    $active_positions = $positions ? $positions['active_count'] : 0;
    
    // Free margin (işlem yapılabilir bakiye) hesapla
    // Burada örnek olarak toplam bakiyeden açık pozisyonlar için ayrılan tutarı çıkarıyoruz
    $sql = "SELECT SUM(amount) as used_margin FROM positions WHERE user_id = ? AND status = 'Open'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $margin_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $used_margin = $margin_result ? $margin_result['used_margin'] : 0;
    
    $free_margin = $total_balance - $used_margin;
    if ($free_margin < 0) $free_margin = 0;

    // Son wallet işlemlerini getir - basitleştirilmiş sorgu
    try {
        // Doğrudan SQL sorgusu ile verileri çek
        $sql = "SELECT * FROM wallet_transactions ORDER BY created_at DESC LIMIT 10";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug bilgisi
        error_log("SQL: $sql");
        error_log("Bulunan işlem sayısı: " . count($recent_transactions));
    } catch (PDOException $e) {
        error_log("Wallet transactions sorgu hatası: " . $e->getMessage());
        $recent_transactions = [];
    }
} catch (PDOException $e) {
    // Tablo yoksa boş array'ler kullan
    error_log("Genel veritabanı hatası: " . $e->getMessage());
    $user_wallets = [];
    $total_balance = 0;
    $free_margin = 0;
    $active_positions = 0;
    $recent_transactions = [];
}

// Desteklenen kriptolar
$supported_cryptos = [
    'BTC' => 'Bitcoin',
    'ETH' => 'Ethereum', 
    'USDT' => 'Tether',
    'SOL' => 'Solana',
    'ADA' => 'Cardano',
    'XRP' => 'Ripple'
];

$page_title = "Wallet";
$page_description = "Manage your cryptocurrency wallet on İstanbulBorsa. Deposit, withdraw and trade with your crypto balance.";

// Dashboard benzeri CSS stilleri
$page_specific_css = '<style>
    /* Dashboard specific CSS variables */
    :root {
        --color-bg-card: #ffffff;
        --color-border: #e0e0e0;
        --color-bg-hover: #f5f5f5;
        --color-text-muted: #666666;
        --color-success: #00B894;
        --color-danger: #D63031;
        --color-warning: #FDCB6E;
        --color-bg: #f8f9fa;
        --sidebar-bg: #ffffff;
        --sidebar-text: #2D3436;
        --sidebar-hover: #f8f9fa;
        --sidebar-active: #0984E3;
    }
    
    body.dark-theme {
        --color-bg-card: #343A40;
        --color-border: #495057;
        --color-bg-hover: #495057;
        --color-text-muted: #CED4DA;
        --color-bg: #212529;
        --sidebar-bg: #1e2124;
        --sidebar-text: #ffffff;
        --sidebar-hover: #36393f;
        --sidebar-active: #0984E3;
    }
    
    /* Dashboard Layout */
    .dashboard-container {
        display: flex;
        min-height: calc(100vh - 80px);
        background-color: var(--color-bg);
        margin-top: 0;
        transition: all 0.3s ease;
    }
    
    .header {
        display: block !important;
        position: relative;
        z-index: 1001;
    }
    
    .sidebar {
        width: 260px;
        background-color: var(--sidebar-bg) !important;
        border-right: 2px solid var(--color-border);
        transition: all 0.3s ease;
        padding: 20px 0;
        position: relative;
        box-shadow: 4px 0 15px rgba(0, 0, 0, 0.15);
        z-index: 100;
    }
    
    .sidebar.collapsed {
        width: 80px;
    }
    
    .sidebar.collapsed .user-details,
    .sidebar.collapsed .nav-item span {
        display: none;
    }
    
    .sidebar.collapsed .sidebar-header {
        justify-content: center;
    }
    
    .sidebar.collapsed .nav-item a {
        justify-content: center;
        padding: 15px 10px;
    }
    
    .sidebar.collapsed .nav-item i {
        margin-right: 0;
    }
    
    .sidebar-header {
        padding: 0 20px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid var(--color-border);
        margin-bottom: 20px;
        background: linear-gradient(135deg, var(--sidebar-bg) 0%, rgba(9, 132, 227, 0.05) 100%);
    }
    
    .user-info {
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--color-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .user-details {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    
    .user-name {
        font-weight: 600;
        font-size: 16px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--sidebar-text) !important;
    }
    
    .user-email {
        font-size: 12px;
        color: var(--sidebar-text) !important;
        opacity: 0.7;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .sidebar-toggle {
        background: none;
        border: none;
        color: var(--sidebar-text) !important;
        font-size: 20px;
        cursor: pointer;
        padding: 5px;
        margin-left: 10px;
        flex-shrink: 0;
    }
    
    .nav-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .nav-item {
        margin-bottom: 5px;
    }
    
    .nav-item a {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        color: var(--sidebar-text) !important;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 15px;
        border-radius: 0 25px 25px 0;
        margin: 2px 0;
        margin-right: 15px;
        position: relative;
    }
    
    .nav-item a:hover {
        background-color: var(--sidebar-hover) !important;
        color: var(--sidebar-active) !important;
        font-weight: 600;
        transform: translateX(5px);
    }
    
    .nav-item.active a {
        background-color: var(--sidebar-active) !important;
        color: #ffffff !important;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(9, 132, 227, 0.3);
    }
    
    .nav-item i {
        margin-right: 15px;
        width: 20px;
        text-align: center;
        flex-shrink: 0;
        font-size: 16px;
    }
    
    .main-content {
        flex: 1;
        padding: 30px;
        background-color: var(--color-bg);
        overflow-x: auto;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
    }
    
    .header-actions {
        display: flex;
        gap: 10px;
    }
    
    /* Wallet Cards */
    .wallet-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .wallet-card {
        background-color: var(--color-bg-card);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--color-border);
    }
    
    .card-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 15px;
        color: white;
    }
    
    .card-icon.primary { background-color: var(--color-primary); }
    .card-icon.success { background-color: var(--color-success); }
    .card-icon.warning { background-color: var(--color-warning); }
    
    .card-label {
        font-size: 14px;
        color: var(--color-text-muted);
        margin-bottom: 5px;
    }
    
    .card-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    /* Wallet Actions */
    .wallet-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .action-card {
        background-color: var(--color-bg-card);
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--color-border);
    }
    
    .action-card h3 {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--sidebar-text);
    }
    
    .form-group select,
    .form-group input {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        font-size: 14px;
        background-color: var(--color-bg-card);
        color: var(--sidebar-text);
    }
    
    .form-group select:focus,
    .form-group input:focus {
        outline: none;
        border-color: var(--color-primary);
    }
    
    /* Wallet Balance Table */
    .wallet-section {
        background-color: var(--color-bg-card);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--color-border);
        overflow-x: auto;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .section-header h2 {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }
    
    .wallet-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    
    .wallet-table th, .wallet-table td {
        padding: 15px 10px;
        text-align: left;
        border-bottom: 1px solid var(--color-border);
    }
    
    .wallet-table th {
        font-weight: 600;
        color: var(--color-text-muted);
        font-size: 14px;
    }
    
    .crypto-info {
        display: flex;
        align-items: center;
        min-width: 120px;
    }
    
    .crypto-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: var(--color-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
        font-size: 14px;
    }
    
    .crypto-name {
        font-weight: 600;
        margin-right: 5px;
    }
    
    .crypto-symbol {
        color: var(--color-text-muted);
        font-size: 12px;
    }
    
    /* Transaction Table */
    .transaction-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    
    .transaction-table th, .transaction-table td {
        padding: 15px 10px;
        text-align: left;
        border-bottom: 1px solid var(--color-border);
    }
    
    .transaction-table th {
        font-weight: 600;
        color: var(--color-text-muted);
        font-size: 14px;
    }
    
    .transaction-date {
        font-weight: 600;
        font-size: 14px;
    }
    
    .transaction-id {
        font-size: 12px;
        color: var(--color-text-muted);
    }
    
    .transaction-type {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .transaction-type.deposit {
        background-color: rgba(0, 184, 148, 0.1);
        color: var(--color-success);
    }
    
    .transaction-type.withdrawal {
        background-color: rgba(214, 48, 49, 0.1);
        color: var(--color-danger);
    }
    
    .transaction-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .transaction-status.completed {
        background-color: rgba(0, 184, 148, 0.1);
        color: var(--color-success);
    }
    
    .transaction-status.pending {
        background-color: rgba(253, 203, 110, 0.1);
        color: var(--color-warning);
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    /* Alert Messages */
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .alert.success {
        background-color: rgba(0, 184, 148, 0.1);
        color: var(--color-success);
        border: 1px solid rgba(0, 184, 148, 0.2);
    }
    
    .alert.error {
        background-color: rgba(214, 48, 49, 0.1);
        color: var(--color-danger);
        border: 1px solid rgba(214, 48, 49, 0.2);
    }

    /* Dashboard kullanıcı menüsü özel stilleri */
    .dropdown-menu {
        position: relative;
        display: inline-block;
    }
    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% - 2px);
        background-color: var(--sidebar-bg, #fff) !important;
        min-width: 180px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1000;
        border-radius: 8px;
        border: 1px solid var(--color-border, #e0e0e0);
        margin-top: 0px;
        padding-top: 7px;
        flex-direction: column;
    }
    .dropdown-content a {
        color: var(--sidebar-text, #2D3436) !important;
        padding: 12px 16px;
        text-decoration: none;
        display: flex;
        align-items: center;
        font-size: 14px;
        transition: background-color 0.3s ease;
    }
    .dropdown-content a i {
        margin-right: 8px;
        width: 16px;
    }
    .dropdown-content a:hover {
        background-color: var(--sidebar-hover, #f8f9fa) !important;
    }
    .dropdown-content a:first-child {
        border-radius: 8px 8px 0 0;
    }
    .dropdown-content a:last-child {
        border-radius: 0 0 8px 8px;
    }
    .dropdown-content.show {
        display: flex;
    }
    .dropdown-menu:hover .dropdown-content,
    .dropdown-content:hover {
        display: block;
    }
    .dropdown-menu::before {
        content: "";
        position: absolute;
        top: 100%;
        right: 0;
        width: 100%;
        height: 10px;
        background: transparent;
        z-index: 999;
    }
    .user-profile {
        display: flex;
        align-items: center;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }
    .user-profile:hover {
        background-color: var(--sidebar-hover, #f8f9fa);
    }
    .user-profile .profile-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: var(--color-primary, #2D3436);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
        font-weight: 600;
        font-size: 14px;
    }
    .user-profile .user-name {
        font-weight: 600;
        margin-right: 10px;
        color: var(--sidebar-text, #2D3436) !important;
    }
</style>';

include 'header.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini getir
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user) {
    $_SESSION['user_name'] = $user['name'] . ' ' . $user['surname'];
    $_SESSION['user_email'] = $user['email'];
}

?>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        $initials = "";
                        $name_parts = explode(" ", $_SESSION['user_name']);
                        foreach($name_parts as $part) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                        echo $initials;
                        ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['user_name']; ?></div>
                        <div class="user-email"><?php echo $_SESSION['user_email']; ?></div>
                    </div>
                </div>
                <button class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="trading.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Trade</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="markets.php">
                        <i class="fas fa-coins"></i>
                        <span>Markets</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="wallet.php">
                        <i class="fas fa-wallet"></i>
                        <span>Wallet</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-history"></i>
                        <span>Transaction History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-user-circle"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Wallet</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="refreshBalances()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Wallet Summary Cards -->
            <div class="wallet-cards">
                <div class="wallet-card">
                    <div class="card-icon primary">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="card-label">Total Completed Balance</div>
                    <div class="card-value">$<?php echo number_format($total_balance, 2); ?></div>
                </div>
                <div class="wallet-card">
                    <div class="card-icon success">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="card-label">Active Positions</div>
                    <div class="card-value"><?php echo $active_positions; ?></div>
                </div>
                <div class="wallet-card">
                    <div class="card-icon warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-label">Available for Trading</div>
                    <div class="card-value">$<?php echo number_format($free_margin, 2); ?></div>
                </div>
            </div>

            <!-- Wallet Actions -->
            <div class="wallet-actions">
                <!-- Deposit Form -->
                <div class="action-card">
                    <h3><i class="fas fa-plus-circle"></i> Deposit Funds</h3>
                    <form id="depositForm">
                        <div class="form-group">
                            <label for="deposit_type">Deposit Method</label>
                            <select id="deposit_type" name="deposit_type" required onchange="toggleDepositFields()">
                                <option value="">Select method...</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="crypto">Cryptocurrency</option>
                            </select>
                        </div>
                        
                        <!-- Banka Havalesi için alanlar -->
                        <div id="bank_deposit_fields" style="display:none;">
                            <div class="form-group">
                                <label for="bank_name">Bank Name</label>
                                <input type="text" id="bank_name" name="bank_name" placeholder="Bank you transferred from">
                            </div>
                            <div class="form-group">
                                <label for="account_name">Account Name</label>
                                <input type="text" id="account_name" name="account_name" placeholder="Account holder name">
                            </div>
                            <div class="form-group">
                                <label for="reference_number">Reference/Receipt No</label>
                                <input type="text" id="reference_number" name="reference_number" placeholder="Transfer reference number">
                            </div>
                            <div class="form-group">
                                <label for="bank_amount">Amount (USD)</label>
                                <input type="number" id="bank_amount" name="amount" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        
                        <!-- Kripto için alanlar -->
                        <div id="crypto_deposit_fields" style="display:none;">
                            <div class="form-group">
                                <label for="deposit_crypto">Cryptocurrency</label>
                                <select id="deposit_crypto" name="crypto_symbol">
                                    <option value="USDT">Tether (USDT)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="deposit_amount">Amount</label>
                                <input type="number" id="deposit_amount" name="amount" step="0.00000001" min="0" placeholder="0.00000000">
                            </div>
                            <div class="form-group">
                                <label for="deposit_address">Sender Wallet Address</label>
                                <input type="text" id="deposit_address" name="crypto_address" placeholder="Wallet address you sent crypto from">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Deposit
                        </button>
                    </form>
                </div>

                <!-- Withdraw Form -->
                <div class="action-card">
                    <h3><i class="fas fa-minus-circle"></i> Withdraw Funds</h3>
                    <form id="withdrawForm">
                        <div class="form-group">
                            <label for="withdraw_type">Withdrawal Method</label>
                            <select id="withdraw_type" name="withdraw_type" required onchange="toggleWithdrawFields()">
                                <option value="">Select method...</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="crypto">Cryptocurrency</option>
                            </select>
                        </div>
                        
                        <!-- Banka Havalesi için alanlar -->
                        <div id="bank_withdraw_fields" style="display:none;">
                            <div class="form-group">
                                <label for="withdraw_bank_name">Bank Name</label>
                                <input type="text" id="withdraw_bank_name" name="bank_name" placeholder="Bank to receive funds">
                            </div>
                            <div class="form-group">
                                <label for="withdraw_account_name">Account Name</label>
                                <input type="text" id="withdraw_account_name" name="account_name" placeholder="Account holder name">
                            </div>
                            <div class="form-group">
                                <label for="iban">IBAN/Account Number</label>
                                <input type="text" id="iban" name="iban" placeholder="Your bank account number or IBAN">
                            </div>
                            <div class="form-group">
                                <label for="withdraw_bank_amount">Amount (USD)</label>
                                <input type="number" id="withdraw_bank_amount" name="amount" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        
                        <!-- Kripto için alanlar -->
                        <div id="crypto_withdraw_fields" style="display:none;">
                            <div class="form-group">
                                <label for="withdraw_crypto">Cryptocurrency</label>
                                <select id="withdraw_crypto" name="crypto_symbol">
                                    <option value="USDT">Tether (USDT)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="withdraw_amount">Amount</label>
                                <input type="number" id="withdraw_amount" name="amount" step="0.00000001" min="0" placeholder="0.00000000">
                            </div>
                            <div class="form-group">
                                <label for="withdraw_address">Recipient Wallet Address</label>
                                <input type="text" id="withdraw_address" name="crypto_address" placeholder="Wallet address to send crypto to">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-minus"></i> Withdraw
                        </button>
                    </form>
                </div>
            </div>

            <!-- Wallet Balances -->
            <section class="wallet-section">
                <div class="section-header">
                    <h2>Your Crypto Balances</h2>
                </div>
                <table class="wallet-table">
                    <thead>
                        <tr>
                            <th>Cryptocurrency</th>
                            <th>Balance</th>
                            <th>USD Value</th>
                            <th>Current Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($user_wallets)): ?>
                            <?php foreach ($user_wallets as $wallet): ?>
                                <tr>
                                    <td>
                                        <div class="crypto-info">
                                            <i class="<?php echo htmlspecialchars($wallet['icon_class'] ?? 'fas fa-coins'); ?> crypto-icon"></i>
                                            <span class="crypto-name"><?php echo htmlspecialchars($wallet['crypto_name']); ?></span>
                                            <span class="crypto-symbol"><?php echo htmlspecialchars($wallet['crypto_symbol']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($wallet['balance'], 8); ?> <?php echo $wallet['crypto_symbol']; ?></td>
                                    <td>$<?php echo number_format($wallet['usd_equivalent'], 2); ?></td>
                                    <td>$<?php echo number_format($wallet['current_price'], 2); ?></td>
                                    <td>
                                        <a href="trading.php?symbol=<?php echo $wallet['crypto_symbol']; ?>" class="btn btn-outline btn-sm">Trade</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <!-- Bekleyen İşlemler -->
                            <?php if (!empty($pending_transactions)): ?>
                                <?php foreach ($pending_transactions as $transaction): ?>
                                    <?php if ($transaction['transaction_type'] == 'Deposit' && $transaction['payment_method'] == 'Crypto'): ?>
                                    <tr style="background-color: rgba(253, 203, 110, 0.1);">
                                        <td>
                                            <div class="crypto-info">
                                                <i class="<?php echo htmlspecialchars($transaction['icon_class'] ?? 'fas fa-coins'); ?> crypto-icon"></i>
                                                <span class="crypto-name"><?php echo htmlspecialchars($transaction['crypto_name']); ?></span>
                                                <span class="crypto-symbol"><?php echo htmlspecialchars($transaction['crypto_symbol']); ?></span>
                                                <span class="badge badge-warning" style="margin-left: 10px; background-color: var(--color-warning); color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px;">Pending</span>
                                            </div>
                                        </td>
                                        <td><?php echo number_format($transaction['amount'], 8); ?> <?php echo $transaction['crypto_symbol']; ?></td>
                                        <td>$<?php echo number_format($transaction['usd_amount'], 2); ?></td>
                                        <td>-</td>
                                        <td>
                                            <span class="badge" style="background-color: var(--color-warning); color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Awaiting Approval</span>
                                        </td>
                                    </tr>
                                    <?php elseif ($transaction['transaction_type'] == 'Deposit' && $transaction['payment_method'] == 'Bank Transfer'): ?>
                                    <tr style="background-color: rgba(253, 203, 110, 0.1);">
                                        <td>
                                            <div class="crypto-info">
                                                <i class="fas fa-university crypto-icon"></i>
                                                <span class="crypto-name">Bank Transfer</span>
                                                <span class="crypto-symbol">USD</span>
                                                <span class="badge badge-warning" style="margin-left: 10px; background-color: var(--color-warning); color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px;">Pending</span>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td>$<?php echo number_format($transaction['usd_amount'], 2); ?></td>
                                        <td>-</td>
                                        <td>
                                            <span class="badge" style="background-color: var(--color-warning); color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Awaiting Approval</span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: var(--color-text-muted);">
                                    No crypto balances found. Start by depositing some cryptocurrency.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- Recent Transactions -->
            <section class="wallet-section">
                <div class="section-header">
                    <h2>Recent Wallet Transactions</h2>
                    <a href="#" class="btn btn-secondary btn-sm">View All</a>
                </div>
                
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Method/Currency</th>
                            <th>Amount</th>
                            <th>USD Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_transactions)): ?>
                            <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td>
                                        <div class="transaction-date"><?php echo date('M d, Y', strtotime($transaction['created_at'])); ?></div>
                                        <div class="transaction-id">#<?php echo $transaction['id']; ?></div>
                                    </td>
                                    <td><span class="transaction-type <?php echo strtolower($transaction['transaction_type']); ?>"><?php echo $transaction['transaction_type']; ?></span></td>
                                    <td>
                                    <?php if ($transaction['payment_method'] == 'Crypto'): ?>
                                        <?php echo htmlspecialchars($transaction['crypto_name'] ?: 'Kripto'); ?> (<?php echo $transaction['crypto_symbol'] ?: 'USDT'; ?>)
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($transaction['payment_method'] ?: 'Bank Transfer'); ?>
                                    <?php endif; ?>
                                    </td>
                                    <td>
                                    <?php if ($transaction['payment_method'] == 'Crypto'): ?>
                                        <?php echo number_format($transaction['amount'], 8); ?> <?php echo $transaction['crypto_symbol'] ?: 'USDT'; ?>
                                    <?php else: ?>
                                        $<?php echo number_format($transaction['amount'], 2); ?>
                                    <?php endif; ?>
                                    </td>
                                    <td>$<?php echo number_format($transaction['usd_amount'], 2); ?></td>
                                    <td><span class="transaction-status <?php echo strtolower($transaction['status']); ?>"><?php echo $transaction['status']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--color-text-muted);">
                                    No transactions found. Try making a deposit or withdrawal.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

<script>
// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const dashboardContainer = document.querySelector('.dashboard-container');
    
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        dashboardContainer.classList.toggle('sidebar-collapsed');
        
        // Toggle icon
        const icon = sidebarToggle.querySelector('i');
        if (sidebar.classList.contains('collapsed')) {
            icon.className = 'fas fa-chevron-right';
        } else {
            icon.className = 'fas fa-bars';
        }
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
    
    // Restore sidebar state from localStorage
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        dashboardContainer.classList.add('sidebar-collapsed');
        const icon = sidebarToggle.querySelector('i');
        icon.className = 'fas fa-chevron-right';
    }
});

// Para yatırma türüne göre form alanlarını göster/gizle
function toggleDepositFields() {
    const depositType = document.getElementById('deposit_type').value;
    const bankFields = document.getElementById('bank_deposit_fields');
    const cryptoFields = document.getElementById('crypto_deposit_fields');
    
    if (depositType === 'bank') {
        bankFields.style.display = 'block';
        cryptoFields.style.display = 'none';
    } else if (depositType === 'crypto') {
        bankFields.style.display = 'none';
        cryptoFields.style.display = 'block';
    } else {
        bankFields.style.display = 'none';
        cryptoFields.style.display = 'none';
    }
}

// Para çekme türüne göre form alanlarını göster/gizle
function toggleWithdrawFields() {
    const withdrawType = document.getElementById('withdraw_type').value;
    const bankFields = document.getElementById('bank_withdraw_fields');
    const cryptoFields = document.getElementById('crypto_withdraw_fields');
    
    if (withdrawType === 'bank') {
        bankFields.style.display = 'block';
        cryptoFields.style.display = 'none';
    } else if (withdrawType === 'crypto') {
        bankFields.style.display = 'none';
        cryptoFields.style.display = 'block';
    } else {
        bankFields.style.display = 'none';
        cryptoFields.style.display = 'none';
    }
}

// Alert functions
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    alert.innerHTML = message;
    
    alertContainer.appendChild(alert);
    
    // Remove alert after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Deposit form handler
document.getElementById('depositForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'deposit');
    
    fetch('wallet.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            this.reset();
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('An error occurred during the operation. Please try again.', 'error');
    });
});

// Withdraw form handler
document.getElementById('withdrawForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'withdraw');
    
    fetch('wallet.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            this.reset();
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('An error occurred during the operation. Please try again.', 'error');
    });
});

// Refresh balances
function refreshBalances() {
    location.reload();
}
</script>

<?php include 'footer.php'; ?> 