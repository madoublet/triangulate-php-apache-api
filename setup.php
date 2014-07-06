<?php

	// DB connection parameters
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'triangulate');
	define('DB_USER', 'dbuser');
	define('DB_PASSWORD', 'dbpass');
	
	// URL of the application
	define('APP_URL', 'http://path.totriangulate.com');
	
	// location of the app folder
	define('APP_LOCATION', '../app');
	
	// URL of the API
	define('API_URL', 'http://path.totriangulate.com/api');
	
	// URL of sites produced by the app
	define('SITES_URL', 'http://path.totriangulate.com/sites');
	
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
	    
	// Google Maps API Key for use within the App
	define('GOOGLE_MAPS_API_KEY', 'YOUR GOOGLE MAPS API KEY');
    
    // set what emails should be sent out and a reply-to email address
	define('REPLY_TO', '');
	define('REPLY_TO_NAME', '');
	define('SEND_WELCOME_EMAIL', false);
	define('SEND_PAYMENT_SUCCESSFUL_EMAIL', false);
	define('SEND_PAYMENT_FAILED_EMAIL', false);
	
    // start page (sets the default page a user sees after logon)
	define('START_PAGE', 'pages');
	
	// set the default theme (directory name: themes/simple => simple)
	define('DEFAULT_THEME', 'simple');

	// the brand of your app
    define('BRAND', 'Respond CMS');
    define('BRAND_LOGO', 'images/respond-brand.png');
    define('BRAND_ICON', 'images/respond-icon.png');
    define('COPY', '<a href="http://respondcms.com">Respond CMS</a> version '.VERSION.'.  Made by Matthew Smith in Manchester, MO');

?>