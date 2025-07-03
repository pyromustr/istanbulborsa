<?php
session_start();
require_once '../config.php';

// Sadece admin erişimi için kontrol
// Gerçek uygulamada session kontrolü yapılmalıdır
$is_admin = true; 

// Şimdilik sadece admin erişimi için IP kontrolü yapıyoruz
// Bu kısımı kendi IP adresinize göre değiştirin
$allowed_ips = ['127.0.0.1', '::1', $_SERVER['REMOTE_ADDR']]; // localhost ve kendi IP adresiniz
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    header('Location: ../login.php');
    exit();
}

// AJAX işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Yeni pozisyon açma
    if ($action === 'open_position') {
        $asset_symbol = $_POST['asset_symbol'] ?? '';
        $position_type = $_POST['position_type'] ?? '';
        $open_price = floatval($_POST['open_price'] ?? 0);
        $percentage = floatval($_POST['percentage'] ?? 0);
        $open_date = $_POST['open_date'] ?? date('Y-m-d H:i:s');
        
        // Validasyon
        if (empty($asset_symbol) || empty($position_type) || $open_price <= 0 || $percentage <= 0 || $percentage > 100) {
            echo json_encode(['success' => false, 'message' => 'Invalid input parameters']);
            exit();
        }
        
        try {
            // Admin pozisyonu oluştur
            $sql = "INSERT INTO admin_positions (asset_symbol, position_type, percentage_of_balance, open_date, open_price) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$asset_symbol, $position_type, $percentage, $open_date, $open_price]);
            
            $admin_position_id = $conn->lastInsertId();
            
            // Tüm kullanıcılar için pozisyon aç - status kontrolünü kaldırdık
            $sql = "SELECT u.id FROM users u";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $positions_opened = 0;
            
            foreach ($users as $user) {
                $user_id = $user['id'];
                
                // Kullanıcının wallet_transactions tablosundaki Completed durumundaki işlemlerinin toplamını al
                $sql = "SELECT SUM(usd_amount) as total_completed_amount 
                        FROM wallet_transactions 
                        WHERE user_id = ? AND status = 'Completed'";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id]);
                $completed_result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total_completed_amount = $completed_result ? $completed_result['total_completed_amount'] : 0;
                
                // Eğer kullanıcının tamamlanmış işlemi varsa
                if ($total_completed_amount > 0) {
                    // Kullanıcının bakiyesinin belirli yüzdesi kadar işlem aç
                    $amount = ($total_completed_amount * $percentage) / 100;
                    
                    if ($amount >= 10) { // Minimum işlem tutarı
                        $sql = "INSERT INTO positions (user_id, asset_symbol, position_type, amount, open_date, open_price, admin_position_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user_id, $asset_symbol, $position_type, $amount, $open_date, $open_price, $admin_position_id]);
                        
                        $positions_opened++;
                    }
                }
            }
            
            echo json_encode(['success' => true, 'message' => "Position opened successfully for $positions_opened users"]);
            exit();
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // Pozisyon kapatma
    if ($action === 'close_position') {
        $admin_position_id = intval($_POST['admin_position_id'] ?? 0);
        $close_price = floatval($_POST['close_price'] ?? 0);
        $close_date = $_POST['close_date'] ?? date('Y-m-d H:i:s');
        
        // Validasyon
        if ($admin_position_id <= 0 || $close_price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input parameters']);
            exit();
        }
        
        try {
            // Admin pozisyonunu kapat
            $sql = "UPDATE admin_positions SET status = 'Closed', close_date = ?, close_price = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$close_date, $close_price, $admin_position_id]);
            
            // İlgili kullanıcı pozisyonlarını kapat ve kar/zarar hesapla
            $sql = "SELECT * FROM positions WHERE admin_position_id = ? AND status = 'Open'";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$admin_position_id]);
            $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $positions_closed = 0;
            
            foreach ($positions as $position) {
                // Kar/zarar hesapla
                $profit_loss = 0;
                
                if ($position['position_type'] === 'Buy') {
                    $profit_loss = ($close_price - $position['open_price']) * $position['amount'] / $position['open_price'];
                } else { // Sell
                    $profit_loss = ($position['open_price'] - $close_price) * $position['amount'] / $position['open_price'];
                }
                
                // Pozisyonu kapat
                $sql = "UPDATE positions SET status = 'Closed', close_date = ?, close_price = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$close_date, $close_price, $position['id']]);
                
                // Kullanıcının bakiyesini güncelle - wallet_transactions tablosuna işlem ekle
                if ($profit_loss != 0) {
                    $transaction_type = $profit_loss > 0 ? 'Deposit' : 'Withdrawal';
                    $profit_loss_abs = abs($profit_loss);
                    
                    // Wallet transaction ekle
                    $sql = "INSERT INTO wallet_transactions (user_id, crypto_symbol, crypto_name, transaction_type, amount, usd_amount, payment_method, status) 
                            VALUES (?, 'USDT', 'Tether', ?, ?, ?, 'Crypto', 'Completed')";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$position['user_id'], $transaction_type, $profit_loss_abs, $profit_loss_abs]);
                }
                
                $positions_closed++;
            }
            
            echo json_encode(['success' => true, 'message' => "Position closed successfully for $positions_closed users"]);
            exit();
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
    }
}

// Assets listesini al
$assets = [];
try {
    $sql = "SELECT * FROM assets ORDER BY asset_name";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Admin pozisyonlarını al
$admin_positions = [];
try {
    $sql = "SELECT * FROM admin_positions ORDER BY open_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $admin_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Her pozisyon için güncel fiyatı ve kar/zarar hesapla
    foreach ($admin_positions as &$position) {
        // Güncel fiyatı assets tablosundan al
        $asset_symbol = $position['asset_symbol'];
        $sql = "SELECT current_price FROM assets WHERE asset_symbol = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$asset_symbol]);
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($asset && isset($asset['current_price'])) {
            $current_price = $asset['current_price'];
            $position['current_price'] = $current_price;
            
            // Kar/zarar hesapla
            if ($position['status'] === 'Open') {
                $profit_loss_percentage = 0;
                if ($position['position_type'] === 'Buy') {
                    $profit_loss_percentage = (($current_price - $position['open_price']) / $position['open_price']) * 100;
                } else { // Sell
                    $profit_loss_percentage = (($position['open_price'] - $current_price) / $position['open_price']) * 100;
                }
                
                $position['profit_loss_percentage'] = $profit_loss_percentage;
            } else {
                // Kapalı pozisyonlar için kapanış fiyatı ile kar/zarar hesapla
                $profit_loss_percentage = 0;
                if ($position['position_type'] === 'Buy') {
                    $profit_loss_percentage = (($position['close_price'] - $position['open_price']) / $position['open_price']) * 100;
                } else { // Sell
                    $profit_loss_percentage = (($position['open_price'] - $position['close_price']) / $position['open_price']) * 100;
                }
                
                $position['profit_loss_percentage'] = $profit_loss_percentage;
                $position['current_price'] = $position['close_price']; // Kapalı pozisyonlar için güncel fiyat = kapanış fiyatı
            }
        } else {
            // Eğer assets tablosunda fiyat bulunamazsa
            $position['current_price'] = $position['open_price'];
            $position['profit_loss_percentage'] = 0;
        }
    }
    
} catch (PDOException $e) {
    // Tablo yoksa boş array kullan
    $admin_positions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trades - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #0984e3;
            --secondary: #2d3436;
            --success: #00b894;
            --danger: #d63031;
            --warning: #fdcb6e;
            --light: #f5f6fa;
            --dark: #2d3436;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            color: #2d3436;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #0984e3;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        h1, h2, h3 {
            margin-bottom: 15px;
        }
        
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        button {
            background-color: #0984e3;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #0673c5;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #00b894;
            color: white;
        }
        
        .badge-danger {
            background-color: #d63031;
            color: white;
        }
        
        .badge-primary {
            background-color: #0984e3;
            color: white;
        }
        
        .badge-secondary {
            background-color: #636e72;
            color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
        
        .btn-danger {
            background-color: #d63031;
        }
        
        .btn-danger:hover {
            background-color: #b02525;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-chart-line"></i> Trade Management Panel</h1>
        </div>
    </header>
    
    <div class="container">
        <div id="alertContainer"></div>
        
        <!-- Open New Position Form -->
        <div class="card">
            <h2>Open New Position</h2>
            <form id="openPositionForm">
                <div class="form-group">
                    <label for="asset">Select Asset</label>
                    <select id="asset" name="asset" required>
                        <option value="">Choose asset...</option>
                        <?php foreach ($assets as $asset): ?>
                            <option value="<?php echo $asset['asset_symbol']; ?>">
                                <?php echo $asset['asset_name']; ?> (<?php echo $asset['asset_symbol']; ?>) - $<?php echo number_format($asset['current_price'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="position_type">Position Type</label>
                    <select id="position_type" name="position_type" required>
                        <option value="">Select type...</option>
                        <option value="Buy">Buy (Long)</option>
                        <option value="Sell">Sell (Short)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="open_price">Open Price</label>
                    <input type="number" id="open_price" name="open_price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="open_date">Open Date</label>
                    <input type="datetime-local" id="open_date" name="open_date" required>
                </div>
                
                <div class="form-group">
                    <label for="percentage">Percentage of User Balance (%)</label>
                    <input type="number" id="percentage" name="percentage" step="0.1" min="1" max="100" value="10" required>
                    <small>This percentage of each user's balance will be used for this position.</small>
                </div>
                
                <button type="submit">Open Position for All Users</button>
            </form>
        </div>
        
        <!-- Admin Positions Table -->
        <div class="card">
            <h2>Admin Positions</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Asset</th>
                        <th>Type</th>
                        <th>Open Price</th>
                        <th>Current Price</th>
                        <th>Profit/Loss</th>
                        <th>% of Balance</th>
                        <th>Status</th>
                        <th>Open Date</th>
                        <th>Close Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($admin_positions)): ?>
                        <?php foreach ($admin_positions as $position): ?>
                            <tr>
                                <td><?php echo $position['id']; ?></td>
                                <td><?php echo $position['asset_symbol']; ?></td>
                                <td>
                                    <?php if ($position['position_type'] === 'Buy'): ?>
                                        <span class="badge badge-success">Buy</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Sell</span>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($position['open_price'], 2); ?></td>
                                <td>$<?php echo number_format($position['current_price'], 2); ?></td>
                                <td>
                                    <?php if ($position['profit_loss_percentage'] > 0): ?>
                                        <span style="color: #00b894;">+<?php echo number_format($position['profit_loss_percentage'], 2); ?>%</span>
                                    <?php else: ?>
                                        <span style="color: #d63031;"><?php echo number_format($position['profit_loss_percentage'], 2); ?>%</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $position['percentage_of_balance']; ?>%</td>
                                <td>
                                    <?php if ($position['status'] === 'Open'): ?>
                                        <span class="badge badge-primary">Open</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($position['open_date'])); ?></td>
                                <td><?php echo $position['close_date'] ? date('Y-m-d H:i:s', strtotime($position['close_date'])) : '-'; ?></td>
                                <td>
                                    <?php if ($position['status'] === 'Open'): ?>
                                        <button class="btn-sm btn-danger close-position" data-id="<?php echo $position['id']; ?>">Close</button>
                                    <?php else: ?>
                                        <span>Closed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" style="text-align: center;">No positions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Close Position Modal -->
    <div id="closePositionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 20px; border-radius: 5px; width: 400px;">
            <h3>Close Position</h3>
            <form id="closePositionForm">
                <input type="hidden" id="admin_position_id" name="admin_position_id">
                
                <div class="form-group">
                    <label for="close_price">Close Price</label>
                    <input type="number" id="close_price" name="close_price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="close_date">Close Date</label>
                    <input type="datetime-local" id="close_date" name="close_date" required>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" id="cancelClosePosition" style="background-color: #636e72;">Cancel</button>
                    <button type="submit">Close Position</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Alert function
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = message;
            
            alertContainer.appendChild(alert);
            
            // Remove alert after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
        
        // Set default date time for open_date input
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const year = now.getFullYear();
            const month = (now.getMonth() + 1).toString().padStart(2, '0');
            const day = now.getDate().toString().padStart(2, '0');
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            
            const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('open_date').value = formattedDateTime;
            
            // Asset seçildiğinde fiyatı otomatik doldur
            const assetSelect = document.getElementById('asset');
            assetSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    // Seçilen opsiyonun metninden fiyatı çıkar
                    const priceText = selectedOption.text.split('- $')[1];
                    if (priceText) {
                        const price = parseFloat(priceText.replace(/,/g, ''));
                        document.getElementById('open_price').value = price;
                    }
                } else {
                    document.getElementById('open_price').value = '';
                }
            });
        });
        
        // Open Position Form Handler
        document.getElementById('openPositionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const asset = document.getElementById('asset');
            const asset_symbol = asset.value;
            const position_type = document.getElementById('position_type').value;
            const open_price = document.getElementById('open_price').value;
            const open_date = document.getElementById('open_date').value;
            const percentage = document.getElementById('percentage').value;
            
            const formData = new FormData();
            formData.append('action', 'open_position');
            formData.append('asset_symbol', asset_symbol);
            formData.append('position_type', position_type);
            formData.append('open_price', open_price);
            formData.append('open_date', open_date);
            formData.append('percentage', percentage);
            
            fetch('manage_trades.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'danger');
            });
        });
        
        // Close Position Button Handlers
        document.querySelectorAll('.close-position').forEach(button => {
            button.addEventListener('click', function() {
                const positionId = this.dataset.id;
                document.getElementById('admin_position_id').value = positionId;
                
                // Pozisyonun güncel fiyatını bul ve close_price alanına doldur
                const row = this.closest('tr');
                const currentPriceCell = row.querySelector('td:nth-child(5)');
                if (currentPriceCell) {
                    const currentPrice = parseFloat(currentPriceCell.textContent.replace('$', '').replace(',', ''));
                    document.getElementById('close_price').value = currentPrice;
                }
                
                // Varsayılan kapanış tarihi olarak şu anki zamanı ayarla
                const now = new Date();
                const year = now.getFullYear();
                const month = (now.getMonth() + 1).toString().padStart(2, '0');
                const day = now.getDate().toString().padStart(2, '0');
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                
                const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                document.getElementById('close_date').value = formattedDateTime;
                
                document.getElementById('closePositionModal').style.display = 'block';
            });
        });
        
        // Cancel Close Position
        document.getElementById('cancelClosePosition').addEventListener('click', function() {
            document.getElementById('closePositionModal').style.display = 'none';
        });
        
        // Close Position Form Handler
        document.getElementById('closePositionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const admin_position_id = document.getElementById('admin_position_id').value;
            const close_price = document.getElementById('close_price').value;
            const close_date = document.getElementById('close_date').value;
            
            const formData = new FormData();
            formData.append('action', 'close_position');
            formData.append('admin_position_id', admin_position_id);
            formData.append('close_price', close_price);
            formData.append('close_date', close_date);
            
            fetch('manage_trades.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    document.getElementById('closePositionModal').style.display = 'none';
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'danger');
            });
        });
    </script>
</body>
</html>
