<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini çek
$stmt = $conn->prepare('SELECT name, surname, email, phone FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $surname === '' || $email === '' || $phone === '') {
        $error = 'Tüm alanları doldurmalısınız!';
    } else {
        // E-posta başka kullanıcıya ait mi kontrolü
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'Bu e-posta başka bir kullanıcıya ait!';
        } else {
            $stmt = $conn->prepare('UPDATE users SET name = ?, surname = ?, email = ?, phone = ? WHERE id = ?');
            if ($stmt->execute([$name, $surname, $email, $phone, $user_id])) {
                $success = 'Profiliniz başarıyla güncellendi!';
                // Oturum bilgilerini de güncelle
                $_SESSION['username'] = $name;
                $_SESSION['user_name'] = $name . ' ' . $surname;
                $_SESSION['user_email'] = $email;
                // Son bilgileri tekrar çek
                $user = ['name' => $name, 'surname' => $surname, 'email' => $email, 'phone' => $phone];
            } else {
                $error = 'Güncelleme sırasında hata oluştu!';
            }
        }
    }
}

$page_title = 'Profilim';
$page_description = 'Kullanıcı profil bilgilerinizi görüntüleyin ve güncelleyin.';
$page_specific_css = '<style>
.profile-form { max-width: 400px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); padding: 32px; }
.profile-form h2 { text-align: center; margin-bottom: 24px; }
.profile-form .form-group { margin-bottom: 18px; }
.profile-form label { font-weight: 600; display: block; margin-bottom: 6px; }
.profile-form input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #e0e0e0; font-size: 15px; }
.profile-form button { width: 100%; margin-top: 18px; }
.profile-form .success { color: #00B894; text-align: center; margin-bottom: 12px; }
.profile-form .error { color: #D63031; text-align: center; margin-bottom: 12px; }
</style>';

include 'header.php';
?>
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-info">
                <div class="user-avatar">
                    <?php 
                    $initials = "";
                    $name_parts = explode(" ", $_SESSION['user_name']);
                    foreach($name_parts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    echo $initials;
                    ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo $_SESSION['user_name']; ?></div>
                    <div class="user-email"><?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'user@example.com'; ?></div>
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
            <li class="nav-item">
                <a href="trading.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Trade</span>
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
            <li class="nav-item active">
                <a href="profile.php">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>
    <!-- Main Content -->
    <main class="main-content">
        <form class="profile-form" method="POST">
            <h2>Profil Bilgilerim</h2>
            <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            <div class="form-group">
                <label for="name">Ad</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="surname">Soyad</label>
                <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($user['surname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Telefon</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </form>
    </main>
</div>
<?php include 'footer.php'; ?> 