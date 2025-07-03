<?php
require_once 'config.php'; // Veritabanı bağlantısı için

$page_title = "Home";
$page_description = "İstanbulBorsa - Trade stocks and other assets securely with low fees.";
$include_tradingview = true;

include 'header.php';

// Fetch top 8 assets by market cap for the Market Trends section
try {
    $sql = "SELECT * FROM assets ORDER BY market_cap DESC LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $market_trends_assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle database error gracefully
    $market_trends_assets = [];
    // Optionally log the error: error_log($e->getMessage());
}

$page_specific_css = '<style>
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
?>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Trade Stocks and Assets with Confidence</h1>
                    <p>Join millions of users trading on İstanbulBorsa with low fees and maximum security.</p>
                    <div class="hero-cta">
                        <a href="register.php" class="btn btn-primary btn-large">Get Started</a>
                        <a href="markets.php" class="btn btn-outline btn-large">View Markets</a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <span class="stat-value">$5B+</span>
                            <span class="stat-label">24h Trading Volume</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value">200+</span>
                            <span class="stat-label">Stocks & Assets</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value">1M+</span>
                            <span class="stat-label">Active Users</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/hero-image.svg" alt="Stock trading illustration">
                </div>
            </div>
        </div>
    </section>

    <!-- Market Trends Section -->
    <section class="crypto-section" id="markets">
        <div class="container">
            <div class="section-header">
                <h2>Market Trends</h2>
                <p>Live prices of the most popular stocks and assets</p>
            </div>
            <div class="crypto-grid">
                <?php if (!empty($market_trends_assets)): ?>
                    <?php foreach ($market_trends_assets as $asset): ?>
                        <?php
                            // Calculate 24h change
                            $price_change_percentage = 0;
                            if (isset($asset['price_24h_ago']) && $asset['price_24h_ago'] > 0) {
                                $price_change_24h = $asset['current_price'] - $asset['price_24h_ago'];
                                $price_change_percentage = ($price_change_24h / $asset['price_24h_ago']) * 100;
                            }
                            $change_class = $price_change_percentage >= 0 ? 'positive' : 'negative';
                        ?>
                        <a href="trading.php?symbol=<?php echo htmlspecialchars($asset['asset_symbol']); ?>" class="crypto-card">
                            <div class="crypto-info">
                                <div class="crypto-icon">
                                    <i class="<?php echo htmlspecialchars($asset['icon_class'] ?? 'fas fa-chart-line'); ?>"></i>
                                </div>
                                <div class="crypto-name">
                                    <h3><?php echo htmlspecialchars($asset['asset_name']); ?></h3>
                                    <span class="crypto-symbol"><?php echo htmlspecialchars($asset['asset_symbol']); ?></span>
                                </div>
                            </div>
                            <div class="crypto-price">
                                <span class="price">$<?php echo number_format($asset['current_price'], 2); ?></span>
                                <span class="change <?php echo $change_class; ?>">
                                    <?php echo number_format($price_change_percentage, 2); ?>%
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="error-message">Market data is currently unavailable. Please try again later.</p>
                <?php endif; ?>
            </div>
            <div class="view-all">
                <a href="markets.php" class="btn btn-secondary">View All Markets</a>
            </div>
        </div>
    </section>

    <!-- Price Charts Section -->
    <section class="charts-section" id="trading">
        <div class="container">
            <div class="section-header">
                <h2>Price Charts</h2>
                <p>Track cryptocurrency price movements in real-time</p>
            </div>
            <div class="tabs">
                <div class="tab active">Bitcoin (BTC)</div>
                <div class="tab">Ethereum (ETH)</div>
                <div class="tab">Solana (SOL)</div>
                <div class="tab">Cardano (ADA)</div>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">
                        <h3>Bitcoin (BTC)</h3>
                        <span class="price">$42,850.75</span>
                        <span class="change positive">+2.34%</span>
                    </div>
                    <div class="chart-timeframes">
                        <button class="timeframe active">1D</button>
                        <button class="timeframe">1W</button>
                        <button class="timeframe">1M</button>
                        <button class="timeframe">3M</button>
                        <button class="timeframe">1Y</button>
                        <button class="timeframe">All</button>
                    </div>
                </div>
                <div class="chart tradingview-chart">
                    <!-- TradingView Widget BEGIN -->
                    <div class="tradingview-widget-container">
                        <div id="tradingview_btcusd_index"></div>
                    </div>
                    <!-- TradingView Widget END -->
                </div>
                <div class="chart-info">
                    <div class="info-item">
                        <span class="info-label">Market Cap</span>
                        <span class="info-value">$821.7B</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Volume (24h)</span>
                        <span class="info-value">$28.5B</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Circulating Supply</span>
                        <span class="info-value">19.2M BTC</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">All-Time High</span>
                        <span class="info-value">$69,044.77</span>
                    </div>
                </div>
            </div>
            <div class="trading-cta">
                <a href="start-trading.html" class="btn btn-primary">Start Trading</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose CryptoExchange</h2>
                <p>We provide the tools and security you need for successful trading</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/feature-1.svg" alt="Security Icon">
                    </div>
                    <h3>Industry-Leading Security</h3>
                    <p>Advanced encryption, two-factor authentication, and cold storage for your digital assets.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/feature-2.svg" alt="Fees Icon">
                    </div>
                    <h3>Competitive Fees</h3>
                    <p>Trade with low fees starting at just 0.1% and enjoy volume-based discounts.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="assets/feature-3.svg" alt="Interface Icon">
                    </div>
                    <h3>Intuitive Trading Interface</h3>
                    <p>Powerful yet easy-to-use platform suitable for both beginners and professional traders.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Trading</h3>
                    <p>Trade on the go with our responsive web interface optimized for all devices.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Customer Support</h3>
                    <p>Get help whenever you need it with our round-the-clock customer service team.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3>Secure Wallet</h3>
                    <p>Manage all your digital assets in one secure, convenient place.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How to Start Section -->
    <section class="how-to-section" id="how-to">
        <div class="container">
            <div class="section-header">
                <h2>How to Start Trading</h2>
                <p>Begin your cryptocurrency journey in three simple steps</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <div class="step-icon">
                            <img src="assets/how-to-1.svg" alt="Create account icon">
                        </div>
                        <h3>Create an Account</h3>
                        <p>Sign up with your email address and create a strong password. Verify your identity to unlock all features.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <div class="step-icon">
                            <img src="assets/how-to-2.svg" alt="Deposit funds icon">
                        </div>
                        <h3>Deposit Funds</h3>
                        <p>Add funds to your account using bank transfer, credit card, or by depositing cryptocurrency.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <div class="step-icon">
                            <img src="assets/how-to-3.svg" alt="Start trading icon">
                        </div>
                        <h3>Start Trading</h3>
                        <p>Begin buying and selling cryptocurrencies on our exchange with just a few clicks.</p>
                    </div>
                </div>
            </div>
            <div class="get-started-cta">
                <a href="register.php" class="btn btn-primary btn-large">Create Account Now</a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="section-header">
                <h2>Frequently Asked Questions</h2>
                <p>Find answers to common questions about cryptocurrency trading</p>
            </div>
            <div class="faq-accordion">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What is cryptocurrency?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Cryptocurrency is a digital or virtual currency that uses cryptography for security and operates on a technology called blockchain. It's not issued by any central authority, making it theoretically immune to government interference or manipulation.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How do I create an account?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>To create an account, click the "Sign Up" button at the top of the page. You'll need to provide your email address, create a password, and verify your identity with basic personal information and a government-issued ID.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What are the trading fees?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Our trading fees start at 0.1% per transaction and decrease based on your 30-day trading volume. We also offer maker-taker fee schedules to encourage liquidity. You can view our complete fee schedule on our Fees page.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How secure is my account?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>We employ industry-leading security measures including two-factor authentication, advanced encryption, and cold storage for the majority of digital assets. We also regularly undergo security audits and maintain insurance coverage for digital assets held in our custody.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What payment methods are accepted?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>We accept a variety of payment methods including bank transfers (ACH/SEPA), wire transfers, credit/debit cards, and cryptocurrency deposits. Available methods may vary by region and verification level.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How long do transactions take?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Cryptocurrency transactions on our platform are executed instantly. Withdrawal times depend on the cryptocurrency network's confirmation times and can vary from a few minutes to over an hour. Fiat deposits and withdrawals typically take 1-5 business days depending on the payment method and your bank.</p>
                    </div>
                </div>
            </div>
            <div class="faq-cta">
                <p>Still have questions?</p>
                <a href="#" class="btn btn-secondary">Contact Support</a>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2>Stay Updated</h2>
                    <p>Subscribe to our newsletter for the latest crypto news, market trends, and platform updates.</p>
                </div>
                <form class="newsletter-form">
                    <div class="form-group">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </div>
                    <div class="form-disclaimer">
                        <p>By subscribing, you agree to our <a href="#">Privacy Policy</a>. You can unsubscribe at any time.</p>
                    </div>
                </form>
            </div>
        </div>
    </section>

<!-- TradingView Chart Script -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    if (typeof TradingView !== 'undefined' && document.getElementById('tradingview_btcusd_index')) {
        new TradingView.widget({
            "width": "100%",
            "height": 400,
            "symbol": "BITSTAMP:BTCUSD",
            "interval": "D",
            "timezone": "Etc/UTC",
            "theme": document.body.classList.contains('dark-theme') ? "dark" : "light",
            "style": "1",
            "locale": "en",
            "toolbar_bg": "#f1f3f6",
            "enable_publishing": false,
            "hide_top_toolbar": false,
            "hide_legend": true,
            "allow_symbol_change": true,
            "container_id": "tradingview_btcusd_index"
        });

        // Listen for theme changes to update the chart theme
        const themeSwitch = document.getElementById('theme-switch');
        if (themeSwitch) {
            themeSwitch.addEventListener('change', function() {
                // Refresh the page to reload the widget with the new theme
                setTimeout(() => {
                    location.reload();
                }, 500);
            });
        }
    }
});
</script>

<?php include 'footer.php'; ?>
