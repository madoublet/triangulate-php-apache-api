<?php

	// DB connection parameters
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'triangulate');
	define('DB_USER', 'dbuser');
	define('DB_PASSWORD', 'dbpass');
	
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