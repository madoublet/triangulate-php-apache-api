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
		
		// copy emails directory
		$emails_src = APP_LOCATION.'/site/emails/';
		$emails_dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/emails';
		
		// create emails directory if it does not exist
		if(!file_exists($emails_dir)){
			mkdir($emails_dir, 0755, true);	
		}
		
		// copy emails directory
		if(file_exists($emails_dir)){
			Utilities::CopyDirectory($emails_src, $emails_dir);
		}
		
		// deny access to draft
		$dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/fragments/draft/';
		Publish::CreateDeny($dir);
		
		// deny access to publish
		$dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/fragments/publish/';
		Publish::CreateDeny($dir);
		
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
		$styles_src = APP_LOCATION.'/themes/'.$theme.'/styles/';
		
		if(file_exists($styles_src)){
			$styles_dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/themes/'.$theme.'/styles/';
		
			Utilities::CopyDirectory($styles_src, $styles_dest);
		}
		
		// copy files
		$files_src = APP_LOCATION.'/themes/'.$theme.'/files/';
		
		if(file_exists($files_src)){
			$files_dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/files/';

			Utilities::CopyDirectory($files_src, $files_dest);
		}
		
		// copy resources
		$res_src = APP_LOCATION.'/themes/'.$theme.'/resources/';
		
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
		
		// inject states
		Publish::InjectStates($site);
		
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
            
            $content = str_replace('{{states}}', $states, $content);
        }
        
        // save content
        Utilities::SaveContent($js_dir, $app_filename, $content);
		
	}
	
	
	// publish site
	public static function PublishSiteJSON($siteId){
		
		$site = Site::GetBySiteId($siteId);
		
		// set logoUrl
		$logoUrl = '';
		
		if($site['LogoUrl'] != ''){
			$logoUrl = 'files/'.$site['LogoUrl'];
		}
		
		// set iconUrl
		$iconUrl = '';
		
		if($site['IconUrl'] != ''){
			$iconUrl = 'files/'.$site['IconUrl'];
		}
		
		// setup sites array
		$site_arr = array(
					'SiteId' => $site['SiteId'],
					'Domain' => $site['Domain'],
					'API' => API_URL,
					'Name' => $site['Name'],
					'LogoUrl' => $logoUrl,
					'IconUrl' => $iconUrl,
					'IconBg' => $site['IconBg'],
					'Theme' => $site['Theme'],
					'PrimaryEmail' => $site['PrimaryEmail'],
					'Language' => $site['Language']
				);
		
		// encode to json
		$encoded = json_encode($site_arr);

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
		
		$list = MenuItem::GetMenuItems($site['SiteId']);
		
		$menu = array();
		$count = 0;
		
		foreach ($list as $row){
		
			$isInternal = false;
			$state = '';
			$url = '';

			if($row['PageId'] != -1){
			
				$page = Page::GetByPageId($row['PageId']);

				if($page != NULL){
					$pageId = $page['PageId'];
					$state = $page['FriendlyId'];
					$isInternal = true;
					
					
				}
				else{
					$pageId = -1;
				}
			}
			else{
				$pageId = -1;
			}

			$item = array(
					'MenuItemId' => $row['MenuItemId'],
				    'Name'  => $row['Name'],
				    'CssClass'  => $row['CssClass'],
				    'Type' => $row['Type'],
					'Url' => $row['Url'],
					'IsNested' => $row['IsNested'],
					'IsInternal' => $isInternal,
					'PageId' => $pageId
				);
				
			$menu[$count] = $item;	
			$count = $count + 1;
		}
		
		// encode to json
		$encoded = json_encode($menu);

		$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/data/';
		
		Utilities::SaveContent($dest, 'menu.json', $encoded);
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

	// publishes a fragment
	public static function PublishFragment($siteFriendlyId, $file, $status, $content){

		// clean content
		$content = str_replace( "&nbsp;", ' ', $content);

		$dir = SITES_LOCATION.'/'.$siteFriendlyId.'/fragments/'.$status.'/';
		
		if(!file_exists($dir)){
			mkdir($dir, 0755, true);	
		}
		
		// create fragment
		$fragment = SITES_LOCATION.'/'.$siteFriendlyId.'/fragments/'.$status.'/'.$file;
		file_put_contents($fragment, $content); // save to file
	}

	// publishes a page
	// live 	-> 	/site/{{site.FriendlyId}}/templates/page/{{pageType.FriendlyId}}.{{page.FriendlyId}}.html
	// preview	->  /site/{{site.FriendlyId}}/templates/preview/{{pageType.FriendlyId}}.{{page.FriendlyId}}.html
	public static function PublishPage($pageId, $preview = false, $remove_draft = false){
	
		$page = Page::GetByPageId($pageId);
        
		if($page!=null){
			
			$site = Site::GetBySiteId($page['SiteId']); // test for now
			$dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/templates/';
			$imageurl = $dest.'files/';
			$siteurl = 'http://'.$site['Domain'].'/';
			
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
			$html = Utilities::GeneratePage($site, $page, $pageType, $siteurl, $imageurl, $preview);
			
			// remove any drafts associated with the page
			if($remove_draft==true){
			
				// set file
				$file = $page['FriendlyId'].'.html';
				
				// set file
				if($page['PageTypeId'] != -1){
					if($pageType != NULL){
		    			$file = $pageType['FriendlyId'].'.'.$page['FriendlyId'].'.html';
		    		}
				}
			
			
				$draft = SITES_LOCATION.'/'.$site['FriendlyId'].'/fragments/draft/'.$file;
					
				if(file_exists($draft)){
					unlink($draft);
				}
			}

			// save the content to the published file
			Utilities::SaveContent($dest, $file, $html);
			
			// inject states
			Publish::InjectStates($site);
            
            return $dest.$file;
		}
	}
}

?>