<?php

	// define brand
	define('BRAND', 'Triangulate');

	// DB connection parameters
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'triangulate');
	define('DB_USER', 'dbuser');
	define('DB_PASSWORD', 'dbpass');
	
	// S3 deployment options
	define('ENABLE_S3_DEPLOYMENT', false);
	define('BUCKET_NAME', '{{site}}.triangulate.io');
	define('S3_LOCATION', 'us-east-1',
	define('S3_URL', 'http://{{site}}.triangulate.io.s3-website-us-east-1.amazonaws.com');
	define('S3_KEY', 'AWS ACCESS KEY');
	define('S3_SECRET', 'AWS SECRET KEY');
	
	// URL of the application
	define('APP_URL', 'http://app.path-to-triangulate.com');
	
	// location of the app folder
	define('APP_LOCATION', '../app');
	
	// URL of the API
	define('API_URL', 'http://api.path-to-triangulate.com');
	
	// URL of sites produced by the app
	define('SITES_URL', 'http://sites.path-to-triangulate.com');
	
	// location of the sites folder
	define('SITES_LOCATION', '../sites');
	
	// setup default language for the site
	define('DEFAULT_LANGUAGE', 'en');
	
	// site admin
	define('SITE_ADMIN', '');
	
	// passcode
	define('PASSCODE', 'ilovetriangulate');
	
	// JWT key
	define('JWT_KEY', 'ilovetriangulate');
	
	// CORS (optional for external URLs)
	define ('CORS', serialize (array (
	    'http://path.totriangulate.com'
	    )));
	    
	// advanced SMTP settings (see https://github.com/Synchro/PHPMailer)
	define('IS_SMTP', false);
	define('SMTP_HOST', 'smtp.mailserver.com');
	define('SMTP_AUTH', true);
	define('SMTP_USERNAME', '');
	define('SMTP_PASSWORD', '');
	define('SMTP_SECURE', 'tls');
	    
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