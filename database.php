CREATE DATABASE history_timeline;
USE history_timeline;

-- Admin table
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE historical_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    date DATE NOT NULL,
    location VARCHAR(255),
    description TEXT,
    category VARCHAR(100),
    significance TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO admin (username, password, email) VALUES 
('admin', '$2y$10$YourHashedPassword', 'admin@timeline.com');

-- Insert sample events
INSERT INTO historical_events (title, year, date, location, description, category) VALUES
('Indus Valley Civilization', -2500, '2024-01-01', 'Harappa, Pakistan', 'One of the world''s earliest urban civilizations', 'Ancient'),
('Maurya Empire', -322, '2024-01-01', 'Pataliputra', 'Founded by Chandragupta Maurya', 'Empire'),
('Mughal Empire Begins', 1526, '2024-01-01', 'Panipat', 'Babur establishes Mughal Empire', 'Empire'),
('Indian Independence', 1947, '1947-08-15', 'Delhi', 'India gains independence from British rule', 'Modern');

ALTER TABLE historical_events 
ADD COLUMN image_path VARCHAR(255) NULL AFTER significance;

-- User Favorites Table
CREATE TABLE IF NOT EXISTS user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES historical_events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, event_id)
);

-- User History Table
CREATE TABLE IF NOT EXISTS user_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES historical_events(id) ON DELETE CASCADE
);
