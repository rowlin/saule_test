-- Apply to existing database:
-- docker exec -i saule_database_1 mysql -u root -psecret saule_betting < sql/migration.sql

ALTER TABLE balance_logs
    ADD FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD INDEX idx_admin_id (admin_id),
    ADD INDEX idx_created_at (created_at);

ALTER TABLE bets
    ADD INDEX idx_user_status (user_id, status);
