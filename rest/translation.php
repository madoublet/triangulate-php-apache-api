<?php 

/**
 * Retrieves the default translation for the site
 * @uri /translation/retrieve
 */
class TranslationRetrieveResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
    
       	// get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 
			
			parse_str($this->request->data, $request); // parse request

			$locale = $request['locale'];
			
			// get a reference to the site, user
			$site = Site::GetBySiteId($token->SiteId);
            
            $file = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/'.$locale.'/translation.json';
            $json = '{}';
            
           	// retrieve default file if it exists, if not the translation is empty 
            if(file_exists($file)){
	            $json = file_get_contents($file);
	            
	            // initialize a blank file
	            if($json == ''){
		            $json = '{}';
	            }
            }
           
            // return a json response
            $response = new Tonic\Response(Tonic\Response::OK);
            $response->contentType = 'application/json';
            $response->body = $json;

            return $response;
        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
    
} 

/**
 * Retrieves the default translation for the site
 * @uri /translation/retrieve/default
 */
class TranslationRetrieveDefaultResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
    
       	// get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 
			
			// get a reference to the site, user
			$site = Site::GetBySiteId($token->SiteId);
            
            $file = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/'.$site['Language'].'/translation.json';
            $json = '{}';
            
           	// retrieve default file if it exists, if not the translation is empty 
            if(file_exists($file)){
	            $json = file_get_contents($file);
	            
	            // initialize a blank file
	            if($json == ''){
		            $json = '{}';
	            }
            }
           
            // return a json response
            $response = new Tonic\Response(Tonic\Response::OK);
            $response->contentType = 'application/json';
            $response->body = $json;

            return $response;
        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
    
}

/**
 * Adds a locale for the site
 * @uri /translation/add/locale
 */
class TranslationAddLocaleResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
    
       	// get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 
			
			// get a reference to the site, user
			$site = Site::GetBySiteId($token->SiteId);
			
			parse_str($this->request->data, $request); // parse request
		          
		    // get content      
			$locale = $request['locale'];
            
            // copy default file to new locale
            $src = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/'.$site['Language'].'/';
            $dest = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/'.$locale.'/';
            
            // create libs directory if it does not exist
			if(!file_exists($dest)){
				mkdir($dest, 0755, true);	
			}
			
			// copy libs directory
			if(file_exists($src)){
				Utilities::CopyDirectory($src, $dest);
			}
                      
            // return a json response
           	return new Tonic\Response(Tonic\Response::OK);

            return $response;
        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
    
}

/**
 * Removes a locale for the site
 * @uri /translation/remove/locale
 */
class TranslationRemoveLocaleResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
    
       	// get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 
			
			// get a reference to the site, user
			$site = Site::GetBySiteId($token->SiteId);
			
			parse_str($this->request->data, $request); // parse request
		          
		    // get content      
			$locale = $request['locale'];
            
            // copy default file to new locale
            $src = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/'.$locale.'/';
            
            Utilities::RemoveDirectory($src);
                                  
            // return a json response
           	return new Tonic\Response(Tonic\Response::OK);

            return $response;
        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
    
}


/**
 * Saves the default translation for the site
 * @uri /translation/save
 */
class TranslationSaveResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function post() {
    
       	// get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 
			
			// get a reference to the site, user
			$site = Site::GetBySiteId($token->SiteId);
            
            parse_str($this->request->data, $request); // parse request
		          
		    // get content      
			$content = $request['content'];
			
			// set locale as default language
			$locale = $site['Language'];
			
			// override if in request
			if(isset($request['locale'])){
				$locale = $request['locale'];
			}
			
			// make it pretty
			$json = json_decode($content);
			$content = json_encode($json, JSON_PRETTY_PRINT);
            
			// set directory an filename
			$dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/'.$locale.'/';
			$filename = 'translation.json';
           
           	// save content
		   	Utilities::SaveContent($dir, $filename, $content);
           
            // return a json response
           	return new Tonic\Response(Tonic\Response::OK);
        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
    
}

/**
 * Lists available translations
 * @uri /translation/list/locales
 */
class TranslationListResource extends Tonic\Resource {

    /**
     * @method GET
     */
    function get() {
    
       	// get token
		$token = Utilities::ValidateJWTToken(apache_request_headers());

		// check if token is not null
        if($token != NULL){ 
			
			// get a reference to the site, user
			$site = Site::GetBySiteId($token->SiteId);
            
			// set directory an filename
			$dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/';
			
			// array to store directories
			$list = array();
			
			if($handle = opendir($dir)){
			    $blacklist = array('.', '..');
			    while (false !== ($file = readdir($handle))) {
			        if (!in_array($file, $blacklist)) {
			            array_push($list, $file);
			        }
			    }
			    closedir($handle);
			}
           
            // return a json response
            $response = new Tonic\Response(Tonic\Response::OK);
            $response->contentType = 'application/json';
            $response->body = json_encode($list);

            return $response;
        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
    
}


?>