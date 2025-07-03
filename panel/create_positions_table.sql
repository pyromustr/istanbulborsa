-- Positions tablosu - Açılan pozisyonları takip etmek için
DROP TABLE IF EXISTS positions;
CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    asset_symbol VARCHAR(10) NOT NULL,
    position_type ENUM('Buy', 'Sell') NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    open_date TIMESTAMP NOT NULL,
    open_price DECIMAL(18,2) NOT NULL,
    close_date TIMESTAMP NULL,
    close_price DECIMAL(18,2) NULL,
    status ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
    admin_position_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Admin pozisyonları tablosu - Yönetici tarafından açılan ana pozisyonlar
DROP TABLE IF EXISTS admin_positions;
CREATE TABLE admin_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_symbol VARCHAR(10) NOT NULL,
    position_type ENUM('Buy', 'Sell') NOT NULL,
    percentage_of_balance DECIMAL(5,2) NOT NULL,
    open_date TIMESTAMP NOT NULL,
    open_price DECIMAL(18,2) NOT NULL,
    close_date TIMESTAMP NULL,
    close_price DECIMAL(18,2) NULL,
    status ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open'
);

-- Başarı mesajı
SELECT 'Positions tabloları başarıyla güncellendi!' as message; 