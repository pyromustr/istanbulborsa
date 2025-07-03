<?php
require_once 'config.php';

// Kullanıcı giriş yapmış mı kontrol et
$user_logged_in = isset($_SESSION['user_id']);
$user = null;
$user_name = 'Guest User';
$user_email = 'Not logged in';
$open_positions = [];

if ($user_logged_in) {
    $user_id = $_SESSION['user_id'];

    // Kullanıcı bilgilerini getir
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user) {
        $user_name = $user['name'] . ' ' . $user['surname'];
        $user_email = $user['email'];
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_email'] = $user_email;
    }
}

// İşlem yapılacak coin bilgisini al
$symbol = isset($_GET['symbol']) && !empty(trim($_GET['symbol'])) ? strtoupper(trim($_GET['symbol'])) : 'XAUUSD';

// Seçili coinin detaylarını getir
$sql = "SELECT * FROM assets WHERE asset_symbol = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$symbol]);
$selected_asset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$selected_asset) {
    // XAUUSD'yi varsayılan olarak getir
    $sql = "SELECT * FROM assets WHERE asset_symbol = 'XAUUSD'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $selected_asset = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$selected_asset) {
        // Eğer XAUUSD yoksa BTC'yi dene
        $sql = "SELECT * FROM assets WHERE asset_symbol = 'BTC'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $selected_asset = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$selected_asset) {
            die("Varsayılan varlık bulunamadı. Lütfen veritabanını kontrol edin.");
        }
        $symbol = 'BTC';
    } else {
        $symbol = 'XAUUSD';
    }
}

// Giriş yapmış kullanıcı için açık pozisyonları getir
if ($user_logged_in && $user) {
    $positions_sql = "SELECT * FROM user_assets WHERE user_id = ? AND asset_symbol = ? AND quantity > 0";
    $stmt = $conn->prepare($positions_sql);
    $stmt->execute([$user_id, $symbol]);
    $open_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Market verilerini hazırla
$market_info = [
    'name' => $selected_asset['asset_name'],
    'symbol' => $selected_asset['asset_symbol'],
    'current_price' => $selected_asset['current_price'],
    'price_24h_ago' => $selected_asset['price_24h_ago'],
    'market_cap' => $selected_asset['market_cap'],
    'volume_24h' => $selected_asset['volume_24h'],
    'circulating_supply' => $selected_asset['circulating_supply'],
    'all_time_high' => $selected_asset['all_time_high']
];

// 24 saatlik değişim yüzdesi hesapla
$price_change_24h = $market_info['current_price'] - $market_info['price_24h_ago'];
if ($market_info['price_24h_ago'] != 0) {
    $price_change_percentage = ($price_change_24h / $market_info['price_24h_ago']) * 100;
} else {
    $price_change_percentage = 0;
}

// Tüm coinleri getir
$sql = "SELECT * FROM assets ORDER BY market_cap DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$all_assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// TradingView sembolü için uygun formatı hazırla
function getTradingViewSymbol($symbol) {
    $map = [
        'BTC' => 'BITSTAMP:BTCUSD',
        'ETH' => 'BITSTAMP:ETHUSD',
        'SOL' => 'BINANCE:SOLUSD',
        'ADA' => 'BINANCE:ADAUSD',
        'DOT' => 'BINANCE:DOTUSD',
        'XRP' => 'BINANCE:XRPUSD',
        'XAUUSD' => 'OANDA:XAUUSD',
        'XAGUSD' => 'OANDA:XAGUSD',
        'EURUSD' => 'OANDA:EURUSD',
        'GBPUSD' => 'OANDA:GBPUSD',
        'USDJPY' => 'OANDA:USDJPY',
        'USDCHF' => 'OANDA:USDCHF',
        'AUDUSD' => 'OANDA:AUDUSD',
        'USDCAD' => 'OANDA:USDCAD',
        'NZDUSD' => 'OANDA:NZDUSD',
        'EURGBP' => 'OANDA:EURGBP',
        'EURJPY' => 'OANDA:EURJPY',
        'GBPJPY' => 'OANDA:GBPJPY',
    ];
    return $map[$symbol] ?? 'OANDA:XAUUSD';
}
$tradingview_symbol = getTradingViewSymbol($symbol);

$page_title = "Trading - {$market_info['name']}";
$page_description = "Trade {$market_info['name']} on İstanbulBorsa with real-time charts and low fees.";
$include_tradingview = true;

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
    
    /* Header visibility */
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

    /* Trading Chart Container */
    .trading-chart-container {
        background-color: var(--color-bg-card);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--color-border);
        min-height: 600px;
    }
    
    .chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .asset-info-header {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .asset-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--color-primary);
        color: white;
    display: flex;
    align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .asset-details h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
    }
    
    .asset-price {
    font-size: 20px;
    font-weight: 600;
        margin: 5px 0;
}

.price-change {
    font-size: 14px;
    }
    
    .price-change.positive { color: var(--color-success); }
    .price-change.negative { color: var(--color-danger); }
    
    .tradingview-widget-container {
        height: 500px;
        width: 100%;
    }
    
    #tradingview_chart {
        height: 100%;
        width: 100%;
    }
    
    /* Market Info Cards */
    .market-info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .info-card {
        background-color: var(--color-bg-card);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--color-border);
        text-align: center;
    }
    
    .info-card-label {
        font-size: 14px;
        color: var(--color-text-muted);
        margin-bottom: 10px;
    }
    
    .info-card-value {
        font-size: 18px;
        font-weight: 600;
    }
    
    /* Transaction Table Styles */
    .transaction-section {
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
    
    .transaction-type.buy {
        background-color: rgba(0, 184, 148, 0.1);
        color: var(--color-success);
    }
    
    .transaction-type.sell {
        background-color: rgba(214, 48, 49, 0.1);
        color: var(--color-danger);
    }
    
    .transaction-type.transfer {
        background-color: rgba(45, 52, 54, 0.1);
        color: var(--color-primary);
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
</style>';

include 'header.php';
?>

    <!-- Dashboard Container -->
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        if ($user_logged_in) {
                            $initials = "";
                            $name_parts = explode(" ", $user_name);
                            foreach($name_parts as $part) {
                                $initials .= strtoupper(substr($part, 0, 1));
                            }
                            echo $initials;
                        } else {
                            echo "G";
                        }
                        ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $user_name; ?></div>
                        <div class="user-email"><?php echo $user_email; ?></div>
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
                <li class="nav-item active">
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
                <li class="nav-item">
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
                <?php if ($user_logged_in): ?>
                <li class="nav-item">
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Trading - <?php echo htmlspecialchars($market_info['name']); ?></h1>
                <div class="header-actions">
                    <select id="assetSelector" class="btn btn-secondary" onchange="changeAsset(this.value)">
                        <?php foreach ($all_assets as $asset): ?>
                            <option value="<?php echo htmlspecialchars($asset['asset_symbol']); ?>" 
                                    <?php echo $asset['asset_symbol'] == $symbol ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($asset['asset_name']); ?> (<?php echo htmlspecialchars($asset['asset_symbol']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Market Info Cards -->
            <div class="market-info-cards">
                <div class="info-card">
                    <div class="info-card-label">Current Price</div>
                    <div class="info-card-value">$<?php echo number_format($market_info['current_price'], 2); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-card-label">24h Change</div>
                    <div class="info-card-value price-change <?php echo $price_change_percentage >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($price_change_percentage, 2); ?>%
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-label">Market Cap</div>
                    <div class="info-card-value">$<?php echo number_format($market_info['market_cap'] / 1000000000, 2); ?>B</div>
                </div>
                <div class="info-card">
                    <div class="info-card-label">24h Volume</div>
                    <div class="info-card-value">$<?php echo number_format($market_info['volume_24h'] / 1000000, 2); ?>M</div>
                </div>
            </div>

            <!-- Trading Chart -->
            <div class="trading-chart-container">
                <div class="chart-header">
                    <div class="asset-info-header">
                        <div class="asset-icon">
                            <i class="<?php echo htmlspecialchars($selected_asset['icon_class'] ?? 'fas fa-chart-line'); ?>"></i>
                        </div>
                        <div class="asset-details">
                            <h2><?php echo htmlspecialchars($market_info['name']); ?> (<?php echo htmlspecialchars($market_info['symbol']); ?>)</h2>
                            <div class="asset-price">$<?php echo number_format($market_info['current_price'], 2); ?></div>
                            <div class="price-change <?php echo $price_change_percentage >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $price_change_percentage >= 0 ? '+' : ''; ?><?php echo number_format($price_change_percentage, 2); ?>% (24h)
                            </div>
                        </div>
                    </div>
        </div>

                <!-- TradingView Widget -->
                <div class="tradingview-widget-container">
                    <div id="tradingview_chart"></div>
                </div>
        </div>

            <!-- Recent Transactions -->
            <section class="transaction-section">
                <div class="section-header">
                    <h2>Recent Transactions</h2>
                    <a href="#" class="btn btn-secondary btn-sm">View All</a>
                </div>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Asset</th>
                            <th>Amount</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="transaction-date">May 15, 2025</div>
                                <div class="transaction-id">#TX8293782</div>
                            </td>
                            <td><span class="transaction-type buy">Buy</span></td>
                            <td>Bitcoin (BTC)</td>
                            <td>0.0534 BTC</td>
                            <td>$42,850.75</td>
                            <td>$2,288.23</td>
                            <td><span class="transaction-status completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="transaction-date">May 14, 2025</div>
                                <div class="transaction-id">#TX8293656</div>
                            </td>
                            <td><span class="transaction-type sell">Sell</span></td>
                            <td>Cardano (ADA)</td>
                            <td>100 ADA</td>
                            <td>$0.5342</td>
                            <td>$53.42</td>
                            <td><span class="transaction-status completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="transaction-date">May 13, 2025</div>
                                <div class="transaction-id">#TX8293512</div>
                            </td>
                            <td><span class="transaction-type buy">Buy</span></td>
                            <td>Solana (SOL)</td>
                            <td>5.2 SOL</td>
                            <td>$106.75</td>
                            <td>$555.10</td>
                            <td><span class="transaction-status completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="transaction-date">May 12, 2025</div>
                                <div class="transaction-id">#TX8293498</div>
                            </td>
                            <td><span class="transaction-type transfer">Transfer</span></td>
                            <td>Ethereum (ETH)</td>
                            <td>0.5 ETH</td>
                            <td>-</td>
                            <td>-</td>
                            <td><span class="transaction-status pending">Pending</span></td>
                            </tr>
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

// Asset selector change function
function changeAsset(symbol) {
    window.location.href = 'trading.php?symbol=' + symbol;
}

// TradingView Chart with theme detection
function createTradingViewWidget() {
    const isDarkTheme = document.body.classList.contains('dark-theme');
    const container = document.getElementById('tradingview_chart');
    
        if (container) {
        // Clear previous widget
        container.innerHTML = '';
        
            new TradingView.widget({
            "width": "100%",
            "height": "500",
                "symbol": "<?php echo $tradingview_symbol; ?>",
                "interval": "D",
                "timezone": "Etc/UTC",
            "theme": isDarkTheme ? "dark" : "light",
                "style": "1",
            "locale": "tr",
            "toolbar_bg": isDarkTheme ? "#131722" : "#f1f3f6",
                "enable_publishing": false,
                "allow_symbol_change": true,
            "container_id": "tradingview_chart",
            "studies": [
                "MASimple@tv-basicstudies"
            ],
            "show_popup_button": true,
            "popup_width": "1000",
            "popup_height": "650"
        });
    }
}

    // Initial widget creation
    createTradingViewWidget();

// Watch for theme changes
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            // Recreate widget when theme changes
                    createTradingViewWidget();
                }
            });
        });

// Start observing body class changes
observer.observe(document.body, {
    attributes: true,
    attributeFilter: ['class']
});
</script>

<?php include 'footer.php'; ?>