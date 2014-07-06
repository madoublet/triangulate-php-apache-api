<?php

/**
 * A public method to handle form submissions to Respond
 * @uri /form
 */
class FormResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function form() {

        // parse request
        parse_str($this->request->data, $request);

        $siteId = $request['siteId'];
        $pageId = $request['pageId'];
        $body = $request['body'];
    
        $site = Site::GetBySiteId($siteId);
        $page = Page::GetByPageId($pageId);

        if($site != null && $page != null){
            
            $subject = BRAND.': Form Submission ['.$site['Name'].': '.$page['Name'].']';
            
            $content =  '<h3>Site Information</h3>'.
                        '<table>'.
                        '<tr>'.
                        '<td style="padding: 5px 25px 5px 0;">Site:</td>'.
                        '<td style="padding: 5px 0">'.$site['Name'].'</td>'.
                        '</tr>'.
                         '<tr>'.
                        '<td style="padding: 5px 25px 5px 0;">Page:</td>'.
                        '<td style="padding: 5px 0">'.$page['Name'].'</td>'.
                        '</tr>'.
                        '</table>'.
                        '<h3>Form Details</h3>'.
                        $body;
            
            
            // send an email
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $headers .= 'From: ' . $site['PrimaryEmail'] . "\r\n" .
                		'Reply-To: ' . $site['PrimaryEmail'] . "\r\n";
            
            // sends the email
            $to = $site['PrimaryEmail'];
            $from = $site['PrimaryEmail'];
            $fromName = $site['Name'];
            
            Utilities::SendEmail($to, $from, $fromName, $subject, $content);
            
            // return a successful response (200)
            return new Tonic\Response(Tonic\Response::OK);
            
        } else{ // unauthorized access

            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
        
    }
}

/**
 * A public method to check reCaptcha field on form submissions to Respond
 * @uri /form/checkCaptcha
 */
class CheckCaptchaResource extends Tonic\Resource {

    /* Test if the text introduced in reCaptcha field is correct */
	/**
	 * @method POST
	 */
    function checkCaptcha() {
    
    	// parse request
    	parse_str($this->request->data, $request);

    	$siteId = $request['siteId'];
    	$pageUniqId = $request['pageId'];
    	
    	$recaptcha_challenge_field = $request['recaptcha_challenge_field'];
    	$recaptcha_response_field = $request['recaptcha_response_field'];

    	require_once('../libs/recaptchalib.php');
    	
    	$site = Site::GetBySiteId($siteId);
    	
    	$resp = recaptcha_check_answer ($site['FormPrivateId'],
    			$_SERVER["REMOTE_ADDR"],
    			$recaptcha_challenge_field,
    			$recaptcha_response_field);

    	$response = new Tonic\Response(Tonic\Response::OK);
    	$response->contentType = 'text/html';
    	if ($resp->is_valid) {
    		$response->body = 'OK';
    	} else {
    		$response->body = 'NOK';
    	}
    	return $response;
    }
}

?>