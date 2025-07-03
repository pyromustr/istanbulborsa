<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $success = false;
    try {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // Here you could send a real password reset email
            // mail($email, "Password Reset", "Password reset instructions...");
            $success = true;
        }
    } catch(PDOException $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}

$page_title = "Forgot Password";
$page_description = "Reset your İstanbulBorsa account password by entering your registered email address.";
$page_specific_css = '<style>
    .logo-text {
        text-decoration: none;
        font-size: 24px;
        font-weight: bold;
        color: #000;
    }
    body.dark-theme .logo-text {
        color: #fff;
    }
    .forgot-form {
        max-width: 400px;
        margin: 0 auto;
        padding: var(--space-l);
    }
    .forgot-password {
        display: block;
        text-align: right;
        margin-top: var(--space-s);
        color: var(--color-secondary);
    }
</style>';

include 'header.php';
?>
    <!-- Forgot Password Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-illustration">
                    <img src="assets/login-illustration.svg" alt="Password reset illustration">
                </div>
                <div class="auth-form-container">
                    <div class="auth-form-header">
                        <h2>Forgot Password</h2>
                        <p>Enter your registered email address and we'll send you password reset instructions.</p>
                    </div>
                    <?php if (isset($success) && $success): ?>
                        <p class="success">If this email is registered in our system, password reset instructions have been sent.</p>
                    <?php elseif (isset($error)): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <form class="auth-form forgot-form" method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                        <a href="login.php" class="forgot-password">Return to login</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
<?php include 'footer.php'; ?> 