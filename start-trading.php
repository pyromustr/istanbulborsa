<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Trading - CryptoExchange</title>
    <meta name="description" content="Start trading cryptocurrencies on CryptoExchange with our easy-to-use trading platform.">
    <!-- Normalize CSS -->
    <link rel="stylesheet" href="css/normalize.css">
    <!-- Custom styles -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/additional-styles.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php"><img src="assets/logo.svg" alt="CryptoExchange Logo"></a>
                </div>
                <input type="checkbox" id="nav-toggle" class="nav-toggle">
                <label for="nav-toggle" class="nav-toggle-label">
                    <i class="fas fa-bars"></i>
                </label>
                <nav class="navigation">
                    <ul class="nav-list">
                        <li><a href="index.php#markets">Markets</a></li>
                        <li><a href="index.php#trading">Trading</a></li>
                        <li><a href="index.php#features">Features</a></li>
                        <li><a href="index.php#how-to">How to Start</a></li>
                        <li><a href="index.php#faq">FAQ</a></li>
                    </ul>
                </nav>
                <div class="theme-toggle">
                    <input type="checkbox" id="theme-switch" class="theme-switch">
                    <label for="theme-switch" class="theme-switch-label">
                        <i class="fas fa-sun"></i>
                        <i class="fas fa-moon"></i>
                    </label>
                </div>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-secondary">Log In</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Start Trading Section -->
    <section class="start-trading-page">
        <div class="container">
            <div class="page-title-section">
                <h1>Start Trading</h1>
                <p>Choose a trading option that matches your experience and needs</p>
            </div>
            
            <div class="trading-options">
                <div class="trading-option-card">
                    <div class="option-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h2>Quick Trade</h2>
                    <p>Buy and sell cryptocurrencies instantly with our simplified interface. Perfect for beginners.</p>
                    <ul class="option-features">
                        <li><i class="fas fa-check"></i> No complex charts</li>
                        <li><i class="fas fa-check"></i> Market orders only</li>
                        <li><i class="fas fa-check"></i> Instant execution</li>
                        <li><i class="fas fa-check"></i> Basic price information</li>
                    </ul>
                    <a href="trading.php" class="btn btn-primary btn-block">Quick Trade</a>
                </div>
                
                <div class="trading-option-card featured">
                    <div class="featured-badge">Recommended</div>
                    <div class="option-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h2>Advanced Trading</h2>
                    <p>Full-featured trading interface with advanced charts, order types, and analysis tools.</p>
                    <ul class="option-features">
                        <li><i class="fas fa-check"></i> TradingView charts</li>
                        <li><i class="fas fa-check"></i> Multiple order types</li>
                        <li><i class="fas fa-check"></i> Technical indicators</li>
                        <li><i class="fas fa-check"></i> Order book depth</li>
                    </ul>
                    <a href="trading.php" class="btn btn-primary btn-block">Advanced Trade</a>
                </div>
                
                <div class="trading-option-card">
                    <div class="option-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h2>Automated Trading</h2>
                    <p>Create trading bots and strategies that execute automatically based on market conditions.</p>
                    <ul class="option-features">
                        <li><i class="fas fa-check"></i> Strategy builder</li>
                        <li><i class="fas fa-check"></i> Backtesting tools</li>
                        <li><i class="fas fa-check"></i> Custom indicators</li>
                        <li><i class="fas fa-check"></i> 24/7 execution</li>
                    </ul>
                    <a href="trading.php" class="btn btn-primary btn-block">Bot Trading</a>
                </div>
            </div>
            
            <div class="trading-comparisons">
                <h2>Compare Trading Options</h2>
                <div class="comparison-table-container">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th>Quick Trade</th>
                                <th>Advanced Trading</th>
                                <th>Automated Trading</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Trading Fee</td>
                                <td>0.2%</td>
                                <td>0.1%</td>
                                <td>0.05%</td>
                            </tr>
                            <tr>
                                <td>Order Types</td>
                                <td>Market</td>
                                <td>Market, Limit, Stop</td>
                                <td>All + Custom</td>
                            </tr>
                            <tr>
                                <td>Charts</td>
                                <td>Basic</td>
                                <td>Advanced</td>
                                <td>Advanced + Backtesting</td>
                            </tr>
                            <tr>
                                <td>Technical Analysis</td>
                                <td><i class="fas fa-times"></i></td>
                                <td><i class="fas fa-check"></i></td>
                                <td><i class="fas fa-check"></i></td>
                            </tr>
                            <tr>
                                <td>API Access</td>
                                <td><i class="fas fa-times"></i></td>
                                <td><i class="fas fa-check"></i></td>
                                <td><i class="fas fa-check"></i></td>
                            </tr>
                            <tr>
                                <td>Mobile App</td>
                                <td><i class="fas fa-check"></i></td>
                                <td><i class="fas fa-check"></i></td>
                                <td><i class="fas fa-check"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="trading-cta-section">
                <div class="cta-box">
                    <h2>Not sure where to start?</h2>
                    <p>Try our crypto trading simulator with virtual funds to practice without risk.</p>
                    <a href="trading.php" class="btn btn-secondary btn-large">Try Demo Trading</a>
                </div>
            </div>
            
            <div class="trading-faq">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-accordion">
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>What's the difference between the trading options?</h3>
                            <div class="faq-toggle">+</div>
                        </div>
                        <div class="faq-answer">
                            <p>Quick Trade is designed for beginners with a simple interface for buying and selling cryptocurrencies with just a few clicks. Advanced Trading offers comprehensive charting tools and order types for experienced traders. Automated Trading allows you to create and deploy trading bots that execute trades based on predefined strategies.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Can I switch between different trading options?</h3>
                            <div class="faq-toggle">+</div>
                        </div>
                        <div class="faq-answer">
                            <p>Yes, you can freely switch between Quick Trade, Advanced Trading, and Automated Trading at any time. Your account balances and trading history are shared across all platforms.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3>Do I need to verify my identity to start trading?</h3>
                            <div class="faq-toggle">+</div>
                        </div>
                        <div class="faq-answer">
                            <p>Yes, identity verification is required for all trading options to comply with regulatory requirements. Basic verification allows trading with limited amounts, while full verification removes these limits.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <div class="footer-logo">
                        <img src="assets/logo.svg" alt="CryptoExchange Logo">
                    </div>
                    <p class="footer-tagline">Trade cryptocurrencies with confidence.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Products</h3>
                    <ul>
                        <li><a href="#">Exchange</a></li>
                        <li><a href="#">Institutional</a></li>
                        <li><a href="#">Wallet</a></li>
                        <li><a href="#">Card</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="#">Learn</a></li>
                        <li><a href="#">Market Updates</a></li>
                        <li><a href="#">API Documentation</a></li>
                        <li><a href="#">Support Center</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Press</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="copyright">
                    <p>&copy; 2025 CryptoExchange. All rights reserved.</p>
                </div>
                <div class="footer-links">
                    <a href="#">Terms</a>
                    <a href="#">Privacy</a>
                    <a href="#">Cookies</a>
                    <a href="#">Disclaimers</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Theme Toggle Script -->
    <script>
        // Check for saved theme preference or use device preference
        const themeSwitch = document.getElementById('theme-switch');
        
        // Function to set a theme
        function setTheme(isDark) {
            if (isDark) {
                document.body.classList.add('dark-theme');
                themeSwitch.checked = true;
            } else {
                document.body.classList.remove('dark-theme');
                themeSwitch.checked = false;
            }
            // Save preference
            localStorage.setItem('darkTheme', isDark);
        }
        
        // Initial theme setup
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('darkTheme');
        
        if (savedTheme !== null) {
            setTheme(savedTheme === 'true');
        } else {
            setTheme(prefersDark);
        }
        
        // Theme switch event listener
        themeSwitch.addEventListener('change', function() {
            setTheme(this.checked);
        });
    </script>
</body>
</html>