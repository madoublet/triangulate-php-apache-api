<?php

// SearchIndex DAO
class SearchIndex{
    
	// adds a page to the index
	public static function Add($pageId, $siteId, $language, $url, $name, $image, $isSecure, $h1s, $h2s, $h3s, $description, $content){
		
        try{
            
            $db = DB::get();
    	
    		$q = "INSERT INTO SearchIndex (PageId, SiteId, Language, Url, Name, Image, IsSecure, H1s, H2s, H3s, Description, Content) 
    			    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $s = $db->prepare($q);
            $s->bindParam(1, $pageId);
            $s->bindParam(2, $siteId);
            $s->bindParam(3, $language);
            $s->bindParam(4, $url);
            $s->bindParam(5, $name);
            $s->bindParam(6, $image);
            $s->bindParam(7, $isSecure);
            $s->bindParam(8, $h1s);
            $s->bindParam(9, $h2s);
            $s->bindParam(10, $h3s);
            $s->bindParam(11, $description);
            $s->bindParam(12, $content);
            
            $s->execute();
            
            return array(
                'PageId' => $pageId,
                'SiteId' => $siteId,
                'Language' => $language,
                'Url' => $url,
                'Name' => $name,
                'Image' => $image,
                'IsSecure' => $isSecure,
                'H1s' => $h1s,
                'H2s' => $h2s,
                'H3s' => $h3s,
                'Description' => $description,
                'Content' => $content
                );
                
        } catch(PDOException $e){
            die('[SearchIndex::Add] PDO Error: '.$e->getMessage());
        }
	}
	
	// searches the index
	public static function Search($siteId, $language, $term, $showSecure){
		
        try{

            $db = DB::get();
            
            $arr = explode(' ', $term);
            
            $s_term = '';
            
            foreach($arr as $val) {
	             $s_term .= '+'.$val.' ';
	        }
	        
	        $s_term = trim($s_term);
            
            /* #basic */
            if($showSecure == true){  // a logged in user can see all results
	            $q = "SELECT Name, Url, Description, Image FROM SearchIndex
						WHERE SiteId = ? AND Language = ? AND MATCH (Name, H1s, H2s, H3s, Description, Content) AGAINST (? IN BOOLEAN MODE)";

            }
            else{  // a non-logged in user can only see non-secured results
				$q = "SELECT Name, Url, Description, Image FROM SearchIndex
						WHERE SiteId = ? AND Language = ? AND IsSecure = 0 AND MATCH (Name, H1s, H2s, H3s, Description, Content) AGAINST (? IN BOOLEAN MODE)";
			}
				
        			
            $s = $db->prepare($q);
            $s->bindParam(1, $siteId);
            $s->bindParam(2, $language);
            $s->bindParam(3, $s_term);
            
            $s->execute();
            
            $arr = array();
            
            while($row = $s->fetch(PDO::FETCH_ASSOC)) {  
                array_push($arr, $row);
            } 
            
            return $arr;
        
		} catch(PDOException $e){
            die('[SearchIndex::SEARCH] PDO Error: '.$e->getMessage());
        } 
        
	}
	
	// removes index for a page
	public static function Remove($pageId){
		
        try{
            
            $db = DB::get();
            
            $q = "DELETE FROM SearchIndex WHERE PageId = ?";
     
            $s = $db->prepare($q);
            $s->bindParam(1, $pageId);
            
            $s->execute();
            
		} catch(PDOException $e){
            die('[SearchIndex::Remove] PDO Error: '.$e->getMessage());
        }
        
	}
	
	
}

?>