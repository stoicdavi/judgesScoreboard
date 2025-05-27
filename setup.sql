-- Create database
CREATE DATABASE IF NOT EXISTS scoring_app;
USE scoring_app;

-- Create tables
CREATE TABLE IF NOT EXISTS judges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judge_id INT NOT NULL,
    participant_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    comments TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (judge_id) REFERENCES judges(id),
    FOREIGN KEY (participant_id) REFERENCES participants(id)
);

-- Create database user
CREATE USER IF NOT EXISTS 'scoring_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON scoring_app.* TO 'scoring_user'@'localhost';
FLUSH PRIVILEGES;

-- Insert sample data
INSERT INTO judges (name, email, password) VALUES 
('John Smith', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password is 'password'
('Jane Doe', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO participants (name, category) VALUES 
('Alice Johnson', 'Singing'),
('Bob Williams', 'Singing'),
('Charlie Brown', 'Dancing'),
('Diana Ross', 'Dancing'),
('Edward Norton', 'Acting'),
('Fiona Apple', 'Acting');

-- Insert some sample scores
INSERT INTO scores (judge_id, participant_id, score, comments) VALUES 
(1, 1, 85.5, 'Great voice control'),
(1, 2, 78.0, 'Good performance but needs more confidence'),
(1, 3, 92.0, 'Excellent choreography'),
(2, 1, 82.0, 'Nice tone but pitch issues'),
(2, 3, 88.5, 'Very energetic performance'),
(2, 5, 95.0, 'Outstanding character portrayal');
