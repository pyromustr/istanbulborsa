    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <div class="footer-logo">
                        <img src="assets/logo.svg" alt="İstanbulBorsa Logo">
                    </div>
                    <p class="footer-tagline">Trade cryptocurrencies with confidence.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Telegram"><i class="fab fa-telegram"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Products</h3>
                    <ul>
                        <li><a href="#">Exchange</a></li>
                        <li><a href="#">Institutional Services</a></li>
                        <li><a href="#">Mobile App</a></li>
                        <li><a href="#">Wallet</a></li>
                        <li><a href="#">API</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="#">Market Data</a></li>
                        <li><a href="#">Trading Guide</a></li>
                        <li><a href="#">Crypto News</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Status</a></li>
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
                    <p>&copy; <?php echo date('Y'); ?> İstanbulBorsa. All rights reserved.</p>
                </div>
                <div class="footer-links">
                    <a href="#">Terms of Service</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Cookies</a>
                    <a href="#">Legal Disclaimers</a>
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

    <style>
        @media (max-width: 576px) {
            .footer-grid {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            .footer-column {
                align-items: center;
            }
            .social-links {
                justify-content: center;
            }
            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</body>
</html> 