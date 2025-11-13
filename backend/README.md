Play2EarnX PHP Backend (OTP + Claim + Market Sim)


This package provides a PHP-based backend with MySQL for the Play2EarnX economy simulation.

Files of interest:
- api/request-otp.php      (POST {email})
- api/verify-otp.php       (POST {email, otp}) -> returns {token, user_id}
- api/claim.php            (POST {token, score, address}) -> create claim & optionally call signing server
- api/buy.php              (POST {token, tokens}) -> simulate buy
- api/sell.php             (POST {token, tokens}) -> simulate sell
- includes/config.php     (edit DB, SMTP, rules)
- includes/db.php         (PDO helper)
- includes/rate_limiter.php
- includes/readme_db.sql  (DB schema)

Quick start:
1. Copy files to your PHP server root, e.g. /var/www/html/play2earnx
2. Edit includes/config.php to set DB and SMTP values
3. Create DB and tables: mysql -u root -p < includes/readme_db.sql
4. Install PHPMailer via composer in the root folder: composer require phpmailer/phpmailer
5. Call POST /api/request-otp.php with JSON {"email":"you@example.com"}
6. Enter OTP via POST /api/verify-otp.php {"email":"you@example.com","otp":"123456"}
7. Use returned token for claim/buy/sell endpoints

Notes:
- If BACKEND_SIGNER_URL in config is set, claim.php will call that endpoint to get an on-chain signature.
- Rate limiting is file-based temporary solution. For production use Redis or DB-based limiter.
- All DB queries use prepared statements to avoid SQL injection.
