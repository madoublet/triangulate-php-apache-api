<?php

// Site model
class Site{
	
	// adds a Site
	public static function Add($domain, $name, $friendlyId, $logoUrl, $theme, $primaryEmail, $timeZone, $language){
        
        try{
            
        	$db = DB::get();
		
    		$siteId = uniqid();
    		$analyticsId = '';
    		$facebookAppId = '';
    		
    		$type = 'Non-Subscription';
  
    		$timestamp = gmdate("Y-m-d H:i:s", time());

            $q = "INSERT INTO Sites (SiteId, FriendlyId, Domain, Name, LogoUrl, Theme, PrimaryEmail, TimeZone, Language, Created) 
    			    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $siteId);
            $s->bindParam(2, $friendlyId);
            $s->bindParam(3, $domain);
            $s->bindParam(4, $name);
            $s->bindParam(5, $logoUrl);
            $s->bindParam(6, $theme);
            $s->bindParam(7, $primaryEmail);
            $s->bindParam(8, $timeZone);
            $s->bindParam(9, $language);
            $s->bindParam(10, $timestamp);
            
            $s->execute();
            
            return array(
                'SiteId' => $siteId,
                'FriendlyId' => $friendlyId,
                'Domain' => $domain,
                'Name' => $name,
                'LogoUrl' => $logoUrl,
                'Theme' => $theme,
                'PrimaryEmail' => $primaryEmail,
                'TimeZone' => $timeZone,
                'Language' => $language,
                'Created' => $timestamp
                );
                
        } catch(PDOException $e){
            die('[Site::Add] PDO Error: '.$e->getMessage());
        }
	}
	
	
	// edits the site information
	public static function Edit($siteId, $name, $domain, $primaryEmail, $timeZone, $language, $currency, $weightUnit, $shippingCalculation, $shippingRate, $shippingTiers, $taxRate, $payPalId, $payPalUseSandbox, $formPublicId, $formPrivateId){

		try{
            
            $db = DB::get();
            
            $q = "UPDATE Sites SET 
            		Name= ?, 
                    Domain= ?, 
        			PrimaryEmail = ?,
        			TimeZone = ?,
        			Language = ?,
        			Currency = ?,
        			WeightUnit = ?,
        			ShippingCalculation = ?, 
        			ShippingRate = ?,
        			ShippingTiers = ?,
        			TaxRate = ?,
        			PayPalId = ?,
        			PayPalUseSandbox = ?,
            		FormPublicId=?,
            		FormPrivateId=?
        			WHERE SiteId = ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $name);
            $s->bindParam(2, $domain);
            $s->bindParam(3, $primaryEmail);
            $s->bindParam(4, $timeZone);
            $s->bindParam(5, $language);
            $s->bindParam(6, $currency);
            $s->bindParam(7, $weightUnit);
            $s->bindParam(8, $shippingCalculation);
            $s->bindValue(9, strval($shippingRate), PDO::PARAM_STR);
            $s->bindParam(10, $shippingTiers);
            $s->bindParam(11, $taxRate);
            $s->bindParam(12, $payPalId);
            $s->bindParam(13, $payPalUseSandbox);
            $s->bindParam(14, $formPublicId);
            $s->bindParam(15, $formPrivateId);
            $s->bindParam(16, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::Edit] PDO Error: '.$e->getMessage());
        }
        
	}    
	
    // edits the theme
    public static function EditTheme($siteId, $theme){
        
        try{
            
            $db = DB::get();
            
            $q = "UPDATE Sites SET 
                	Theme= ?
                	WHERE SiteId = ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $theme);
            $s->bindParam(2, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::EditTheme] PDO Error: '.$e->getMessage());
        }
        
	}
    
    // edits the logo
    public static function EditLogo($siteId, $logoUrl){

        try{
            
            $db = DB::get();
            
            $q = "UPDATE Sites SET 
                    LogoUrl= ?
                    WHERE SiteId = ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $logoUrl);
            $s->bindParam(2, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::EditLogo] PDO Error: '.$e->getMessage());
        }
        
	}
	
	// edits the icon
    public static function EditIcon($siteId, $iconUrl){

        try{
            
            $db = DB::get();
            
            $q = "UPDATE Sites SET 
                    IconUrl= ?
                    WHERE SiteId = ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $iconUrl);
            $s->bindParam(2, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::EditIcon] PDO Error: '.$e->getMessage());
        }
        
	}
	
	// edits the icon bg
    public static function EditIconBg($siteId, $iconBg){

        try{
            
            $db = DB::get();
            
            $q = "UPDATE Sites SET 
                    IconBg= ?
                    WHERE SiteId = ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $iconBg);
            $s->bindParam(2, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::EditIconBg] PDO Error: '.$e->getMessage());
        }
        
	}
	
	// determines whether a friendlyId is unique
	public static function IsFriendlyIdUnique($friendlyId){

        try{

            $db = DB::get();
    
    		$count = 0;
    	
    		$q ="SELECT Count(*) as Count FROM Sites where FriendlyId = ?";
    
        	$s = $db->prepare($q);
            $s->bindParam(1, $friendlyId);
            
    		$s->execute();
    
    		$count = $s->fetchColumn();
    
    		if($count==0){
    			return true;
    		}
    		else{
    			return false;
    		}
            
        } catch(PDOException $e){
            die('[Site::IsFriendlyIdUnique] PDO Error: '.$e->getMessage());
        } 
        
	}

	// set last login
	public static function SetLastLogin($siteId){
        
        try{
            
            $db = DB::get();
            
            $timestamp = gmdate("Y-m-d H:i:s", time());
            
            $q = "UPDATE Sites SET LastLogin = ? WHERE SiteId= ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $timestamp);
            $s->bindParam(2, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::SetLastLogin] PDO Error: '.$e->getMessage());
        }
	}
	
	// update customer
	public static function EditCustomer($siteId, $customerId){
        
        try{
        
            $db = DB::get();
            
            $q = "UPDATE Sites SET Type = 'Subscription', CustomerId = ? WHERE SiteId= ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $customerId);
            $s->bindParam(2, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::EditCustomer] PDO Error: '.$e->getMessage());
        }
	}
		
	// update type
	public static function EditType($siteId, $type){
        
        try{
        
            $db = DB::get();
            
            $timestamp = gmdate("Y-m-d H:i:s", time());
            
            $q = "UPDATE Sites SET Type = ? WHERE SiteId= ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $type);
            $s->bindParam(2, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::UpdateStatus] PDO Error: '.$e->getMessage());
        }
	}
	
	// gets all sites
	public static function GetSites(){
		
        try{
            $db = DB::get();
            
            $q = "SELECT SiteId, FriendlyId, Domain, Name, LogoUrl, IconUrl, IconBg, Theme,
    						PrimaryEmail, TimeZone, Language, Currency, WeightUnit, 
							ShippingCalculation, ShippingRate, ShippingTiers, TaxRate, 
							PayPalId, PayPalUseSandbox, PayPalLogoUrl,
							FormPublicId, FormPrivateId,
							LastLogin, CustomerId, Created
							FROM Sites ORDER BY Name ASC";
                    
            $s = $db->prepare($q);
            
            $s->execute();
            
            $arr = array();
            
        	while($row = $s->fetch(PDO::FETCH_ASSOC)) {  
                array_push($arr, $row);
            } 
            
            return $arr;
        
		} catch(PDOException $e){
            die('[Site::GetSites] PDO Error: '.$e->getMessage());
        }   
	}
	
	// gets all domains
	public static function GetDomains(){
		
        try{
            $db = DB::get();
            
            $q = "SELECT Domain FROM Sites";
                    
            $s = $db->prepare($q);
            
            $s->execute();
            
            $arr = array();
            
        	while($row = $s->fetch(PDO::FETCH_ASSOC)) { 
        		$domain = 'http://'.$row['Domain'];
        		$www = 'http://www.'.$row['Domain'];
        	
                array_push($arr, $domain);
                array_push($arr, $www);
            } 
            
            return $arr;
        
		} catch(PDOException $e){
            die('[Site::GetDomains] PDO Error: '.$e->getMessage());
        }   
	}
	
	// removes a site
	public static function Remove($siteId){
		
        try{
            
            $db = DB::get();
            
            $q = "DELETE FROM Sites WHERE SiteId = ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $siteId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[Site::Remove] PDO Error: '.$e->getMessage());
        }
        
	}
	
	// Gets a site for a specific domain name
	public static function GetByDomain($domain){
		 
        try{
        
    		$db = DB::get();
            
            $q = "SELECT SiteId, FriendlyId, Domain, Name, LogoUrl, IconUrl, IconBg, Theme,
    						PrimaryEmail, TimeZone, Language, Currency, WeightUnit, 
							ShippingCalculation, ShippingRate, ShippingTiers, TaxRate, 
							PayPalId, PayPalUseSandbox,
							FormPrivateId, FormPublicId,
							SubscriptionId, CustomerId,
							LastLogin, Created		
    						FROM Sites WHERE Domain = ?";
                    
            $s = $db->prepare($q);
            $s->bindParam(1, $domain);
            
            $s->execute();
            
            $row = $s->fetch(PDO::FETCH_ASSOC);        
    
    		if($row){
    			return $row;
    		}
        
        } catch(PDOException $e){
            die('[Site::GetByDomain] PDO Error: '.$e->getMessage());
        }
        
	}
	
	// Gets a site for a given friendlyId
	public static function GetByFriendlyId($friendlyId){
		
        try{
        
        	$db = DB::get();
            
            $q = "SELECT SiteId, FriendlyId, Domain, Name, LogoUrl, IconUrl, IconBg, Theme,
    						PrimaryEmail, TimeZone, Language, Currency, WeightUnit, 
							ShippingCalculation, ShippingRate, ShippingTiers, TaxRate, 
							PayPalId, PayPalUseSandbox,
							FormPrivateId, FormPublicId,
							SubscriptionId, CustomerId,
							LastLogin, Created
							FROM Sites WHERE FriendlyId = ?";
                    
            $s = $db->prepare($q);
            $s->bindParam(1, $friendlyId);
            
            $s->execute();
            
            $row = $s->fetch(PDO::FETCH_ASSOC);        
    
    		if($row){
    			return $row;
    		}
        
        } catch(PDOException $e){
            die('[Site::GetByFriendlyId] PDO Error: '.$e->getMessage());
        }
        
	}	
	
	// Gets a site for a given SiteId
	public static function GetBySiteId($siteId){
		
        try{
        
            $db = DB::get();
            
            $q = "SELECT SiteId, FriendlyId, Domain, Name, LogoUrl, IconUrl, IconBg, Theme,
    						PrimaryEmail, TimeZone, Language, Currency, WeightUnit, 
							ShippingCalculation, ShippingRate, ShippingTiers, TaxRate, 
							PayPalId, PayPalUseSandbox,
							FormPrivateId, FormPublicId,
							SubscriptionId, CustomerId,
							LastLogin, Created
							FROM Sites WHERE Siteid = ?";
                    
            $s = $db->prepare($q);
            $s->bindParam(1, $siteId);
            
            $s->execute();
            
            $row = $s->fetch(PDO::FETCH_ASSOC);        
    
    		if($row){
    			return $row;
    		}
        
        } catch(PDOException $e){
            die('[Site::GetBySiteId] PDO Error: '.$e->getMessage());
        }
        
	}
	
}

?>