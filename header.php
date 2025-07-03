<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

$header_css = '<style>
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
        background-color: var(--sidebar-bg) !important;
        min-width: 180px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1000;
        border-radius: 8px;
        border: 1px solid var(--color-border);
        margin-top: 0px;
        padding-top: 7px;
        flex-direction: column;
    }
    .dropdown-content a {
        color: var(--sidebar-text) !important;
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
        background-color: var(--sidebar-hover) !important;
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
        background-color: var(--sidebar-hover);
    }
    .user-profile .profile-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: var(--color-primary);
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
        color: var(--sidebar-text) !important;
    }
    /* Dashboard Layout */
    .dashboard-container {
        display: flex;
        min-height: calc(100vh - 64px);
        background-color: var(--color-bg);
        margin-top: 64px;
        transition: all 0.3s ease;
    }
    
    /* Header visibility */
    .header {
        display: block !important;
        position: relative;
        z-index: 1001;
    }
    
    /* Sidebar ve dashboard özel stilleri burada kalacak, kullanıcı menüsü ile ilgili olanlar kaldırıldı */
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
    .dashboard-container.sidebar-collapsed {
        grid-template-columns: 80px 1fr;
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
    
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .dashboard-card {
        background-color: var(--color-bg-card);
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--color-border);
    }
    
    .card-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 10px;
        color: white;
    }
    
    .card-icon.primary { background-color: var(--color-primary); }
    .card-icon.success { background-color: var(--color-success); }
    .card-icon.warning { background-color: var(--color-warning); }
    .card-icon.danger { background-color: var(--color-danger); }
    
    .card-label {
        font-size: 13px;
        color: var(--color-text-muted);
        margin-bottom: 3px;
    }
    
    .card-value {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .card-change {
        font-size: 12px;
    }
    
    .card-change.positive { color: var(--color-success); }
    .card-change.negative { color: var(--color-danger); }
    
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
    
    .asset-info {
        display: flex;
        align-items: center;
        min-width: 120px;
    }
    
    .crypto-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: var(--color-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
        font-size: 14px;
    }
    
    .asset-name {
        font-weight: 600;
        margin-right: 5px;
    }
    
    .asset-ticker {
        color: var(--color-text-muted);
        font-size: 12px;
    }
    
    .positive { color: var(--color-success); }
    .negative { color: var(--color-danger); }
    
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
    
    .asset-actions {
        display: flex;
        gap: 5px;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .dashboard-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .dashboard-container {
            flex-direction: column;
        }
        
        .sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid var(--color-border);
            padding: 15px 0;
        }
        
        .sidebar-header {
            padding: 0 15px 15px;
        }
        
        .nav-item a {
            padding: 12px 15px;
        }
        
        .main-content {
            padding: 15px;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .dashboard-cards {
            grid-template-columns: 1fr;
        }
        
        .transaction-section {
            padding: 15px;
        }
        
        .transaction-table th, .transaction-table td {
            padding: 10px 8px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 576px) {
        .dashboard-container {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
            position: relative;
        }
    }
    
    @media (max-width: 480px) {
        .user-avatar {
            width: 40px;
            height: 40px;
            font-size: 16px;
            margin-right: 10px;
        }
        
        .user-name {
            font-size: 14px;
        }
        
        .user-email {
            font-size: 11px;
        }
        
        .card-value {
            font-size: 20px;
        }
        
        .transaction-table {
            min-width: 500px;
        }
    }
    .mobile-menu,
    .mobile-menu-bg {
        display: none;
    }
    @media (max-width: 768px) {
        .main-header {
            padding: 0 10px;
        }
        .main-header .header-right {
            display: none;
        }
        .hamburger {
            display: flex;
        }
        .main-header .header-left {
            gap: 8px;
        }
        .mobile-menu {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 80vw;
            max-width: 320px;
            background: #fff;
            z-index: 1200;
            box-shadow: 2px 0 24px rgba(0,0,0,0.12);
            padding: 32px 0 24px 0;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(.4,0,.2,1);
        }
        .mobile-menu.open {
            transform: translateX(0);
        }
        .mobile-menu-bg {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.25);
            z-index: 1199;
        }
        .mobile-menu-bg.open {
            display: block;
        }
        .dropdown-menu.show {
            display: flex;
        }
    }
    .profile-dropdown {
        position: relative;
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    .profile-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #0984E3;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        margin-right: 8px;
    }
    .profile-name {
        font-weight: 600;
        color: #222;
        margin-right: 6px;
    }
    .dropdown-arrow {
        font-size: 14px;
        color: #666;
        cursor: pointer;
    }
    .dropdown-menu {
        display: none;
        position: absolute;
        left: 0;
        top: 48px;
        min-width: 160px;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        z-index: 1001;
        flex-direction: column;
        padding: 8px 0;
    }
    .dropdown-menu a {
        padding: 10px 18px;
        color: #222;
        text-decoration: none;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }
    .dropdown-menu a:hover {
        background: #f5faff;
        color: #0984E3;
    }
    @media (min-width: 769px) {
        .main-header .logo {
            margin-left: 80px;
        }
        /* Hover ile açılma kaldırıldı, sadece tıklama ile açılacak */
        .dropdown-menu.show {
            display: flex !important;
        }
    }
</style>';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - İstanbulBorsa' : 'İstanbulBorsa - Trade Cryptocurrency with Confidence'; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Trade Bitcoin, Ethereum and other cryptocurrencies securely with İstanbulBorsa.'; ?>">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/additional-styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <style>
        .main-header {
            width: 100%;
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            padding: 0 32px;
        }
        .main-header .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .main-header .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            font-size: 22px;
            font-weight: bold;
            color: #222;
        }
        .main-header .logo img {
            height: 38px;
            margin-right: 10px;
        }
        .main-header .header-right {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-left: auto;
            justify-content: flex-end;
            margin-right: 80px;
        }
        .main-header .nav-links {
            display: flex;
            gap: 18px;
        }
        .main-header .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.2s;
        }
        .main-header .nav-links a:hover {
            color: #0984E3;
        }
        .main-header .auth-buttons {
            display: flex;
            gap: 10px;
        }
        .main-header .auth-buttons a {
            padding: 7px 18px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #0984E3;
            color: #0984E3;
            background: #fff;
            transition: background 0.2s, color 0.2s;
        }
        .main-header .auth-buttons a.btn-primary {
            background: #0984E3;
            color: #fff;
        }
        .main-header .auth-buttons a.btn-primary:hover {
            background: #065ea6;
            color: #fff;
        }
        .main-header .auth-buttons a.btn-secondary:hover {
            background: #f0f6ff;
        }
        /* Profil ve Dropdown */
        .profile-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .profile-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #0984E3;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-right: 8px;
        }
        .profile-name {
            font-weight: 600;
            color: #222;
            margin-right: 6px;
        }
        .dropdown-arrow {
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            left: 0;
            top: 48px;
            min-width: 160px;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            z-index: 1001;
            flex-direction: column;
            padding: 8px 0;
        }
        .dropdown-menu a {
            padding: 10px 18px;
            color: #222;
            text-decoration: none;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .dropdown-menu a:hover {
            background: #f5faff;
            color: #0984E3;
        }
        /* Hamburger Menü */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            width: 38px;
            height: 38px;
            cursor: pointer;
            z-index: 1101;
        }
        .hamburger span {
            height: 2px;
            width: 80%;
            background: #000000;
            margin: 5px 0;
            border-radius: 2px;
            transition: 0.3s;
        }
        /* Mobilde menü kapalıyken gizle */
        @media (max-width: 768px) {
            .main-header {
                padding: 0 10px;
            }
            .main-header .header-right {
                display: none;
            }
            .hamburger {
                display: flex;
            }
            .main-header .header-left {
                gap: 8px;
            }
            .mobile-menu {
                display: flex;
                flex-direction: column;
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 80vw;
                max-width: 320px;
                background: #fff;
                z-index: 1200;
                box-shadow: 2px 0 24px rgba(0,0,0,0.12);
                padding: 32px 0 24px 0;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(.4,0,.2,1);
            }
            .mobile-menu.open {
                transform: translateX(0);
            }
            .mobile-menu .nav-links {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                width: 100%;
                gap: 0;
            }
            .mobile-menu .nav-links a {
                display: flex;
                align-items: center;
                width: 100%;
                box-sizing: border-box;
                padding: 16px 24px;
                font-size: 17px;
                border-radius: 0;
                border: none;
                text-align: left;
                border-bottom: 1px solid #f0f0f0;
                margin: 0;
                color: #333;
                font-weight: 500;
                transition: color 0.2s, background-color 0.2s;
            }
            .mobile-menu .nav-links a i {
                margin-right: 16px;
                width: 20px;
                text-align: center;
                color: #666;
            }
            .mobile-menu .nav-links a:hover {
                color: #0984E3;
                background-color: #f5faff;
            }
            .mobile-menu .nav-links a:hover i {
                color: #0984E3;
            }
            .mobile-menu .nav-links a:last-child {
                border-bottom: none;
            }
            .mobile-menu .auth-buttons {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                width: 100%;
                gap: 12px;
                margin-top: 24px;
                padding: 0 24px;
                box-sizing: border-box;
            }
            .mobile-menu .auth-buttons a {
                display: block;
                width: 100%;
                box-sizing: border-box;
                text-align: center;
                padding: 12px 18px;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 600;
                text-decoration: none;
                transition: background 0.2s, color 0.2s;
            }
            .mobile-menu .auth-buttons a.btn-secondary {
                border: 1px solid #0984E3;
                color: #0984E3;
                background: #fff;
            }
             .mobile-menu .auth-buttons a.btn-secondary:hover {
                background: #f0f6ff;
            }
            .mobile-menu .auth-buttons a.btn-primary {
                background: #0984E3;
                color: #fff;
                border: 1px solid #0984E3;
            }
            .mobile-menu .auth-buttons a.btn-primary:hover {
                background: #065ea6;
            }
            .mobile-menu .profile-dropdown {
                margin-top: 10px;
            }
            .mobile-menu .dropdown-menu {
                position: static;
                box-shadow: none;
                border: none;
                min-width: 100%;
                border-radius: 0;
                padding: 0;
            }
            .mobile-menu .dropdown-menu a {
                display: block;
                width: 100%;
                box-sizing: border-box;
                padding: 12px 32px;
                font-size: 17px;
                margin: 0;
            }
            .mobile-menu-bg {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.25);
                z-index: 1199;
            }
            .mobile-menu-bg.open {
                display: block;
            }
        }
        @media (max-width: 400px) {
            .main-header .logo img {
                height: 28px;
                margin-right: 6px;
            }
            .main-header .logo {
                font-size: 15px;
            }
        }
        body { padding-top: 64px; }
    </style>
    <?php if(isset($header_css)): ?>
    <?php echo $header_css; ?>
    <?php endif; ?>
    <!-- TradingView Widget -->
    <?php if(isset($include_tradingview) && $include_tradingview): ?>
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
    <?php endif; ?>
</head>
<body>
    <header class="main-header">
        <div class="header-left">
            <div class="hamburger" id="hamburgerMenu" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <a href="index.php" class="logo">
                <img src="assets/smart_investing.png" alt="İstanbulBorsa Logo">
            </a>
        </div>
        <div class="header-right" id="desktopMenu">
            <nav class="nav-links">
                <a href="index.php">Ana Sayfa</a>
                <a href="markets.php">Piyasalar</a>
                <a href="start-trading.php">Ticaret Yap</a>
                <a href="wallet.php">Varlıklar</a>
            </nav>
            <?php if(!isset($_SESSION['user_id'])): ?>
            <div class="auth-buttons">
                <a href="login.php" class="btn-secondary">Giriş Yap</a>
                <a href="register.php" class="btn-primary">Kayıt Ol</a>
            </div>
            <?php else: ?>
            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-icon" onclick="toggleDropdown()">
                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : '?'; ?>
                </div>
                <span class="profile-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Kullanıcı'; ?></span>
                <span class="dropdown-arrow" onclick="toggleDropdown()"><i class="fas fa-chevron-down"></i></span>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>
    <div id="mobileMenuBg" class="mobile-menu-bg" onclick="toggleMobileMenu()"></div>
    <div id="mobileMenu" class="mobile-menu">
        <div style="width:100%;display:flex;justify-content:center;align-items:center;margin-bottom:12px;">
            <a href="index.php" class="logo" style="display:flex;align-items:center;justify-content:center;">
                <img src="assets/smart_investing.png" alt="İstanbulBorsa Logo" style="height:38px;">
            </a>
        </div>
        <nav class="nav-links">
            <a href="index.php">Ana Sayfa</a>
            <a href="markets.php">Piyasalar</a>
            <a href="start-trading.php">Ticaret Yap</a>
            <a href="wallet.php">Varlıklar</a>
        </nav>
        <?php if(!isset($_SESSION['user_id'])): ?>
        <div class="auth-buttons">
            <a href="login.php" class="btn-secondary">Giriş Yap</a>
            <a href="register.php" class="btn-primary">Kayıt Ol</a>
        </div>
        <?php else: ?>
        <nav class="nav-links" style="margin-top: 12px; border-top: 1px solid #f0f0f0; padding-top: 12px;">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
        </nav>
        <?php endif; ?>
    </div>
    <script>
        // Sadece profile-name tıklanınca aç/kapat
        function toggleDropdown() {
            var menu = document.getElementById('dropdownMenu');
            if(menu) menu.classList.toggle('show');
        }
        var profileName = document.querySelector('#profileDropdown .profile-name');
        if(profileName) {
            profileName.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDropdown();
            });
        }
        // Hamburger ve mobil menü
        function toggleMobileMenu() {
            var menu = document.getElementById('mobileMenu');
            var bg = document.getElementById('mobileMenuBg');
            var isOpen = menu.classList.contains('open');
            if(isOpen) {
                menu.classList.remove('open');
                bg.classList.remove('open');
            } else {
                menu.classList.add('open');
                bg.classList.add('open');
            }
        }
        // Mobil profil dropdown
        function toggleMobileDropdown() {
            var menu = document.getElementById('mobileDropdownMenu');
            if(menu) menu.classList.toggle('show');
        }
        document.addEventListener('click', function(event) {
            var dropdown = document.getElementById('mobileDropdownMenu');
            var profile = document.getElementById('mobileProfileDropdown');
            var hamburger = document.getElementById('hamburgerMenu');
            var mobileMenu = document.getElementById('mobileMenu');
            if(dropdown && profile && !profile.contains(event.target) && !hamburger.contains(event.target) && !mobileMenu.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
    <main>
        <!-- Sayfa içeriği buradan başlar -->
    </main>
</body>
</html> 