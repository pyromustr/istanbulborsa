<?php
require_once 'config.php';
session_start();

$page_title = "Panel";
$page_description = "Kişisel paneliniz. Portföy, işlemler ve özetler.";

include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Toplam bakiye, kar/zarar, pozisyon sayıları
$total_completed_amount = 0;
$total_profit_loss = 0;
$active_positions_count = 0;
$closed_positions_count = 0;
$live_profit_loss = 0;
try {
    $sql = "SELECT SUM(usd_amount) as total_completed_amount FROM wallet_transactions WHERE user_id = ? AND status = 'Completed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $completed_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_completed_amount = $completed_result ? $completed_result['total_completed_amount'] : 0;

    $sql = "SELECT COUNT(*) as count FROM positions WHERE user_id = ? AND status = 'Open'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $active_positions_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    $sql = "SELECT COUNT(*) as count FROM positions WHERE user_id = ? AND status = 'Closed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $closed_positions_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    $sql = "SELECT position_type, amount, open_price, close_price FROM positions WHERE user_id = ? AND status = 'Closed'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $pl = ($p['position_type'] === 'Buy') ? ($p['close_price'] - $p['open_price']) * $p['amount'] / $p['open_price'] : ($p['open_price'] - $p['close_price']) * $p['amount'] / $p['open_price'];
        $total_profit_loss += $pl;
    }
    $sql = "SELECT p.position_type, p.amount, p.open_price, a.current_price FROM positions p JOIN assets a ON p.asset_symbol = a.asset_symbol WHERE p.user_id = ? AND p.status = 'Open'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $pl = ($p['position_type'] === 'Buy') ? ($p['current_price'] - $p['open_price']) * $p['amount'] / $p['open_price'] : ($p['open_price'] - $p['current_price']) * $p['amount'] / $p['open_price'];
        $live_profit_loss += $pl;
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
$total_balance = $total_completed_amount + $total_profit_loss;
$percentage_change = ($total_balance > 0 && $total_completed_amount > 0) ? ($total_profit_loss / $total_completed_amount) * 100 : 0;
?>
<div class="dashboard-container">

    <!-- Ana İçerik -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Panel</h1>
            <div class="header-actions">
                <a href="wallet.php" class="btn btn-primary"><i class="fas fa-plus"></i> Para Yatır</a>
            </div>
        </div>
        <!-- Kartlar -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-icon primary"><i class="fas fa-wallet"></i></div>
                <div class="card-label">Toplam Bakiye</div>
                <div class="card-value">$<?php echo number_format($total_balance, 2); ?></div>
                <div class="card-change <?php echo $percentage_change > 0 ? 'positive' : ($percentage_change < 0 ? 'negative' : ''); ?>">
                    <?php if ($percentage_change != 0): ?>
                        <i class="fas fa-arrow-<?php echo $percentage_change > 0 ? 'up' : 'down'; ?>"></i>
                        <span><?php echo number_format(abs($percentage_change), 2); ?>% ($<?php echo number_format(abs($total_profit_loss), 2); ?>)</span>
                    <?php else: ?>
                        <span>Değişim yok</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="card-icon success"><i class="fas fa-chart-line"></i></div>
                <div class="card-label">Canlı Kar/Zarar</div>
                <div class="card-value"><?php echo $live_profit_loss >= 0 ? '+' : ''; ?>$<?php echo number_format($live_profit_loss, 2); ?></div>
                <div class="card-change <?php echo $live_profit_loss > 0 ? 'positive' : ($live_profit_loss < 0 ? 'negative' : ''); ?>">
                    <?php if ($active_positions_count > 0): ?>
                        <i class="fas fa-arrow-<?php echo $live_profit_loss > 0 ? 'up' : 'down'; ?>"></i>
                        <span><?php echo $active_positions_count; ?> açık pozisyon</span>
                    <?php else: ?>
                        <span>Açık pozisyon yok</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="card-icon warning"><i class="fas fa-coins"></i></div>
                <div class="card-label">Açık İşlem</div>
                <div class="card-value"><?php echo $active_positions_count; ?></div>
                <div class="card-change"><span>Açık pozisyon</span></div>
            </div>
            <div class="dashboard-card">
                <div class="card-icon danger"><i class="fas fa-exchange-alt"></i></div>
                <div class="card-label">Kapanan İşlem</div>
                <div class="card-value"><?php echo $closed_positions_count; ?></div>
                <div class="card-change"><span>Tamamlanan pozisyon</span></div>
            </div>
        </div>
        <!-- Aktif Pozisyonlar -->
        <section class="transaction-section">
            <div class="section-header">
                <h2>Açık Pozisyonlar</h2>
            </div>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Varlık</th>
                        <th>Tip</th>
                        <th>Miktar</th>
                        <th>Açılış Fiyatı</th>
                        <th>Güncel Fiyat</th>
                        <th>K/Z</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                try {
                    $sql = "SELECT p.*, a.current_price, a.asset_name, a.icon_class FROM positions p JOIN assets a ON p.asset_symbol = a.asset_symbol WHERE p.user_id = ? AND p.status = 'Open' ORDER BY p.open_date DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user_id]);
                    $active_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($active_positions) > 0):
                        foreach ($active_positions as $position):
                            $profit_loss = ($position['position_type'] === 'Buy') ? ($position['current_price'] - $position['open_price']) * $position['amount'] / $position['open_price'] : ($position['open_price'] - $position['current_price']) * $position['amount'] / $position['open_price'];
                            $profit_loss_percentage = ($position['position_type'] === 'Buy') ? (($position['current_price'] - $position['open_price']) / $position['open_price']) * 100 : (($position['open_price'] - $position['current_price']) / $position['open_price']) * 100;
                ?>
                    <tr>
                        <td><div class="asset-info"><i class="<?php echo $position['icon_class'] ?: 'fas fa-coins'; ?> crypto-icon"></i><span class="asset-name"><?php echo $position['asset_name']; ?></span><span class="asset-ticker"><?php echo $position['asset_symbol']; ?></span></div></td>
                        <td><span class="transaction-type <?php echo strtolower($position['position_type']); ?>"><?php echo $position['position_type']; ?></span></td>
                        <td>$<?php echo number_format($position['amount'], 2); ?></td>
                        <td>$<?php echo number_format($position['open_price'], 2); ?></td>
                        <td>$<?php echo number_format($position['current_price'], 2); ?></td>
                        <td class="<?php echo $profit_loss >= 0 ? 'positive' : 'negative'; ?>"><?php echo $profit_loss >= 0 ? '+' : ''; ?>$<?php echo number_format($profit_loss, 2); ?> (<?php echo $profit_loss_percentage >= 0 ? '+' : ''; ?><?php echo number_format($profit_loss_percentage, 2); ?>%)</td>
                        <td><div class="asset-actions"><a href="trading.php?symbol=<?php echo $position['asset_symbol']; ?>" class="btn btn-outline btn-sm">İşlem Yap</a></div></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--color-text-muted);">Açık pozisyonunuz yok.</td></tr>
                <?php endif; } catch (PDOException $e) { echo '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--color-danger);">Pozisyonlar yüklenemedi.</td></tr>'; } ?>
                </tbody>
            </table>
        </section>
        <!-- Kapanan Pozisyonlar -->
        <section class="transaction-section">
            <div class="section-header">
                <h2>Kapanan Pozisyonlar</h2>
            </div>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Varlık</th>
                        <th>Tip</th>
                        <th>Miktar</th>
                        <th>Açılış</th>
                        <th>Kapanış</th>
                        <th>K/Z</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                try {
                    $sql = "SELECT p.*, a.asset_name, a.icon_class FROM positions p JOIN assets a ON p.asset_symbol = a.asset_symbol WHERE p.user_id = ? AND p.status = 'Closed' ORDER BY p.close_date DESC LIMIT 5";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user_id]);
                    $closed_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($closed_positions) > 0):
                        foreach ($closed_positions as $position):
                            $profit_loss = ($position['position_type'] === 'Buy') ? ($position['close_price'] - $position['open_price']) * $position['amount'] / $position['open_price'] : ($position['open_price'] - $position['close_price']) * $position['amount'] / $position['open_price'];
                            $profit_loss_percentage = ($position['position_type'] === 'Buy') ? (($position['close_price'] - $position['open_price']) / $position['open_price']) * 100 : (($position['open_price'] - $position['close_price']) / $position['open_price']) * 100;
                ?>
                    <tr>
                        <td><div class="transaction-date"><?php echo date('d.m.Y', strtotime($position['close_date'])); ?></div><div class="transaction-id">#POS<?php echo $position['id']; ?></div></td>
                        <td><div class="asset-info"><i class="<?php echo $position['icon_class'] ?: 'fas fa-coins'; ?> crypto-icon"></i><span class="asset-name"><?php echo $position['asset_name']; ?></span><span class="asset-ticker"><?php echo $position['asset_symbol']; ?></span></div></td>
                        <td><span class="transaction-type <?php echo strtolower($position['position_type']); ?>"><?php echo $position['position_type']; ?></span></td>
                        <td>$<?php echo number_format($position['amount'], 2); ?></td>
                        <td>$<?php echo number_format($position['open_price'], 2); ?></td>
                        <td>$<?php echo number_format($position['close_price'], 2); ?></td>
                        <td class="<?php echo $profit_loss >= 0 ? 'positive' : 'negative'; ?>"><?php echo $profit_loss >= 0 ? '+' : ''; ?>$<?php echo number_format($profit_loss, 2); ?> (<?php echo $profit_loss_percentage >= 0 ? '+' : ''; ?><?php echo number_format($profit_loss_percentage, 2); ?>%)</td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--color-text-muted);">Kapanan pozisyon yok.</td></tr>
                <?php endif; } catch (PDOException $e) { echo '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--color-danger);">Pozisyonlar yüklenemedi.</td></tr>'; } ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const dashboardContainer = document.querySelector('.dashboard-container');
    // Masaüstü sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            dashboardContainer.classList.toggle('sidebar-collapsed');
            const icon = sidebarToggle.querySelector('i');
            icon.className = sidebar.classList.contains('collapsed') ? 'fas fa-chevron-right' : 'fas fa-bars';
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    // Ekran boyutuna göre sidebar davranışı
    function handleSidebarBtn() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('mobile');
        } else {
            sidebar.classList.remove('mobile');
            sidebar.classList.remove('mobile-open');
            dashboardContainer.classList.remove('mobile-sidebar-open');
        }
    }
    window.addEventListener('resize', handleSidebarBtn);
    handleSidebarBtn();
    // LocalStorage'dan sidebar durumu
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        dashboardContainer.classList.add('sidebar-collapsed');
        const icon = sidebarToggle.querySelector('i');
        icon.className = 'fas fa-chevron-right';
    }
    // Mobilde sidebar dışına tıklayınca kapansın
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && sidebar.classList.contains('mobile-open')) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
                dashboardContainer.classList.remove('mobile-sidebar-open');
            }
        }
    });
});
</script>
<?php include 'footer.php'; ?>