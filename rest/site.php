<?php 

/**
 * This class defines an example resource that is wired into the URI /example
 * @uri /site/test
 */
class SiteTestResource extends Tonic\Resource {

    /**
     * @method GET
     */
    function get() {

        $response = new Tonic\Response(Tonic\Response::OK);
        $response->contentType = 'text/HTML';
        $response->body = 'API works!';

        return $response;
    }
}

/**
 * This class defines an example resource that is wired into the URI /example
 * @uri /site/validate/id
 */
class SiteValidateIdResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {

        // parse request
        parse_str($this->request->data, $request);

        $friendlyId = $request['friendlyId'];
        
        $isFriendlyIdUnique = Site::IsFriendlyIdUnique($friendlyId);
	            
        if($isFriendlyIdUnique==false){
            return new Tonic\Response(Tonic\Response::CONFLICT);
        }
        else{
	        return new Tonic\Response(Tonic\Response::OK);
        }

    }
}

/**
 * This class defines an example resource that is wired into the URI /example
 * @uri /site/validate/email
 */
class SiteValidateEmailResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {

        // parse request
        parse_str($this->request->data, $request);

        $email = $request['email'];
        
        $isUserUnique = User::IsLoginUnique($email);
	            
        if($isUserUnique==false){
        
            return new Tonic\Response(Tonic\Response::CONFLICT);
        }
        else{
	        return new Tonic\Response(Tonic\Response::OK);
        }

    }
}


/**
 * A protected API call to retrieve the current site
 * @uri /site/create
 */
class SiteCreateResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
        
        parse_str($this->request->data, $request); // parse request

        $friendlyId = $request['friendlyId'];
        $name = $request['name'];
        $s_passcode = $request['passcode'];
        $timeZone = $request['timeZone'];
        $email = '';
        $password = '';
        $language = 'en-us'; // language for the app
        $userId = -1;
        
        $theme = DEFAULT_THEME;
        
        // set theme
        if(isset($request['theme'])){
	        $theme = $request['theme'];
        }
        
        // set language if set
        if(isset($request['language'])){
	        $language = $request['language'];
        }
        
        // check for email and password
        if(isset($request['email'])){
        	
        	$userLanguage = 'en-us';
        
       		if(isset($request['userLanguage'])){
		        $userLanguage = $request['userLanguage'];
	        }
	        
	        $email = $request['email'];
	        $password = $request['password'];
        }
        else{
			// get an authuser
			$authUser = new AuthUser();
			
			if($authUser->UserId && $authUser->IsSuperAdmin==true){ // check if authorized
				$userId = $authUser->UserId;
			}
			else{
				return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
			}
        }
        
        // defaults
        $firstName = 'New';
        $lastName = 'User';
        $domain = SITES_URL.'/'.$friendlyId;
    	$domain = str_replace('http://', '', $domain);
		$logoUrl = 'sample-logo.png';
		
        if($s_passcode == PASSCODE){
           
            $isFriendlyIdUnique = Site::IsFriendlyIdUnique($friendlyId);
	            
            if($isFriendlyIdUnique==false){
                return new Tonic\Response(Tonic\Response::CONFLICT);
            }
            
            // default is blank
            $welcomeEmail = '';
            $receiptEmail = '';
            
            // files for emails
            $welcome_file = APP_LOCATION.'/site/emails/welcome.html';
            $receipt_file = APP_LOCATION.'/site/emails/receipt.html';
            
            // make sure the welcome email exists
            if(file_exists($welcome_file)){
    			
    			// get default email file
    			$welcomeEmail = file_get_contents($welcome_file);
    			
    		}
    		
    		// make sure the receipt email exists
            if(file_exists($receipt_file)){
    			
    			// get default email file
    			$receiptEmail = file_get_contents($receipt_file);
    			
    		}
    		
    		// create the bucket name
    		$bucket = str_replace('{{site}}', $friendlyId, BUCKET_NAME);
    		
            // add the site
    	    $site = Site::Add($domain, $bucket, $name, $friendlyId, $logoUrl, $theme, $email, $timeZone, $language, $welcomeEmail, $receiptEmail);
    	                
            // add the admin
            if($email != ''){
            	$isActive = 1; // admins by default are active
            
            	$user = User::Add($email, $password, $firstName, $lastName, 'Admin', $userLanguage, $isActive, $site['SiteId']);
            	$userId = $user['UserId'];
            }
            
       
            // read the defaults file
            $default_json_file = APP_LOCATION.'/themes/'.$theme.'/default.json';
            
            // set $siteId
            $siteId = $site['SiteId'];
            
            // check to make sure the defaults.json exists
            if(file_exists($default_json_file)){
    			
    			// get json from the file
    			$json_text = file_get_contents($default_json_file);
    			
    			// decode json
    			$json = json_decode($json_text, true);
    			
    			// pagetypes
    			$pagetypes = array();
    			
    			// menu counts
    			$primaryMenuCount = 0;
    			$footerMenuCount = 0;
    			
    			// walk through defaults array
    			foreach($json as &$value){
    			
    				// get values from array
    				$url = $value['url'];
    				$source = $value['source'];
    				$name = $value['name'];
    				$description = $value['description'];
    				$layout = $value['layout'];
    				$stylesheet = $value['stylesheet'];
    				$primaryMenu = $value['primaryMenu'];
    				$footerMenu = $value['footerMenu'];
    				
    				// initialize PT
    				$pageType = NULL;
    				
    				if(strpos($url, '/') !== false){ // the url has a pagetype
						$arr = explode('/', $url);
						
						// get friendly ids from $url
						$pageTypeFriendlyId = $arr[0];
						$pageFriendlyId = $arr[1];
						
						$pageTypeId = -1;
						
						$pageType = PageType::GetByFriendlyId($pageTypeFriendlyId, $siteId);
						
						// create a new pagetype
						if($pageType == NULL){
							$pageType = PageType::Add($pageTypeFriendlyId, $layout, $stylesheet, 0, $siteId, $userId);
						}
						
						// get newly minted page type
						$pageTypeId = $pageType['PageTypeId'];
					
					}
					else{ // root, no pagetype
						$pageFriendlyId = $url;
						$pageTypeId = -1;
					}
					
					// create a page
					$page = Page::Add($pageFriendlyId, $name, $description, 
											$layout, $stylesheet, $pageTypeId, $site['SiteId'], $userId);
				
					// set the page to active							
					Page::SetIsActive($page['PageId'], 1);
					
					// build the content file
					$filename = APP_LOCATION.'/themes/'.$theme.'/'.$source;
					$content = '';
					
					// get the content for the page
					if(file_exists($filename)){
		    			$content = file_get_contents($filename);
		    			
		    			// fix images
		    			$content = str_replace('{{site-dir}}', '//'.$site['Domain'], $content);
		    		}
					
					Page::EditContent($page['PageId'], $content, $userId);
					
					// build the primary menu
					if($primaryMenu == true){
						MenuItem::Add($name, '', 'primary', $url, $page['PageId'], 
										$primaryMenuCount, $site['SiteId'], $userId);
										
						$primaryMenuCount++;
						
					}
					
					// build the footer menu
					if($footerMenu == true){
						MenuItem::Add($name, '', 'footer', $url, $page['PageId'], 
										$footerMenuCount, $site['SiteId'], $userId);
										
						$footerMenuCount++;
					}
    			
    			}
    			
    		}
    		else{
	    		return new Tonic\Response(Tonic\Response::BADREQUEST);
    		}
            
    		// publishes a theme for a site
    		Publish::PublishTheme($site, $theme);
    		
    		// publish the site
    		Publish::PublishSite($site['SiteId']);
    		
    		// create a locale directory
			$locales_dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales';
			
			// create locales directory if it does not exist
			if(!file_exists($locales_dir)){
				mkdir($locales_dir, 0755, true);	
			}
			
			// set directory for locale
			$locale_dir = $locales_dir.'/'.$site['Language'].'/';
			
			// make the locale dir if it does not exist
			if(!file_exists($locale_dir)){
				mkdir($locale_dir, 0755, true);	
			}
			
			// set filename
			$filename = 'translation.json';
			
			// create a blank translation file
		   	Utilities::SaveContent($locale_dir, $filename, '{}');
    		
    		// send welcome email
    		if(SEND_WELCOME_EMAIL == true && $email != ''){
    		
	    		$to = $email;
	    		$from = REPLY_TO;
	    		$fromName = REPLY_TO_NAME;
	    		$subject = BRAND.': Welcome to '.BRAND;
	    		$file = APP_LOCATION.'/emails/new-user.html';
	    		
	    		// create strings to replace
	    		$loginUrl = APP_URL.'/#/login/'.$site['FriendlyId'];
	    		$newSiteUrl = APP_URL.'/sites/'.$site['FriendlyId'];
	    		
	    		$replace = array(
	    			'{{brand-logo}}' => '<img src="'.BRAND_LOGO.'" style="max-height:50px">',
	    			'{{brand}}' => BRAND,
	    			'{{reply-to}}' => REPLY_TO,
	    			'{{new-site-url}}' => $newSiteUrl,
	    			'{{login-url}}' => $loginUrl
	    		);
	    		
	    		// send email from file
	    		Utilities::SendEmailFromFile($to, $from, $fromName, $subject, $replace, $file);
	    	}
            
            return new Tonic\Response(Tonic\Response::OK);
        }
        else{
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }

        
    }
}

/**
 * A protected API call to retrieve the current site
 * @uri /site/retrieve
 */
class SiteRetrieveResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
    
        // get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 

            $site = Site::GetBySiteId($token->SiteId);
            
            // set images URL
			if(FILES_ON_S3 == true){
				$bucket = $site['Bucket'];
				$imagesURL = str_replace('{{bucket}}', $bucket, S3_URL);
			}
			else{
				$imagesURL = '//'.$site['Domain'];
			}
			
			// set the ImagesURL
			$site['ImagesURL'] = $imagesURL.'/';
            
            // determine offset for timezone
            $zone = new DateTimeZone($site['TimeZone']);
			$now = new DateTime("now", $zone);
			
			$offset = $zone->getOffset($now);
			$offset_hours = round(($offset)/3600); 
			
			// set offset for site
			$site['Offset'] = $offset_hours;

            // return a json response
            $response = new Tonic\Response(Tonic\Response::OK);
            $response->contentType = 'application/json';
            $response->body = json_encode($site);

            return $response;
        }
        else{
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
}

/**
 * A protected API call to publish the site
 * @uri /site/publish
 */
class SitePublishResource extends Tonic\Resource {

    /**
     * @method GET
     */
    function get() {
        
        // get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 

            Publish::PublishSite($token->SiteId);

            $response = new Tonic\Response(Tonic\Response::OK);
       
            return $response;
        }
        else{
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
}

/**
 * A protected API call to publish the site
 * @uri /site/deploy
 */
class SiteDeployResource extends Tonic\Resource {

    /**
     * @method GET
     */
    function get() {
        
        // get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 

            S3::DeploySite($token->SiteId);

            $response = new Tonic\Response(Tonic\Response::OK);
       
            return $response;
        }
        else{
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
}

/**
 * A protected API call to view, edit, and delete a site
 * @uri /site/remove
 */
class SiteRemoveResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function remove() {

        // get an authuser
        $authUser = new AuthUser();

        if(isset($authUser->UserId)){ // check if authorized

			if($authUser->IsSuperAdmin == 1){
	            parse_str($this->request->data, $request); // parse request
	
	            $siteId = $request['siteId'];
	            
	            $site = Site::GetBySiteId($siteId);
	            
	            $directory = '../sites/'.$site['FriendlyId'];
	            
	            // Get the directory name
				$oldname = '../sites/'.$site['FriendlyId'];
				
				// Replace any special chars with your choice
				$newname = '../sites/'.$site['FriendlyId'].'-removed';
				
				if(file_exists($oldname)){
					// Renames the directory
					rename($oldname, $newname);
				}
	
				// remove site from DB
				Site::Remove($siteId);
	
	            return new Tonic\Response(Tonic\Response::OK);
            }
            else{ // unauthorized access
	            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
            }
        
        } 
        else{ // unauthorized access
			return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }

        return new Tonic\Response(Tonic\Response::NOTIMPLEMENTED);
    }

}


/**
 * A protected API call to view, edit, and delete a site
 * @uri /site/save
 */
class SiteSaveResource extends Tonic\Resource {


    /**
     * @method POST
     */
    function post() {

        // get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 

            parse_str($this->request->data, $request); // parse request

            $name = $request['name'];
            $domain = $request['domain'];
            $primaryEmail = $request['primaryEmail'];
            $timeZone = $request['timeZone'];
            $language = $request['language'];
            
            $currency = $request['currency'];
            $showCart = $request['showCart'];
            
            $showSettings = $request['showSettings'];
            $showLanguages = $request['showLanguages'];
            $showLogin = $request['showLogin'];
            
            $urlMode = $request['urlMode'];
            
            $weightUnit = $request['weightUnit'];
            $shippingCalculation = $request['shippingCalculation'];
            $shippingRate = $request['shippingRate'];
            $shippingTiers = $request['shippingTiers'];
            $taxRate = $request['taxRate'];
            $payPalId = $request['payPalId'];
            $payPalUseSandbox = $request['payPalUseSandbox'];
            
            $welcomeEmail = $request['welcomeEmail']; 
            $receiptEmail = $request['receiptEmail'];
			$isSMTP = $request['isSMTP']; 
			$SMTPHost = $request['SMTPHost'];
			$SMTPAuth = $request['SMTPAuth'];
			$SMTPUsername = $request['SMTPUsername'];
			$SMTPPassword = $request['SMTPPassword']; 
			$SMTPSecure = $request['SMTPSecure'];
            
            $formPublicId = $request['formPublicId'];
            $formPrivateId = $request['formPrivateId'];
            
            $SMTPPasswordIV = '';
            
            // encyrpt password, #ref: http://stackoverflow.com/questions/10916284/how-to-encrypt-decrypt-data-in-php
            if($SMTPPassword != '' && $SMTPPassword != 'temppassword'){
	            
	            // encrypt password
				$key_size = mcrypt_get_key_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
				$encryption_key = SMTPENC_KEY;
				
				// create iv
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CFB);
				$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM); // 16 bytes output
				
				// encrypt password
				$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encryption_key, $SMTPPassword, MCRYPT_MODE_CFB, $iv);
	            
	            // set password to encrypted password
	            $SMTPPasswordIV = base64_encode($iv);
	            $SMTPPassword = base64_encode($encrypted);
	            
	            // edit SMTP password
	            Site::EditSMTPPassword($token->SiteId, $SMTPPassword, $SMTPPasswordIV);
            }

			// edit site
            Site::Edit($token->SiteId, $name, $domain, $primaryEmail, $timeZone, $language, 
            	$showCart, $showSettings, $showLanguages, $showLogin, $urlMode,
            	$currency, $weightUnit, $shippingCalculation, $shippingRate, $shippingTiers, 
            	$taxRate, $payPalId, $payPalUseSandbox, 
            	$welcomeEmail, $receiptEmail,
				$isSMTP, $SMTPHost, $SMTPAuth, $SMTPUsername, $SMTPSecure,
            	$formPublicId, $formPrivateId);
            
            // republish site settings
            Publish::PublishSiteJSON($token->SiteId);
            	
            return new Tonic\Response(Tonic\Response::OK);
        
        } else{ // unauthorized access

            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }

        return new Tonic\Response(Tonic\Response::NOTIMPLEMENTED);
    }

}

/**
 * A protected API call to view, edit, and delete a site
 * @uri /site/branding/image
 */
class SiteBrandingResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function update() {

        // get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 

            parse_str($this->request->data, $request); // parse request

            $url = $request['url'];
            $type = $request['type'];
            
            $site = Site::GetBySiteId($token->SiteId);

			if($type == 'logo'){
            	Site::EditLogo($token->SiteId, $url);
            }
            else if($type == 'icon'){
	            Site::EditIcon($token->SiteId, $url);
	            
	            if(FILES_ON_S3 == true){
					$bucket = $site['Bucket'];
					$imagesURL = str_replace('{{bucket}}', $bucket, S3_URL);
					
					$source = $imagesURL.'/files/'.$url;
				}
				else{
					$source = SITES_LOCATION.'/'.$site['FriendlyId'].'/files/'.$url;
				}
				
	            // create the icon
	            $destination = SITES_LOCATION.'/'.$site['FriendlyId'].'/favicon.ico';
				
				$ico_lib = new PHP_ICO($source, array( array( 32, 32 ), array( 64, 64 ) ) );
				$ico_lib->save_ico( $destination );
				
            }
            
            // publish site JSON
            Publish::PublishSiteJSON($token->SiteId);

            return new Tonic\Response(Tonic\Response::OK);
        
        } else{ // unauthorized access

            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }

        return new Tonic\Response(Tonic\Response::NOTIMPLEMENTED);
    }

}

/**
 * A protected API call to view, edit, and delete a site
 * @uri /site/branding/icon/background
 */
class SiteBrandingIconBackgroundResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function update() {

        // get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 

            parse_str($this->request->data, $request); // parse request

            $color = $request['color'];

			Site::EditIconBg($token->SiteId, $color);
                        
            return new Tonic\Response(Tonic\Response::OK);
        
        } else{ // unauthorized access

            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }

        return new Tonic\Response(Tonic\Response::NOTIMPLEMENTED);
    }

}

/**
 * This class defines an example resource that is wired into the URI /example
 * @uri /site/list/all
 */
class SiteListAllResource extends Tonic\Resource {

    /**
     * @method GET
     */
    function get() {

        // get an authuser
        $authUser = new AuthUser();

        if(isset($authUser->UserId)){ // check if authorized

            // get sites
            $list = Site::GetSites();

            // return a json response
            $response = new Tonic\Response(Tonic\Response::OK);
            $response->contentType = 'application/json';
            $response->body = json_encode($list);

            return $response;

        }
        else{ // unauthorized access

            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }

    }

}

/**
 * API call to pay for a subscription
 * @uri /site/subscribe/stripe
 */
class SiteSubscribeStripeResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
    
    	// get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 

        	// parse request
        	parse_str($this->request->data, $request);
        	
        	$site = Site::GetBySiteId($token->SiteId);

			$siteId = $site['SiteId'];
			$email = $site['PrimaryEmail'];
			$status = 'Active';
        	$stripe_token = $request['token'];
			$plan = $request['plan'];
        	$domain = $request['domain'];
			$provider = 'Stripe';
    
			// set API key
			Stripe::setApiKey(STRIPE_SECRET_KEY);

            // create a new customer and subscribe them to the plan
            $customer = Stripe_Customer::create(
            	array(
					"card" => $stripe_token,
					"plan" => $plan,
					"email" => $email)
            );

			// get back the id and the end period for the plan
            $customerId = $customer->id;
            
            // get subscription information
            $subscription = $customer->subscriptions->data[0];
			
			$subscriptionId = $subscription->id;			
			$stripe_status = $subscription->status;
			$stripe_plan = $subscription->plan->id;
			$stripe_planname  = $subscription->plan->name;
			
			// subscribe to a plan
			Site::Subscribe($siteId, $status, $plan, $provider, $subscriptionId, $customerId);
			
			// send success email to user
			$to = $site['PrimaryEmail'];
    		$from = REPLY_TO;
    		$fromName = REPLY_TO_NAME;
    		$subject = BRAND.': Thank your for subscribing to '.BRAND;
    		$file = APP_LOCATION.'/emails/subscribe-success.html';
 
    		$replace = array(
    			'{{brand-logo}}' => '<img src="'.BRAND_LOGO.'" style="max-height:50px">',
    			'{{brand}}' => BRAND,
    			'{{reply-to}}' => REPLY_TO
    		);
    		
    		// send 
    		Utilities::SendEmailFromFile($to, $from, $fromName, $subject, $replace, $file);
    		
    		// send details email to admin
			$to = REPLY_TO;
    		$from = REPLY_TO;
    		$fromName = REPLY_TO_NAME;
    		$subject = BRAND.': New Subscriber';
    		$file = APP_LOCATION.'/emails/subscribe-details.html';
 
    		$replace = array(
    			'{{brand-logo}}' => '<img src="'.BRAND_LOGO.'" style="max-height:50px">',
    			'{{brand}}' => BRAND,
    			'{{reply-to}}' => REPLY_TO,
    			'{{domain}}' => $domain,
    			'{{siteid}}' => $site['SiteId'],
    			'{{friendlyid}}' => $site['FriendlyId'],
    			'{{provider}}' => $provider,
    			'{{customerid}}' => $customerId
    		);
    		
    		// send email from file
    		Utilities::SendEmailFromFile($to, $from, $fromName, $subject, $replace, $file);
        
            // return a json response
            return new Tonic\Response(Tonic\Response::OK); 
                
        }
        else{
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
}

/**
 * API call to pay for a subscription
 * @uri /site/unsubscribe/stripe
 */
class SiteUnsubscribeStripeResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
    
    	// get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 

        	// parse request
        	parse_str($this->request->data, $request);
        	
        	$site = Site::GetBySiteId($token->SiteId);

			$siteId = $site['SiteId'];
			$email = $site['PrimaryEmail'];
			$status = 'Unsubscribed';
			$plan = '';
        	$provider = '';
        	$subscriptionId = '';
        	$customerId = $site['CustomerId'];
    
			// set API key
			Stripe::setApiKey(STRIPE_SECRET_KEY);

            // retrieve customer
            $customer = Stripe_Customer::retrieve($site['CustomerId']);
			
			// unsubscribe
			$cu->subscriptions->retrieve($site['SubscriptionId'])->cancel();
            			
			// unsubscribe to a plan
			Site::Subscribe($siteId, $status, $plan, $provider, $subscriptionId, $customerId);
			
			// send success email to user
			$to = $site['PrimaryEmail'];
    		$from = REPLY_TO;
    		$fromName = REPLY_TO_NAME;
    		$subject = BRAND.': You have successfully unsubscribed to '.BRAND;
    		$file = APP_LOCATION.'/emails/unsubscribe-success.html';
 
    		$replace = array(
    			'{{brand-logo}}' => '<img src="'.BRAND_LOGO.'" style="max-height:50px">',
    			'{{brand}}' => BRAND,
    			'{{reply-to}}' => REPLY_TO
    		);
    		
    		// send 
    		Utilities::SendEmailFromFile($to, $from, $fromName, $subject, $replace, $file);
    		
    		// send details email to admin
			$to = REPLY_TO;
    		$from = REPLY_TO;
    		$fromName = REPLY_TO_NAME;
    		$subject = BRAND.': Unsubscribed';
    		$file = APP_LOCATION.'/emails/unsubscribe-details.html';
 
    		$replace = array(
    			'{{brand-logo}}' => '<img src="'.BRAND_LOGO.'" style="max-height:50px">',
    			'{{brand}}' => BRAND,
    			'{{reply-to}}' => REPLY_TO,
    			'{{domain}}' => $domain,
    			'{{siteid}}' => $site['SiteId'],
    			'{{friendlyid}}' => $site['FriendlyId'],
    			'{{provider}}' => $site['Provider'],
    			'{{customerid}}' => $site['CustomerId']
    		);
    		
    		// send email from file
    		Utilities::SendEmailFromFile($to, $from, $fromName, $subject, $replace, $file);
        
            // return a json response
            return new Tonic\Response(Tonic\Response::OK); 
                
        }
        else{
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
}


?>