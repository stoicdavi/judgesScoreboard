# Scoring Application

A simple web application built with the LAMP stack that allows judges to submit scores for participants, with results displayed on a public scoreboard.

## Features

- Public scoreboard showing participant rankings
- Judge login system
- Score submission for judges
- Admin panel for managing judges and participants
- Detailed statistics and results
- Data export functionality

## Requirements

### Server Requirements
- Linux server
- Apache 2.4+
- MySQL 5.7+ or MariaDB 10.2+
- PHP 7.4+ or PHP 8.0+

### PHP Extensions
- php-mysql
- php-json
- php-mbstring
- php-xml
- php-curl


## Installation

1. Install required packages (Ubuntu/Debian):
   ```bash
   # Update package lists
   sudo apt update
   
   # Install Apache, MySQL, PHP and required extensions
   sudo apt install apache2 mysql-server php php-mysql php-json php-mbstring php-xml php-curl
   
   # Enable Apache mod_rewrite
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. Clone the repository to your web server directory:
   ```bash
   git clone https://github.com/stoicdavi/judgesScoreboard.git
   cd judgesScoreboard
   ```

3. Import the database schema:
   ```bash
   mysql -u root -p < setup.sql
   ```

4. Update the database configuration in `config/db.php` with your credentials.

5. Set proper permissions:
   ```bash
   sudo chmod -R 755 /path/to/scoring-app
   sudo chown -R www-data:www-data /path/to/scoring-app
   ```
6. Starting the application
   ```bash
   php -S localhost:8000
   ```

## Usage

### Public Access
- Access the public scoreboard at: `http://localhost:8000/`

### Judge Access
- Judges can log in at: `http://localhost:8000/judge/login.php`
- Default credentials:
  - Email: john@example.com
  - Password: password
#### Judges page
![image](https://github.com/user-attachments/assets/9d253f94-6718-4f34-b7eb-29172fa75e0a)


#### Scoring Process for Judges
1. After logging in, judges will be directed to their dashboard
2. Select the event or competition from the available list
3. View the list of participants assigned to you for scoring
4. Click on a participant's name to access the scoring form
5. Enter scores for each criterion (typically on a scale of 1-10)
6. Add optional comments to justify your scoring decisions
7. Submit the scores by clicking the "Submit" button
8. Scores can be edited until the competition is marked as complete by an admin

### Admin Access
- Admins can log in at: `http://localhost:8000/admin/login.php`
- Default credentials:
  - Username: admin
  - Password: admin123
    - Admin page
  ![image](https://github.com/user-attachments/assets/2c662dc8-4d81-46ac-9bbe-591227ee8785)


## Security Notes

For a production environment, make sure to:
1. Change the default admin and judge passwords
2. Use HTTPS
3. Update the database password in `config/db.php`
4. Consider implementing rate limiting for login attempts
