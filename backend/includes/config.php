<?php
// Configuration - edit before use
define('DB_HOST','127.0.0.1');
define('DB_NAME','play2earnx');
define('DB_USER','root');
define('DB_PASS','');
define('SMTP_HOST',''); // smtp.example.com
define('SMTP_PORT',587);
define('SMTP_USER','');
define('SMTP_PASS','');
define('MAIL_FROM','no-reply@example.com');
define('MAIL_FROM_NAME','Play2EarnX');

// OTP & rate limit
define('OTP_EXPIRY_SECONDS',300);
define('OTP_RATE_LIMIT_MAX',3);
define('OTP_RATE_LIMIT_WINDOW',60);
define('DEBUG_SHOW_OTP',true);

// Claim rules
define('POINTS_PER_TOKEN',100);
define('TOKEN_DECIMALS',18);
define('CONTRACT_ADDRESS','0xYourContractAddress'); // used for claim_hash
define('BACKEND_SIGNER_URL',''); // optional: node signing server URL

define('CLAIM_RATE_LIMIT_MAX',5);
define('CLAIM_RATE_LIMIT_WINDOW',10);
define('MAX_SCORE_PER_CLAIM',10000);
define('DAILY_CLAIM_CAP',1000);

// Market fees
define('MARKET_FEE_RATE',0.03); // 3%
define('MARKET_FEE_BURN',0.02); // 2% burn if used
