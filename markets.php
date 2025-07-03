<?php
require_once 'config.php';



// Helper functions for formatting
function formatNumber($number) {
    if ($number >= 1000000000) {
        return '₺' . number_format($number / 1000000000, 1) . 'B';
    } elseif ($number >= 1000000) {
        return '₺' . number_format($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return '₺' . number_format($number / 1000, 1) . 'K';
    } else {
        return '₺' . number_format($number, 2);
    }
}

function formatPrice($price) {
    if ($price >= 1) {
        return '₺' . number_format($price, 2);
    } else {
        return '₺' . number_format($price, 4);
    }
}

// Pagination and search parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Assets per page
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch assets from database with search and pagination
try {
    // Count total assets for pagination
    $count_sql = "SELECT COUNT(*) as total FROM assets";
    $count_params = [];
    
    if (!empty($search)) {
        $count_sql .= " WHERE asset_name LIKE ? OR asset_symbol LIKE ?";
        $count_params = ["%$search%", "%$search%"];
    }
    
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total_assets = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_assets / $limit);
    
    // Fetch assets with search and pagination
    $sql = "SELECT * FROM assets";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " WHERE asset_name LIKE ? OR asset_symbol LIKE ?";
        $params = ["%$search%", "%$search%"];
    }
    
    $sql .= " ORDER BY market_cap DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $assets = [];
    $total_pages = 0;
    $total_assets = 0;
    $error_message = "Error loading data: " . $e->getMessage();
}

$page_title = "Stock Markets";
$page_description = "View all stock markets and current prices on İstanbulBorsa.";
$page_specific_css = '<style>
    :root {
        --color-primary: #0984E3;
        --color-primary-dark: #0770C1;
        --color-secondary: #6C757D;
        --color-secondary-dark: #5A6268;
        --color-bg-card: #ffffff;
        --color-border: #e0e0e0;
        --color-text: #333333;
        --color-text-muted: #666666;
        --color-bg-hover: #f8f9fa;
    }
    
    .search-form {
        margin-bottom: 30px;
    }
    
    .search-box {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 600px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.1);
        padding: 5px;
        border-radius: 12px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .search-box input {
        flex: 1;
        padding: 12px 15px 12px 45px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        background-color: rgba(255, 255, 255, 0.9);
        color: var(--color-text);
        transition: all 0.3s ease;
    }
    
    .search-box input::placeholder {
        color: var(--color-text-muted);
    }
    
    .search-box input:focus {
        outline: none;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(9, 132, 227, 0.1);
    }
    
    .search-box i.fa-search {
        position: absolute;
        left: 20px;
        color: var(--color-text-muted);
        z-index: 1;
        font-size: 16px;
    }
    
    .search-btn {
        padding: 12px 24px;
        background-color: var(--color-primary);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    
    .search-btn:hover {
        background-color: var(--color-primary-dark);
        transform: translateY(-1px);
    }
    
    .clear-search {
        padding: 12px 24px;
        background-color: var(--color-secondary);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    
    .clear-search:hover {
        background-color: var(--color-secondary-dark);
        transform: translateY(-1px);
        text-decoration: none;
        color: white;
    }

    @media (max-width: 768px) {
        .search-box {
            flex-direction: column;
            padding: 10px;
            gap: 8px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
        }
        
        .search-box i.fa-search {
            left: 15px;
            top: 21px;
        }
        
        .search-btn,
        .clear-search {
            width: 100%;
            text-align: center;
            padding: 12px 20px;
        }
    }
</style>';

include 'header.php';
?>

    <!-- Markets Section -->
    <section class="markets-page">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title"><?php echo $page_title; ?></h1>
                <p class="page-description"><?php echo $page_description; ?></p>
            </div>
            
            <div class="markets-filter">
                <form method="GET" action="markets.php" class="search-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search stocks...">
                        <button type="submit" class="search-btn">Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="markets.php" class="clear-search">Clear</a>
                        <?php endif; ?>
                </div>
                </form>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="markets-table-container">
                <table class="markets-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>24h Change</th>
                            <th>24h Volume</th>
                            <th>Market Cap</th>
                            <th>Trade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($assets)): ?>
                            <?php 
                            $rank = $offset + 1; // Start rank from current page offset
                            foreach ($assets as $asset): 
                                // Calculate 24h change percentage
                                $change_24h = 0;
                                if ($asset['price_24h_ago'] > 0) {
                                    $change_24h = (($asset['current_price'] - $asset['price_24h_ago']) / $asset['price_24h_ago']) * 100;
                                }
                                $change_class = $change_24h >= 0 ? 'positive' : 'negative';
                                $change_sign = $change_24h >= 0 ? '+' : '';
                            ?>
                            <tr>
                                <td><?php echo $rank; ?></td>
                                <td>
                                    <a href="trading.php?symbol=<?php echo $asset['asset_symbol']; ?>" class="coin-info">
                                        <i class="<?php echo $asset['icon_class']; ?> crypto-icon"></i>
                                    <div>
                                            <span class="coin-name"><?php echo htmlspecialchars($asset['asset_name']); ?></span>
                                            <span class="coin-symbol"><?php echo htmlspecialchars($asset['asset_symbol']); ?></span>
                                    </div>
                                </a>
                            </td>
                                <td><?php echo formatPrice($asset['current_price']); ?></td>
                                <td class="<?php echo $change_class; ?>">
                                    <?php echo $change_sign . number_format($change_24h, 2) . '%'; ?>
                            </td>
                                <td><?php echo formatNumber($asset['volume_24h']); ?></td>
                                <td><?php echo formatNumber($asset['market_cap']); ?></td>
                                <td>
                                    <a href="trading.php?symbol=<?php echo $asset['asset_symbol']; ?>" class="btn btn-outline btn-sm">Trade</a>
                            </td>
                        </tr>
                            <?php 
                            $rank++;
                            endforeach; 
                            ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--color-text-muted);">
                                    <i class="fas fa-chart-line" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                                    <br>
                                    <strong>No stocks available</strong>
                                    <br>
                                    <small>Please check back later or contact support.</small>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php
                // Previous button
                if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-button">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif;
                
                // Page numbers
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                // Show first page if not in range
                if ($start_page > 1): ?>
                    <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-button">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif;
                endif;
                
                // Show page numbers in range
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="pagination-button <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor;
                
                // Show last page if not in range
                if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-button"><?php echo $total_pages; ?></a>
                <?php endif;
                
                // Next button
                if ($page < $total_pages): ?>
                    <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-button">
                    <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="pagination-info">
                <p>Total <?php echo $total_assets; ?> stocks, page <?php echo $page; ?> of <?php echo $total_pages; ?></p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle functionality
        const themeSwitch = document.getElementById('theme-switch');
    const body = document.body;
    
    // Check for saved theme preference or default to light mode
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    if (currentTheme === 'dark') {
        body.classList.add('dark-theme');
        if (themeSwitch) themeSwitch.checked = true;
    }
    
    if (themeSwitch) {
        themeSwitch.addEventListener('change', function() {
            if (this.checked) {
                body.classList.add('dark-theme');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark-theme');
                localStorage.setItem('theme', 'light');
            }
        });
    }
    
    // Search form enhancement
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }
    
    // Add loading animation to search button
    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
        });
    }
    
    // Smooth scroll for pagination
    const paginationButtons = document.querySelectorAll('.pagination-button');
    paginationButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            
            // Add loading state
            this.style.opacity = '0.6';
            this.style.pointerEvents = 'none';
            
            // Navigate to new page
            window.location.href = url;
        });
    });
        });
    </script>

<?php include 'footer.php'; ?>