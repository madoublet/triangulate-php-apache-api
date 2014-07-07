<?php 
 

/**
 * Retrieves the default translation for the site
 * @uri /translation/retrieve
 */
class TranslationRetrieveResource extends Tonic\Resource {

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
            
            $file = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/'.$site['Language'].'/translation.json';
            $json = '{}';
            
           	// retrieve default file if it exists, if not the translation is empty 
            if(file_exists($file)){
	            $json = file_get_contents($file);
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
            
			// set directory an filename
            $dir = SITES_LOCATION.'/'.$site['FriendlyId'].'/locales/'.$site['Language'].'/';
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


?>