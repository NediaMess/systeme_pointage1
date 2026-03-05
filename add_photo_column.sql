-- Run this once in phpMyAdmin or MySQL console:
ALTER TABLE users ADD COLUMN IF NOT EXISTS photo VARCHAR(255) DEFAULT NULL;
