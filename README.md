# Scoring Application

A simple web application built with the LAMP stack that allows judges to submit scores for participants, with results displayed on a public scoreboard.

## Features

- Public scoreboard showing participant rankings
- Judge login system
- Score submission for judges
- Admin panel for managing judges and participants
- Detailed statistics and results
- Data export functionality

## Installation

1. Clone the repository to your web server directory:
   ```
   git clone https://github.com/stoicdavi/judgesScoreboard.git
   ```

2. Import the database schema:
   ```
   mysql -u root -p < setup.sql
   ```

3. Update the database configuration in `config/db.php` with your credentials.

4. Set proper permissions:
   ```
   chmod -R 755 /path/to/scoring-app
   chown -R www-data:www-data /path/to/scoring-app
   ```

5. Configure your web server to point to the application directory.

## Usage

### Public Access
- Access the public scoreboard at: `http://your-domain.com/`

### Judge Access
- Judges can log in at: `http://your-domain.com/judge/login.php`
- Default credentials:
  - Email: john@example.com
  - Password: password

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
- Admins can log in at: `http://localhost/admin/login.php`
- Default credentials:
  - Username: admin
  - Password: admin123

## Security Notes

For a production environment, make sure to:
1. Change the default admin and judge passwords
2. Use HTTPS
3. Update the database password in `config/db.php`
4. Consider implementing rate limiting for login attempts
