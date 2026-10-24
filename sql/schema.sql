CREATE DATABASE IF NOT EXISTS invito CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE invito;

-- ============ USERS ============
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============ TEMPLATES ============
CREATE TABLE templates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  folder_path VARCHAR(100) NOT NULL UNIQUE,
  thumbnail VARCHAR(255) DEFAULT NULL,
  text_offset VARCHAR(20) NOT NULL DEFAULT 'center',
  text_delay DECIMAL(5,2) NOT NULL DEFAULT 1.25,
  prefix_text VARCHAR(100) NOT NULL DEFAULT 'Mariage de',
  prefix_hidden TINYINT(1) NOT NULL DEFAULT 0,
  font_size_rem DECIMAL(3,1) NOT NULL DEFAULT 1.6,
  prefix_font_size_rem DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  date_font_size_rem DECIMAL(3,1) NOT NULL DEFAULT 0.9,
  date_offset VARCHAR(20) NOT NULL DEFAULT '',
  hero_text_color VARCHAR(50) NOT NULL DEFAULT '#FFFFFF',
  video_loop TINYINT(1) NOT NULL DEFAULT 0,
  has_two_videos TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============ INVITATIONS ============
CREATE TABLE invitations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  template_id INT UNSIGNED DEFAULT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  groom_name VARCHAR(100) NOT NULL DEFAULT '',
  bride_name VARCHAR(100) NOT NULL DEFAULT '',
  groom_parents VARCHAR(200) NOT NULL DEFAULT '',
  bride_parents VARCHAR(200) NOT NULL DEFAULT '',
  wedding_date DATE DEFAULT NULL,
  wedding_time VARCHAR(50) NOT NULL DEFAULT '',
  venue_name VARCHAR(200) NOT NULL DEFAULT '',
  venue_address TEXT,
  map_embed TEXT,
  map_iframe TEXT,
  quote_text TEXT,
  love_story_title VARCHAR(200) NOT NULL DEFAULT 'Our Journey',
  love_story_text TEXT,
  text_offset VARCHAR(20) DEFAULT NULL,
  text_delay DECIMAL(5,2) DEFAULT NULL,
  video_loop TINYINT(1) NOT NULL DEFAULT 0,
  song_path VARCHAR(255) DEFAULT NULL,
  prefix_text VARCHAR(100) DEFAULT NULL,
  prefix_hidden TINYINT(1) DEFAULT NULL,
  font_size_rem DECIMAL(3,1) DEFAULT NULL,
  prefix_font_size_rem DECIMAL(3,1) DEFAULT NULL,
  date_font_size_rem DECIMAL(3,1) DEFAULT NULL,
  date_offset VARCHAR(20) DEFAULT NULL,
  text_color VARCHAR(50) NOT NULL DEFAULT '#2C2420',
  hero_text_color VARCHAR(50) DEFAULT NULL,
  pfp_groom VARCHAR(255) DEFAULT NULL,
  pfp_groom_transform VARCHAR(100) DEFAULT NULL,
  pfp_bride VARCHAR(255) DEFAULT NULL,
  pfp_bride_transform VARCHAR(100) DEFAULT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  invitation_type ENUM('couple','bac','circoncision') NOT NULL DEFAULT 'couple',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============ RSVPS ============
CREATE TABLE rsvps (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invitation_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  guests TINYINT UNSIGNED NOT NULL DEFAULT 1,
  attendance ENUM('yes','no') NOT NULL,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============ USER TOKENS ============
CREATE TABLE user_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token VARCHAR(128) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============ RATE LIMITS ============
CREATE TABLE rate_limits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rate_key VARCHAR(255) NOT NULL,
  attempts INT NOT NULL DEFAULT 1,
  expires_at DATETIME NOT NULL,
  INDEX idx_rate_key (rate_key),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_tokens_token ON user_tokens(token);
CREATE INDEX idx_tokens_expires ON user_tokens(expires_at);
CREATE INDEX idx_invitations_slug ON invitations(slug);
CREATE INDEX idx_rsvps_invitation ON rsvps(invitation_id);

-- ============ SEED DATA ============
-- Default admin: admin / Admin@123456 (change immediately after first login)
INSERT INTO users (username, password_hash, email, role) VALUES
('admin', '$2y$12$viIcJvjFiq20mERFSsQsWurHRZPCKxx7o.2wABB8W6ssgjBomIOh.', 'admin@invito.app', 'admin');

-- Default templates
INSERT INTO templates (name, folder_path, thumbnail, hero_text_color) VALUES
('Template Classic', 'template1', 'templates/template1/thumbnail.jpg', '#FFFFFF');
INSERT INTO templates (name, folder_path, text_offset, text_delay, prefix_text, prefix_hidden, font_size_rem, date_offset, hero_text_color) VALUES
('Template Cinematic', 'template2', 'bottom-third', 0.5, 'Mariage de', 0, 1.6, '', '#FFFFFF');
INSERT INTO templates (name, folder_path, thumbnail, text_offset, text_delay, prefix_text, prefix_hidden, font_size_rem, date_offset, hero_text_color) VALUES
('Baccalaureate Surprise', 'baccalaureate', 'templates/baccalaureate/thumbnail.jpg', 'bottom-third', 1.25, '', 1, 1.6, '', '#FFFFFF');
INSERT INTO templates (name, folder_path, text_offset, text_delay, prefix_text, prefix_hidden, font_size_rem, date_offset, hero_text_color) VALUES
('Circoncision', 'thor', 'center', 1.25, '', 1, 1.6, '', '#FFFFFF');

-- Default invitation for admin demo
INSERT INTO invitations (user_id, template_id, slug, groom_name, bride_name, wedding_date, wedding_time, venue_name, map_embed) VALUES
(1, 1, 'demo-wedding', 'Amine', 'Sarah', '2026-12-25', '10:00 - 15:00', 'Alger, Algérie', 'https://maps.app.goo.gl/M7QMusLfx51wKBdt5');
