CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    gender VARCHAR(10) NOT NULL CHECK (gender IN ('male', 'female')),
    birth_date DATE NOT NULL,
    address VARCHAR(255),
    status VARCHAR(10) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'blocked')),
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    default_currency VARCHAR(3) NOT NULL DEFAULT 'EUR' CHECK (default_currency IN ('EUR', 'USD', 'RUB')),
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS user_contacts (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(10) NOT NULL CHECK (type IN ('phone', 'email')),
    value VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS balances (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    currency VARCHAR(3) NOT NULL CHECK (currency IN ('EUR', 'USD', 'RUB')),
    amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    UNIQUE (user_id, currency)
);

CREATE TABLE IF NOT EXISTS currency_rates (
    id SERIAL PRIMARY KEY,
    from_currency VARCHAR(3) NOT NULL DEFAULT 'EUR' CHECK (from_currency IN ('EUR', 'USD', 'RUB')),
    to_currency VARCHAR(3) NOT NULL CHECK (to_currency IN ('EUR', 'USD', 'RUB')),
    rate DECIMAL(15, 6) NOT NULL,
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (from_currency, to_currency)
);

CREATE TABLE IF NOT EXISTS balance_logs (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    admin_id INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(50) NOT NULL,
    currency VARCHAR(3) NOT NULL CHECK (currency IN ('EUR', 'USD', 'RUB')),
    amount DECIMAL(15, 2) NOT NULL,
    balance_before DECIMAL(15, 2) NOT NULL,
    balance_after DECIMAL(15, 2) NOT NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_balance_logs_admin_id ON balance_logs(admin_id);
CREATE INDEX IF NOT EXISTS idx_balance_logs_created_at ON balance_logs(created_at);

CREATE TABLE IF NOT EXISTS bets (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_name VARCHAR(255) NOT NULL,
    outcome VARCHAR(10) NOT NULL CHECK (outcome IN ('team1_win', 'draw', 'team2_win')),
    odds DECIMAL(10, 2) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR' CHECK (currency IN ('EUR', 'USD', 'RUB')),
    status VARCHAR(10) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'won', 'lost')),
    created_at TIMESTAMP DEFAULT NOW(),
    settled_at TIMESTAMP DEFAULT NULL
);

CREATE INDEX IF NOT EXISTS idx_bets_user_status ON bets(user_id, status);

INSERT INTO currency_rates (from_currency, to_currency, rate) VALUES
('EUR', 'USD', 1.080000),
('EUR', 'RUB', 98.500000),
('USD', 'EUR', 0.925926),
('USD', 'RUB', 91.203704),
('RUB', 'EUR', 0.010152),
('RUB', 'USD', 0.010964)
ON CONFLICT (from_currency, to_currency) DO NOTHING;

INSERT INTO users (login, password, name, gender, birth_date, address, status, is_admin, default_currency) VALUES
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'male', '1990-05-15', '123 Main St, City', 'active', FALSE, 'EUR'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'male', '1985-01-01', '456 Admin Ave', 'active', TRUE, 'EUR'),
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'female', '1995-08-20', '789 Oak Ln, Town', 'active', FALSE, 'EUR'),
('bob_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 'male', '1988-12-03', '321 Pine Rd, Village', 'active', FALSE, 'EUR')
ON CONFLICT (login) DO NOTHING;

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'phone', '+1234567890' FROM users WHERE login = 'john_doe';

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'email', 'john@example.com' FROM users WHERE login = 'john_doe';

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'phone', '+0987654321' FROM users WHERE login = 'john_doe';

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'email', 'admin@example.com' FROM users WHERE login = 'admin';

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'phone', '+1112223333' FROM users WHERE login = 'admin';

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'email', 'jane@example.com' FROM users WHERE login = 'jane_smith';

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'phone', '+441234567890' FROM users WHERE login = 'jane_smith';

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'email', 'bob@example.com' FROM users WHERE login = 'bob_wilson';

INSERT INTO user_contacts (user_id, type, value)
SELECT id, 'phone', '+499876543210' FROM users WHERE login = 'bob_wilson';

INSERT INTO balances (user_id, currency, amount)
SELECT id, 'EUR', 1000.00 FROM users WHERE login = 'john_doe'
ON CONFLICT (user_id, currency) DO NOTHING;

INSERT INTO balances (user_id, currency, amount)
SELECT id, 'EUR', 2000.00 FROM users WHERE login = 'admin'
ON CONFLICT (user_id, currency) DO NOTHING;

INSERT INTO balances (user_id, currency, amount)
SELECT id, 'EUR', 750.00 FROM users WHERE login = 'jane_smith'
ON CONFLICT (user_id, currency) DO NOTHING;

INSERT INTO balances (user_id, currency, amount)
SELECT id, 'EUR', 500.00 FROM users WHERE login = 'bob_wilson'
ON CONFLICT (user_id, currency) DO NOTHING;
