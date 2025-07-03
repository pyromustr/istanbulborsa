<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['user_name'] = $user['name'] . ' ' . $user['surname'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } catch(PDOException $e) {
        echo "Login error: " . $e->getMessage();
    }
}

$page_title = "Login";
$page_description = "Log in to your İstanbulBorsa account to trade cryptocurrencies securely.";
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
    .login-form {
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

    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-illustration">
                    <img src="assets/login-illustration.svg" alt="Login illustration">
                </div>
                <div class="auth-form-container">
                    <div class="auth-form-header">
                        <h2>Welcome Back</h2>
                        <p>Log in to access your account</p>
                    </div>
                    <form class="auth-form login-form" method="POST" action="">
                        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>
                        <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                        <button type="submit" class="btn btn-primary btn-block">Log In</button>
                    </form>
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register.php">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'footer.php'; ?>