CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    birth_date DATE NOT NULL,
    address VARCHAR(255),
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    default_currency ENUM('EUR', 'USD', 'RUB') NOT NULL DEFAULT 'EUR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('phone', 'email') NOT NULL,
    value VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    currency ENUM('EUR', 'USD', 'RUB') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    UNIQUE KEY unique_user_currency (user_id, currency),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS currency_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_currency ENUM('EUR', 'USD', 'RUB') NOT NULL DEFAULT 'EUR',
    to_currency ENUM('EUR', 'USD', 'RUB') NOT NULL,
    rate DECIMAL(15, 6) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pair (from_currency, to_currency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS balance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    currency ENUM('EUR', 'USD', 'RUB') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    balance_before DECIMAL(15, 2) NOT NULL,
    balance_after DECIMAL(15, 2) NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    outcome ENUM('team1_win', 'draw', 'team2_win') NOT NULL,
    odds DECIMAL(10, 2) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    currency ENUM('EUR', 'USD', 'RUB') NOT NULL DEFAULT 'EUR',
    status ENUM('pending', 'won', 'lost') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settled_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO currency_rates (from_currency, to_currency, rate) VALUES
('EUR', 'USD', 1.080000),
('EUR', 'RUB', 98.500000),
('USD', 'EUR', 0.925926),
('USD', 'RUB', 91.203704),
('RUB', 'EUR', 0.010152),
('RUB', 'USD', 0.010964);

INSERT IGNORE INTO users (login, password, name, gender, birth_date, address, status, is_admin, default_currency) VALUES
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'male', '1990-05-15', '123 Main St, City', 'active', 0, 'EUR'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'male', '1985-01-01', '456 Admin Ave', 'active', 1, 'EUR'),
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'female', '1995-08-20', '789 Oak Ln, Town', 'active', 0, 'EUR'),
('bob_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 'male', '1988-12-03', '321 Pine Rd, Village', 'active', 0, 'EUR');

INSERT IGNORE INTO user_contacts (user_id, type, value) VALUES
(1, 'phone', '+1234567890'),
(1, 'email', 'john@example.com'),
(1, 'phone', '+0987654321'),
(2, 'email', 'admin@example.com'),
(2, 'phone', '+1112223333');
INSERT IGNORE INTO user_contacts (user_id, type, value)
SELECT id, 'email', 'jane@example.com' FROM users WHERE login = 'jane_smith' AND NOT EXISTS (SELECT 1 FROM user_contacts WHERE user_id = (SELECT id FROM users WHERE login = 'jane_smith') AND value = 'jane@example.com');
INSERT IGNORE INTO user_contacts (user_id, type, value)
SELECT id, 'phone', '+441234567890' FROM users WHERE login = 'jane_smith' AND NOT EXISTS (SELECT 1 FROM user_contacts WHERE user_id = (SELECT id FROM users WHERE login = 'jane_smith') AND value = '+441234567890');
INSERT IGNORE INTO user_contacts (user_id, type, value)
SELECT id, 'email', 'bob@example.com' FROM users WHERE login = 'bob_wilson' AND NOT EXISTS (SELECT 1 FROM user_contacts WHERE user_id = (SELECT id FROM users WHERE login = 'bob_wilson') AND value = 'bob@example.com');
INSERT IGNORE INTO user_contacts (user_id, type, value)
SELECT id, 'phone', '+499876543210' FROM users WHERE login = 'bob_wilson' AND NOT EXISTS (SELECT 1 FROM user_contacts WHERE user_id = (SELECT id FROM users WHERE login = 'bob_wilson') AND value = '+499876543210');

INSERT IGNORE INTO balances (user_id, currency, amount) VALUES
(1, 'EUR', 1000.00),
(2, 'EUR', 2000.00);
INSERT IGNORE INTO balances (user_id, currency, amount)
SELECT id, 'EUR', 750.00 FROM users WHERE login = 'jane_smith' AND NOT EXISTS (SELECT 1 FROM balances WHERE user_id = (SELECT id FROM users WHERE login = 'jane_smith') AND currency = 'EUR');
INSERT IGNORE INTO balances (user_id, currency, amount)
SELECT id, 'EUR', 500.00 FROM users WHERE login = 'bob_wilson' AND NOT EXISTS (SELECT 1 FROM balances WHERE user_id = (SELECT id FROM users WHERE login = 'bob_wilson') AND currency = 'EUR');
