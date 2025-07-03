<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    try {
        $sql = "INSERT INTO users (name, surname, email, phone, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $surname, $email, $phone, $password]);
        header("Location: login.php");
        exit();
    } catch(PDOException $e) {
        echo "Kayıt hatası: " . $e->getMessage();
    }
}
$page_title = "Register";
$page_description = "Create a new account on İstanbulBorsa to start trading cryptocurrencies securely.";
include 'header.php';
?>

    <!-- Register Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-illustration">
                    <img src="assets/register-illustration.svg" alt="Registration illustration">
                </div>
                <div class="auth-form-container">
                    <div class="auth-form-header">
                        <h2>Create Account</h2>
                        <p>Join thousands of traders on our platform</p>
                    </div>
                    <form class="auth-form" method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="surname">Surname</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="surname" name="surname" placeholder="Enter your surname" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" placeholder="Enter your phone" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Create a password" required>
                            </div>
                        </div>
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    </form>
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Log In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'footer.php'; ?>