<?php 

class Publish
{

	// publishes the entire site
	public static function PublishSite($siteId){
		
		// publish sitemap
		Publish::PublishSiteMap($siteId);
		
		// publish all CSS
		Publish::PublishAllCSS($siteId);	

		// publish all pages
		Publish::PublishAllPages($siteId);

		// publish rss for page types
		Publish::PublishRssForPageTypes($siteId);
		
		// publish menu
		Publish::PublishMenuJSON($siteId);
		
		// publish site json
		Publish::PublishSiteJSON($siteId);
		
		// publish common js
		Publish::PublishCommonJS($siteId);
		
		// publish common css
		Publish::PublishCommonCSS($siteId);
		
		// publish controller
		Publish::PublishCommon($siteId);	
	}
	
	// publishes common site files
	public static function PublishCommon($siteId){
        
        $site = Site::GetBySiteId($siteId);
      	
		// copy templates/triangulate
		$templates_src = APP_LOCATION.'/site/templates/triangulate';
		$templates_dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/templates/triangulate';
		
		// create libs directory if it does not exist
		if(!file_exists($templates_dest)){
			mkdir($templates_dest, 0755, true);	
		}
		
		// copy libs directory
		if(file_exists($templates_dest)){
			Utilities::CopyDirectory($templates_src, $templates_dest);
		}
		
		// setup htaccess
		Publish::SetupHtaccess($site);
		
	}
	
	// creates .htaccess to deny access to a specific directory
	public static function CreateDeny($dir){
		
		// create dir if needed
		if(!file_exists($dir)){
			mkdir($dir, 0755, true);	
		}
		
		// create .htaccess to deny access
		$deny = $dir.'.htaccess';

		file_put_contents($deny, 'Deny from all'); // save to file	
		
	}
	
	// creates .htaccess for html5 sites
	public static function SetupHtaccess($site){
	
		$htaccess = SITES_LOCATION.'/'.$site['FriendlyId'].'/.htaccess';
	
		if($site['UrlMode'] == 'html5'){
			
			$contents = 'RewriteEngine On'.PHP_EOL.
							'RewriteCond %{REQUEST_FILENAME} !-f'.PHP_EOL.
							'RewriteCond %{REQUEST_FILENAME} !-d'.PHP_EOL.
							'RewriteCond %{REQUEST_URI} !.*\.(css¦js|html|png)'.PHP_EOL.
							'RewriteRule (.*) index.html [L]';
			

			file_put_contents($htaccess, $contents); // save to file			
		}
		else if($site['UrlMode'] == 'static'){
			$contents = 'RewriteEngine On'.PHP_EOL.
						'RewriteCond %{REQUEST_FILENAME} !-f'.PHP_EOL.
						'RewriteRule ^([^\.]+)$ $1.html [NC,L]';
			
			file_put_contents($htaccess, $contents); // save to file
		}
		
	}

	// publishes a theme
	public static function PublishTheme($site, $theme){

		$theme_dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/';
		
		// create theme directory
		if(!file_exists($theme_dir)){
			mkdir($theme_dir, 0755, true);	
		}
		
		// create directory for theme
		$theme_dir .= $theme .'/';
		
		if(!file_exists($theme_dir)){
			mkdir($theme_dir, 0755, true);	
		}
		
		// create directory for layouts
		$layouts_dir = $theme_dir.'/layouts/';
		
		if(!file_exists($layouts_dir)){
			mkdir($layouts_dir, 0755, true);	
		}
		
		// create directory for styles
		$styles_dir = $theme_dir.'/styles/';
		
		if(!file_exists($styles_dir)){
			mkdir($styles_dir, 0755, true);	
		}
		
		// create directory for resources
		$res_dir = $theme_dir.'/resources/';
		
		if(!file_exists($res_dir)){
			mkdir($res_dir, 0755, true);	
		}
		
		// copy layouts
		$layouts_src = APP_LOCATION.'/themes/'.$theme.'/layouts/';
		
		if(file_exists($layouts_src)){
			$layouts_dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/'.$theme.'/layouts/';

			Utilities::CopyDirectory($layouts_src, $layouts_dest);
		}
		
		
		// copy the index from the layouts
		$index_src = $theme_dir.'/layouts/index.html';
		$index_dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/index.html';
		
		if(file_exists($index_src)){
			copy($index_src, $index_dest);
		}
		
		// copy styles
		$styles_src = APP_LOCATION.'themes/'.$theme.'/styles/';
		
		if(file_exists($styles_src)){
			$styles_dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/'.$theme.'/styles/';
		
			Utilities::CopyDirectory($styles_src, $styles_dest);
		}
		
		// copy files
		if(FILES_ON_S3 == true){  // copy files to S3
		
			$files_src = APP_LOCATION.'themes/'.$theme.'/files';
			
			echo '$files_src='.$files_src;
			
			// deploy directory to S3
			S3::DeployDirectory($site, $files_src, 'files/');
		
		}
		else{ // copy files locally
			$files_src = APP_LOCATION.'themes/'.$theme.'/files/';
			
			if(file_exists($files_src)){
				$files_dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/files/';
	
				Utilities::CopyDirectory($files_src, $files_dest);
			}
		}
		
		// copy resources
		$res_src = APP_LOCATION.'themes/'.$theme.'/resources/';
		
		if(file_exists($res_src)){
			$res_dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/'.$theme.'/resources/';
		
			Utilities::CopyDirectory($res_src, $res_dest);
		}
		
	}
	
	// publishes common js
	public static function PublishCommonJS($siteId){
		
		$site = Site::GetBySiteId($siteId);
		
		$src = APP_LOCATION.'/site/js';
		$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/js';
		
		// create dir if it doesn't exist
		if(!file_exists($dest)){
			mkdir($dest, 0755, true);	
		}
		
		// copies a directory
		Utilities::CopyDirectory($src, $dest);
		
		if($site['UrlMode'] == 'static'){
		
			// get static version of triangulate.site.js
			$src_file = APP_LOCATION.'/site/js/static/triangulate.site.js';
			$dest_file = SITES_LOCATION.'/'.$site['FriendlyId'].'/js/triangulate.site.js';
			
			$content = file_get_contents($src_file);
            
            // get language
            $language = $site['Language'];
            
            // set language
            $content = str_replace('{{language}}', $language, $content);

			// update site file
			file_put_contents($dest_file, $content);
		
			// inject controllers
			Publish::InjectControllers($site);
		}
		else{
			// inject states
			Publish::InjectStates($site);
		}
		
	}
	
	// injects states into sites/js/triangulate.site.js
	public static function InjectStates($site){
		
		// inject routes
		$pages = Page::GetPagesForSite($site['SiteId'], true);
		$states = '';
		
		// walk through pages
		foreach($pages as $page){
		
			$state = $page['FriendlyId'];
			$isSecure = 'false';
		
			// defaults
			$url = '/'.$page['FriendlyId'];
			$templateUrl = 'themes/'.$site['Theme'].'/layouts/'.$page['Layout'].'.html';
			
			// check for page type
			if($page['PageTypeId'] != -1){
				$pageType = PageType::GetByPageTypeId($page['PageTypeId']);
				
				if($pageType != NULL){
					$state = $pageType['FriendlyId'].'/'.$page['FriendlyId'];
					$url = '/'.$pageType['FriendlyId'].'/'.$page['FriendlyId'];
					
					if($pageType['IsSecure'] == 1){
						$isSecure = 'true';
					}
				}
			}
			
			// strip the first / for the pageUrl
			$pageUrl = ltrim($url,'/');
			
			// build fullStylesheet
			$fullStylesheetUrl = 'css/'.$page['Stylesheet'].'.css';
			
			// setup state 
			if($url != ''){
				$state = '.state("'.$state.'", {'.PHP_EOL
						      .'url: "'.$url.'",'.PHP_EOL
						      .'templateUrl: "'.$templateUrl.'",'.PHP_EOL
						      .'resolve:{'.PHP_EOL
						      	.'pageMeta:  function(){'.PHP_EOL
						        .'    	return {'.PHP_EOL
						        .'			PageId: \''.$page['PageId'].'\','.PHP_EOL
						        .'			PageTypeId: \''.$page['PageTypeId'].'\','.PHP_EOL
						        .'			FriendlyId: \''.$page['FriendlyId'].'\','.PHP_EOL
						        .'			Url: \''.$pageUrl.'\','.PHP_EOL
						        .'			Name: \''.htmlentities($page['Name'], ENT_QUOTES).'\','.PHP_EOL
						        .'			Description: \''.htmlentities($page['Description'], ENT_QUOTES).'\','.PHP_EOL
						        .'			Keywords: \''.htmlentities($page['Keywords'], ENT_QUOTES).'\','.PHP_EOL
						        .'			Callout: \''.htmlentities($page['Callout'], ENT_QUOTES).'\','.PHP_EOL
						        .'			IsSecure: '.$isSecure.','.PHP_EOL
						        .'			BeginDate: \''.$page['BeginDate'].'\','.PHP_EOL
						        .'			EndDate: \''.$page['EndDate'].'\','.PHP_EOL
						        .'			Location: \''.htmlentities($page['Location'], ENT_QUOTES).'\','.PHP_EOL
						        .'			LatLong: \''.$page['LatLong'].'\','.PHP_EOL
						        .'			Layout: \''.$page['Layout'].'\','.PHP_EOL
						        .'			FullStylesheetUrl: \''.$fullStylesheetUrl.'\','.PHP_EOL
						        .'			Stylesheet: \''.$page['Stylesheet'].'\','.PHP_EOL
						        .'			Image: \''.$page['Image'].'\','.PHP_EOL
						        .'			LastModifiedDate: \''.$page['LastModifiedDate'].'\','.PHP_EOL
						        .'			FirstName: \''.$page['FirstName'].'\','.PHP_EOL
						        .'			LastName: \''.$page['LastName'].'\','.PHP_EOL
						        .'			LastModifiedBy: \''.$page['FirstName'].' '.$page['LastName'].'\','.PHP_EOL
						        .'			PhotoUrl: \''.$page['PhotoUrl'].'\''.PHP_EOL
						        .'		};'.PHP_EOL
						        .'},'.PHP_EOL
						        
						        .'siteMeta:  function($http){'.PHP_EOL
						        .'    	return $http({method: \'GET\', url: \'data/site.json\'});'.PHP_EOL
						        .'}'.PHP_EOL
				
						      .'},'.PHP_EOL
						      .'controller: "PageCtrl"'.PHP_EOL
						    .'})'.PHP_EOL;
			}
			
			// set states
			$states .= $state;
		
		}
		
		// template file
		$template_file = APP_LOCATION.'/site/js/triangulate.site.js';
		
		// site file
        $js_dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/js/';
        $app_filename = 'triangulate.site.js';
		$app_file = $js_dir.$app_filename;
		
		// init content
		$content = '';
		
		// update states
		if(file_exists($template_file)){
            $content = file_get_contents($template_file);
            
            $language = $site['Language'];
            
            // set to the correct format for i18next (ll-LL)
            if(strpos($language, '-') !== FALSE){
				$arr = explode('-', $language);
				$language = strtolower($arr[0]).'-'.strtoupper($arr[1]);
			}
			
			$urlMode = $site['UrlMode'];
			$html5mode = '';
			
			// set $html5mode
			if($urlMode == 'html5'){
				$html5mode = '$locationProvider.html5Mode(true);';
			}
			else if($urlMode == 'hashbang'){
				$html5mode = "$locationProvider.html5Mode(true).hashPrefix('!');";
			}
			
            // set html5mode, language, and states
            $content = str_replace('{{html5mode}}', $html5mode, $content);
            $content = str_replace('{{language}}', $site['Language'], $content);
            $content = str_replace('{{states}}', $states, $content);
        }
        
        // save content
        Utilities::SaveContent($js_dir, $app_filename, $content);
		
	}
	
	// injects controllers into site/js/static/triangulate.site.controllers.js
	public static function InjectControllers($site){
	
		// create site json
		$arr = Publish::CreateSiteJSON($site);
		
		// encode to json
		$site_json = json_encode($arr).';';		
		
		// inject routes
		$pages = Page::GetPagesForSite($site['SiteId'], true);
		
		// a list of controllers for the app
		$ctrls = '';
		
		// walk through pages
		foreach($pages as $page){
		
			$isSecure = 'false';
			
			// create a controller name
			$ctrl = ucfirst($page['FriendlyId']);
			$ctrl = str_replace('-', '', $ctrl);
		
			// defaults
			$url = '/'.$page['FriendlyId'];
			$templateUrl = 'themes/'.$site['Theme'].'/layouts/'.$page['Layout'].'.html';
			
			// check for page type
			if($page['PageTypeId'] != -1){
				$pageType = PageType::GetByPageTypeId($page['PageTypeId']);
				
				if($pageType != NULL){
					$state = $pageType['FriendlyId'].'/'.$page['FriendlyId'];
					$url = '/'.$pageType['FriendlyId'].'/'.$page['FriendlyId'];
					
					$ctrl = ucfirst($pageType['FriendlyId']).$ctrl;
					$ctrl = str_replace('-', '', $ctrl);
					
					if($pageType['IsSecure'] == 1){
						$isSecure = 'true';
					}
				}
			}
			
			// strip the first / for the pageUrl
			$pageUrl = ltrim($url,'/');
			
			// build fullStylesheet
			$fullStylesheetUrl = 'css/'.$page['Stylesheet'].'.css';
			
			// setup state 
			if($url != ''){
				$page_json = '{'.PHP_EOL
						        .'	PageId: \''.$page['PageId'].'\','.PHP_EOL
						        .'	PageTypeId: \''.$page['PageTypeId'].'\','.PHP_EOL
						        .'	FriendlyId: \''.$page['FriendlyId'].'\','.PHP_EOL
						        .'	Url: \''.$pageUrl.'\','.PHP_EOL
						        .'	Name: \''.htmlentities($page['Name'], ENT_QUOTES).'\','.PHP_EOL
						        .'	Description: \''.htmlentities($page['Description'], ENT_QUOTES).'\','.PHP_EOL
						        .'	Keywords: \''.htmlentities($page['Keywords'], ENT_QUOTES).'\','.PHP_EOL
						        .'	Callout: \''.htmlentities($page['Callout'], ENT_QUOTES).'\','.PHP_EOL
						        .'	IsSecure: '.$isSecure.','.PHP_EOL
						        .'	BeginDate: \''.$page['BeginDate'].'\','.PHP_EOL
						        .'	EndDate: \''.$page['EndDate'].'\','.PHP_EOL
						        .'	Location: \''.htmlentities($page['Location'], ENT_QUOTES).'\','.PHP_EOL
						        .'	LatLong: \''.$page['LatLong'].'\','.PHP_EOL
						        .'	Layout: \''.$page['Layout'].'\','.PHP_EOL
						        .'	FullStylesheetUrl: \''.$fullStylesheetUrl.'\','.PHP_EOL
						        .'	Stylesheet: \''.$page['Stylesheet'].'\','.PHP_EOL
						        .'	Image: \''.$page['Image'].'\','.PHP_EOL
						        .'	LastModifiedDate: \''.$page['LastModifiedDate'].'\','.PHP_EOL
						        .'	FirstName: \''.$page['FirstName'].'\','.PHP_EOL
						        .'	LastName: \''.$page['LastName'].'\','.PHP_EOL
						        .'	LastModifiedBy: \''.$page['FirstName'].' '.$page['LastName'].'\','.PHP_EOL
						        .'	PhotoUrl: \''.$page['PhotoUrl'].'\''.PHP_EOL
						        .'};';
			}
			
			// controller file
			$ctrl_file = APP_LOCATION.'/site/js/static/triangulate.site.controller.js';
		
		
			if(file_exists($ctrl_file)){
				$content = file_get_contents($ctrl_file);
				
				// replace ctrl
				$content = str_replace('{{ctrl}}', $ctrl, $content);
				$content = str_replace('{{page}}', $page_json, $content);
				$content = str_replace('{{site}}', $site_json, $content);
				
				// add controller to the list
				$ctrls .= $content.PHP_EOL;
			}
		}
		
		// site file
        $js_dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/js/';
        $app_filename = 'triangulate.site.controllers.js';
		$app_file = $js_dir.$app_filename;
		
		// init content
		$content = '';
		
		// controller file
		$ctrls_file = APP_LOCATION.'/site/js/static/triangulate.site.controllers.js';
		
		// update states
		if(file_exists($ctrls_file)){
            $content = file_get_contents($ctrls_file);
          			
            // set contorllers
            $content = str_replace('{{controllers}}', $ctrls, $content);
        }
        
        // save content
        Utilities::SaveContent($js_dir, $app_filename, $content);
		
	}
	
	// creates site JSON
	public static function CreateSiteJSON($site, $env = 'local'){
		
		// set logoUrl
		$logoUrl = '';
		
		if($site['LogoUrl'] != ''){
			$logoUrl = 'files/'.$site['LogoUrl'];
		}
		
		// set imagesURL
		if($env == 'local'){  // if it is locally deployed
		
			$imagesURL = '/';
			
			// if files are stored on S3
			if(FILES_ON_S3 == true){
				$bucket = $site['Bucket'];
				$imagesURL = str_replace('{{bucket}}', $bucket, S3_URL).'/';
				$imagesURL = str_replace('{{site}}', $site['FriendlyId'], $imagesURL);
			}
			
		}
		else{ // if the deployment is on S3
			$imagesURL = '/';
		}
		
		// set iconUrl
		$iconUrl = '';
		
		if($site['IconUrl'] != ''){
			$iconUrl = $imagesURL.'files/'.$site['IconUrl'];
		}
		
		// set display
		$showCart = false;
		$showSettings = false;
		$showLanguages = false;
		$showLogin = false;
		
		if($site['ShowCart'] == 1){
			$showCart = true;
		}
		
		if($site['ShowSettings'] == 1){
			$showSettings = true;
		}
		
		if($site['ShowLanguages'] == 1){
			$showLanguages = true;
		}
		
		if($site['ShowLogin'] == 1){
			$showLogin = true;
		}
		
		// setup sites array
		return array(
			'SiteId' => $site['SiteId'],
			'Domain' => $site['Domain'],
			'API' => API_URL,
			'Name' => $site['Name'],
			'ImagesURL' => $imagesURL,
			'UrlMode' => $site['UrlMode'],
			'LogoUrl' => $logoUrl,
			'IconUrl' => $iconUrl,
			'IconBg' => $site['IconBg'],
			'Theme' => $site['Theme'],
			'PrimaryEmail' => $site['PrimaryEmail'],
			'Language' => $site['Language'],
			'ShowCart' => $showCart,
			'ShowSettings' => $showSettings,
			'ShowLanguages' => $showLanguages,
			'ShowLogin' => $showLogin,
			'Currency' => $site['Currency'],
			'WeightUnit' => $site['WeightUnit'],
			'ShippingCalculation' => $site['ShippingCalculation'],
			'ShippingRate' => $site['ShippingRate'],
			'ShippingTiers' => $site['ShippingTiers'],
			'TaxRate' => $site['TaxRate'],
			'PayPalId' => $site['PayPalId'],
			'PayPalUseSandbox' => $site['PayPalUseSandbox'],
			'FormPublicId' => $site['FormPublicId']
		);
		
	}
	
	// publish site
	public static function PublishSiteJSON($siteId){
		
		$site = Site::GetBySiteId($siteId);
		
		$arr = Publish::CreateSiteJSON($site);
		
		// encode to json
		$encoded = json_encode($arr);

		$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/data/';
		
		Utilities::SaveContent($dest, 'site.json', $encoded);
	}
	
	// publishes common css
	public static function PublishCommonCSS($siteId){
		
		$site = Site::GetBySiteId($siteId);
		
		$src = APP_LOCATION.'/site/css';
		$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/css';
		
		// create dir if it doesn't exist
		if(!file_exists($dest)){
			mkdir($dest, 0755, true);	
		}
		
		// copies a directory
		Utilities::CopyDirectory($src, $dest);
	}
	
	// publishes all the pages in the site
	public static function PublishAllPages($siteId){
	
		$site = Site::GetBySiteId($siteId);
		
		// Get all pages
		$list = Page::GetPagesForSite($site['SiteId']);
		
		foreach ($list as $row){
		
			Publish::PublishPage($row['PageId'], false, false);
		}
	}
	
	// publish menu
	public static function PublishMenuJSON($siteId){
		
		$site = Site::GetBySiteId($siteId);
		
		$types = MenuType::GetMenuTypes($site['SiteId']);
		
		// create types for primary, footer
		$primary = array(
			'MenuTypeId' => -1,
		    'FriendlyId'  => 'primary'
		);
		
		$footer = array(
			'MenuTypeId' => -1,
		    'FriendlyId'  => 'footer'
		);
		
		// push default types
		array_push($types, $primary);
		array_push($types, $footer);
		
		// walk through types
		foreach($types as $type){
		
			echo $type['FriendlyId'];
		
			// get items for type
			$list = MenuItem::GetMenuItemsForType($site['SiteId'], $type['FriendlyId']);
			
			// create array for menu
			$menu = array();
			
			// walk through menu items
			foreach($list as $row){
			
				$isInternal = false;
				$state = '';
				$url = '';
	
				// push non nested items onto the array
				if($row['IsNested'] == 0){
					
					// create an array item
					$item = array(
						'MenuItemId' => $row['MenuItemId'],
					    'Name'  => $row['Name'],
					    'CssClass'  => $row['CssClass'],
					    'Url' => $row['Url'],
						'PageId' => $row['PageId'],
						'HasChildren' => false,
						'Children' => array()
					);
					
					// push item onto the array
					array_push($menu, $item);
					
				}
				else{
					
					// create an array item
					$item = array(
						'MenuItemId' => $row['MenuItemId'],
					    'Name'  => $row['Name'],
					    'CssClass'  => $row['CssClass'],
					    'Url' => $row['Url'],
						'PageId' => $row['PageId']
					);
					
					// get a reference to the parent
					$parent = array_pop($menu);
					
					// make sure the parent exists
					if($parent != NULL){
						
						// push item to the children array
						array_push($parent['Children'], $item);
						
						// set that it has children
						$parent['HasChildren'] = true;
						
						// push item onto the array
						array_push($menu, $parent);
						
					}
					
				}
		
			}
			
			// encode to json
			$encoded = json_encode($menu);
	
			$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/data/';
			
			echo $dest.'menu-'.$type['FriendlyId'].'.json';
			
			Utilities::SaveContent($dest, 'menu-'.$type['FriendlyId'].'.json', $encoded);
		}
		
	}
		
	// publish rss for all page types
	public static function PublishRssForPageTypes($siteId){
		
		$site = Site::GetBySiteId($siteId);
		
		$list = PageType::GetPageTypes($site['SiteId']);
		
		foreach ($list as $row){
			Publish::PublishRssForPageType($siteId, $row['PageTypeId']);
		}
	}
	
	// publish rss for pages
	public static function PublishRssForPageType($siteId, $pageTypeId){
		
		$site = Site::GetBySiteId($siteId);
		
		$dest = SITES_LOCATION.'/'.$site['FriendlyId'];
		
		$pageType = PageType::GetByPageTypeId($pageTypeId);
		
		// generate rss
		$rss = Utilities::GenerateRSS($site, $pageType);
		
		Utilities::SaveContent($dest.'/data/', strtolower($pageType['FriendlyId']).'.xml', $rss);
	}
	
	// publish sitemap
	public static function PublishSiteMap($siteId){
		
		$site = Site::GetBySiteId($siteId);
		
		$dest = SITES_LOCATION.'/'.$site['FriendlyId'];
		
		// generate default site map
		$content = Utilities::GenerateSiteMap($site);
		
		Utilities::SaveContent($dest.'/', 'sitemap.xml', $content);
	}
	
	// publishes a specific css file
	public static function PublishCSS($site, $name){
	
		// get references to file
	    $lessDir = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/'.$site['Theme'].'/styles/';
	    $cssDir = SITES_LOCATION.'/'.$site['FriendlyId'].'/css/';

	    $lessFile = $lessDir.$name.'.less';
	    $cssFile = $cssDir.$name.'.css';

	    // create css directory (if needed)
	    if(!file_exists($cssDir)){
			mkdir($cssDir, 0755, true);	
		}

	    if(file_exists($lessFile)){
	    	$content = file_get_contents($lessFile);

	    	$less = new lessc;

	    	try{
			  $less->compileFile($lessFile, $cssFile);

			  return true;
			} 
			catch(exception $e){
			
			  return false;
			}
    	}
    	else{
    		return false;
    	}

	}

	// publishes all css
	public static function PublishAllCSS($siteId){

		$site = Site::GetBySiteId($siteId); // test for now

		$lessDir = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/'.$site['Theme'].'/styles/';
		
		//get all image files with a .less ext
		$files = glob($lessDir . "*.less");

		//print each file name
		foreach($files as $file){
			$f_arr = explode("/",$file);
			$count = count($f_arr);
			$filename = $f_arr[$count-1];
			$name = str_replace('.less', '', $filename);

			Publish::PublishCSS($site, $name);
		}

	}

	// publishes a page
	// live 	-> 	/site/{{site.FriendlyId}}/templates/page/{{pageType.FriendlyId}}.{{page.FriendlyId}}.html
	// preview	->  /site/{{site.FriendlyId}}/templates/preview/{{pageType.FriendlyId}}.{{page.FriendlyId}}.html
	public static function PublishPage($pageId, $preview = false, $remove_draft = false){
	
		$page = Page::GetByPageId($pageId);
        
		if($page!=null){
			
			$site = Site::GetBySiteId($page['SiteId']); // test for now
			
			if($site['UrlMode'] == 'static'){ // for sites using static html pages (URL-based routing)
				Publish::PublishDynamicPage($page, $site, $preview, $remove_draft);
				Publish::PublishStaticPage($page, $site, $preview, $remove_draft);
				
				// inject controllers
				Publish::InjectControllers($site);
			}
			else{ // publishes a dynamic version of the page (for sites using UI-ROUTER (html5, hashbang, etc)
				Publish::PublishDynamicPage($page, $site, $preview, $remove_draft);
				
				// inject states
				Publish::InjectStates($site);
			}
			
		}
	}
	
	// publishes a dymanic version of the page
	public static function PublishDynamicPage($page, $site, $preview = false, $remove_draft = false){
		
		$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/templates/';
		$imageurl = $dest.'files/';
		$siteurl = $site['Domain'].'/';
		
		$friendlyId = $page['FriendlyId'];
		
		$url = '';
		$file = '';
        
        // set full destination
        if($preview==true){
            $dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/templates/preview/';
        }   
        else{
            $dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/templates/page/';
 	  	}
        
        // create directory if it does not exist
        if(!file_exists($dest)){
			mkdir($dest, 0755, true);	
		}
        
        // set friendlyId
        $file = $page['FriendlyId'].'.html';
        
        // initialize PT
        $pageType = NULL;
        
		// create a nice path to store the file
		if($page['PageTypeId'] != -1){
			
			$pageType = PageType::GetByPageTypeId($page['PageTypeId']);
			
			// prepend the friendlyId to the fullname
			if($pageType!=null){
				$file = strtolower($pageType['FriendlyId']).'.'.$file;
			}
			else{
				$file = 'uncategorized.'.$file;
			}

		}
	
		// generate default
		$html = '';
		
		if($preview == true){
			$html = $page['Draft'];
		}
		else{
			$html = $page['Content'];
		}
		
		// remove any drafts associated with the page
		if($remove_draft==true){
		
			// remove a draft from the page
			Page::RemoveDraft($page['PageId']);
		
		}

		// save the content to the published file
		Utilities::SaveContent($dest, $file, $html);
		
        return $dest.$file;
        
	}
	
	
	// publishes a static version of the page
	public static function PublishStaticPage($page, $site, $preview = false, $remove_draft = false){
	
		$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/';
		$imageurl = $dest.'files/';
		$siteurl = $site['Domain'].'/';
		
		$friendlyId = $page['FriendlyId'];
		
		$url = '';
		$file = '';
		
		// created ctrl
		$ctrl = ucfirst($page['FriendlyId']);
		$ctrl = str_replace('-', '', $ctrl);
        
 	  	// create a static location for the page
 	  	if($page['PageTypeId'] == -1){
			$url = $page['FriendlyId'].'.html';
			$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/';
		}
		else{
			$pageType = PageType::GetByPageTypeId($page['PageTypeId']);
			
			$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/uncategorized/';
			
			if($pageType!=null){
				$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/'.$pageType['FriendlyId'].'/';
				
				// created ctrl
				$ctrl = ucfirst($pageType['FriendlyId']).$ctrl;
				$ctrl = str_replace('-', '', $ctrl);
			}

		}
        
        // create directory if it does not exist
        if(!file_exists($dest)){
			mkdir($dest, 0755, true);	
		}
        
		// generate default
		$html = '';
		$content = '';
		
		// get index and layout (file_get_contents)
		$index = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/'.$site['Theme'].'/layouts/index.html';
		$layout = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/'.$site['Theme'].'/layouts/'.$page['Layout'].'.html';
		
		// get index html
		if(file_exists($index)){
        	$html = file_get_contents($index);
        }

        // get layout html
		if(file_exists($layout)){
        	$layout_html = file_get_contents($layout);
        
			$html = str_replace('<body ui-view></body>', '<body ng-controller="'.$ctrl.'Ctrl">'.$layout_html.'</body>', $html);
        }
		
		// get draft/content
		if($preview == true){
			$file = $page['FriendlyId'].'.preview.html';
			$content = $page['Draft'];
		}
		else{
			$file = $page['FriendlyId'].'.html';
			$content = $page['Content'];
		}
		
		// replace triangulate-content for layout with content
		$html = str_replace('<triangulate-content id="main-content" url="{{page.Url}}"></triangulate-content>', $content, $html);
		
		// remove any drafts associated with the page
		if($remove_draft==true){
		
			// remove a draft from the page
			Page::RemoveDraft($page['PageId']);
		
		}

		// replace ui-sref with static reference
		//$html = str_replace('ui-sref="', 'href="/', $html);
		
		// replace common Angular calls for SEO, e.g. {{page.Name}} {{page.Description}} {{site.Name}}
		$html = str_replace('{{page.Name}}', $page['Name'], $html);
		$html = str_replace('{{page.Description}}', $page['Description'], $html);
		$html = str_replace('{{page.Keywords}}', $page['Keywords'], $html);
		$html = str_replace('{{page.Callout}}', $page['Callout'], $html);
		$html = str_replace('{{site.Name}}', $site['Name'], $html);
		$html = str_replace('{{site.Language}}', $site['Language'], $html);
		
		// save the content to the published file
		Utilities::SaveContent($dest, $file, $html);
		
        return $dest.$file;
        
	}
	
	// removes a draft of the page
	public static function RemoveDraft($pageId){
	
		// remove a draft from the page
		Page::RemoveDraft($page['PageId']);
		
		return false;
	}
		
}

?>