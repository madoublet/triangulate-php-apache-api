<?php

	// define brand
	define('BRAND', 'Triangulate');
	define('BRAND_LOGO', 'https://app.triangulate.io/images/triangulate-icon.png');
	

	// DB connection parameters
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'triangulate');
	define('DB_USER', 'dbuser');
	define('DB_PASSWORD', 'dbpass');
	
	// S3 deployment options
	
	// enables copying site to S3 for deployment
	define('ENABLE_S3_DEPLOYMENT', false);
	
	// stores all uploaded files on S3
	define('FILES_ON_S3', false);
	
	// default bucket
	define('BUCKET_NAME', '{{site}}.yourdomain.com');
	define('S3_LOCATION', 'us-east-1');
	define('S3_URL', 'http://{{bucket}}.s3-website-us-east-1.amazonaws.com');
	define('S3_KEY', 'AWS ACCESS KEY');
	define('S3_SECRET', 'AWS SECRET KEY');
	
	// URLs
	define('APP_URL', 'http://app.mytriangulate.com');
	define('API_URL', 'http://app.mytriangulate.com/api');
	define('SITES_URL', 'http://sites.mytriangulate.com');
	define('SITE_URL', 'http://{{friendlyId}}.mytriangulate.com');
	
	// default mode (hash, hashbang, html5)
	define('DEFAULT_URL_MODE', 'html5');
	
	// locations
	define('APP_LOCATION', '../');
	define('SITES_LOCATION', '../../sites');
	
	// setup default language for the site
	define('DEFAULT_LANGUAGE', 'en');
	
	// site admin
	define('SITE_ADMIN', '');
	
	// passcode
	define('PASSCODE', 'ilovetriangulate');
	
	// JWT key
	define('JWT_KEY', 'ilovetriangulate');
	
	// stripe keys
	define('STRIPE_SECRET_KEY', '');
	define('STRIPE_PUBLISHABLE_KEY', '');
	
	// Cross Origin Resource Sharing (CORS)
	define ('CORS', serialize (array (
	    'http://sites.mytriangulate.com'
	    )));
	    
	// advanced SMTP settings (see https://github.com/Synchro/PHPMailer)
	define('IS_SMTP', false);
	define('SMTP_HOST', 'smtp.mailserver.com');
	define('SMTP_AUTH', true);
	define('SMTP_USERNAME', '');
	define('SMTP_PASSWORD', '');
	define('SMTP_SECURE', 'tls');
	
	// key used to encrypt site SMTP passwords
	define('SMTPENC_KEY', 'ilovetriangulate');
	    
    // set what emails should be sent out and a reply-to email address
	define('REPLY_TO', '');
	define('REPLY_TO_NAME', '');
	define('SEND_WELCOME_EMAIL', false);
	define('SEND_PAYMENT_SUCCESSFUL_EMAIL', false);
	define('SEND_PAYMENT_FAILED_EMAIL', false);
	
    // start page (sets the default page a user sees after logon)
	define('START_PAGE', '#/app/pages');
	
	// set the default theme (directory name: themes/simple => simple)
	define('DEFAULT_THEME', 'simple');
?>