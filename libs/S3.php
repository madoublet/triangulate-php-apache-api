<?php 

// Wrapper for Amazon S3 utilities
class S3
{

	// creates a bucket on S3
	public static function CreateBucket($bucket){
	
		// create AWS client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => S3_KEY,
		    'secret' => S3_SECRET
		));
		
		// check to see if bucket exists
		$doesExist = $client->doesBucketExist($bucket);
		
		// create a bucket for the site if it does not exists
		if($doesExist == false){
		
			// #ref: http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_createBucket
			$result = $client->createBucket(array(
			    'Bucket' => $bucket,
			    'ACL'	 => 'public-read'		
			));
			
			// enable hosting for the bucket
			$result = $client->putBucketWebsite(array(
			    // Bucket is required
			    'Bucket' => $bucket,
			    'ErrorDocument' => array(
			        // Key is required
			        'Key' => '#/page/error',
			    ),
			    'IndexDocument' => array(
			        // Suffix is required
			        'Suffix' => 'index.html',
			    )));
			    
		}
		
		return true;
		
	}
	
	// saves a file to S3
	public static function SaveFile($site, $contentType, $filename, $file, $meta = array()){
		
		// create AWS client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => S3_KEY,
		    'secret' => S3_SECRET
		));
		
		$bucket = $site['Bucket'];
		
		// create a bucket if it doesn't already exist
		S3::CreateBucket($bucket);
		
		$result = $client->putObject(array(
		    'Bucket'       => $bucket,
		    'Key'          => 'files/'.$filename,
		    'Body'   	   => file_get_contents($file),
		    'ContentType'  => $contentType,
		    'ACL'          => 'public-read',
		    'StorageClass' => 'REDUCED_REDUNDANCY',
		    'Metadata'	   => $meta
		));
			
	}
	
	// saves a file to S3
	public static function RemoveFile($site, $filename){
		
		// create AWS client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => S3_KEY,
		    'secret' => S3_SECRET
		));
		
		$bucket = $site['Bucket'];
		
		// remove file
    	$result = $client->deleteObject(array(
		    'Bucket' => $bucket,
		    'Key' => 'files/'.$filename
		));
		
		// remove thumb
		$result = $client->deleteObject(array(
		    'Bucket' => $bucket,
		    'Key' => 'files/thumbs/'.$filename
		));
			
	}
	
	// saves contents to S3
	public static function SaveContents($site, $contentType, $filename, $contents, $meta = array()){
		
		// create AWS client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => S3_KEY,
		    'secret' => S3_SECRET
		));
		
		$bucket = $site['Bucket'];
		
		// create a bucket if it doesn't already exist
		S3::CreateBucket($bucket);
		
		$result = $client->putObject(array(
		    'Bucket'       => $bucket,
		    'Key'          => 'files/'.$filename,
		    'Body'   	   => $contents,
		    'ContentType'  => $contentType,
		    'ACL'          => 'public-read',
		    'StorageClass' => 'REDUCED_REDUNDANCY',
		    'Metadata'	   => $meta
		));
			
	}
	
	// lists files on S3
	public static function ListFiles($site, $imagesOnly = false){
		
		$arr = array();
		
		// create AWS client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => S3_KEY,
		    'secret' => S3_SECRET
		));
		
		$bucket = $site['Bucket'];
		
		$prefix = 'files/';
	    $url = str_replace('{{bucket}}', $bucket, S3_URL);
		
		// list objects in a bucket
		$iterator = $client->getIterator('ListObjects', array(
		    'Bucket' => $bucket,
		    'Prefix' => $prefix
		));
		
		
		foreach ($iterator as $object) {
			$filename = $object['Key'];
			$size = $object['Size'];
			
			$headers = $client->headObject(array(
				'Bucket' => $bucket,
				'Key' => $object['Key']
			));
			
			$headers = $headers->toArray();
			
			// defaults
			$width = 0;
			$height = 0;
			
			// get width and height from head
			if($headers !== NULL){
				if(isset($headers['Metadata']['width'])){
					$width = $headers['Metadata']['width'];
				}
				
				if(isset($headers['Metadata']['height'])){
					$height = $headers['Metadata']['height'];
				}
			}
			
			
			$filename = str_replace($prefix, '', $filename);
			
			// get extension
			$parts = explode(".", $filename); 
    		$ext = end($parts); // get extension
    		$ext = strtolower($ext); // convert to lowercase
    		
    		// init
    		$file = array();
			
			// exclude thumbs and empty directories
			if(strpos($filename, 'thumbs/') === FALSE && $filename !== ''){
			
				// determine whether the file is an image
				if($ext=='png' || $ext=='jpg' || $ext=='gif' || $ext == 'svg'){ // upload image
	            	$isImage = true;
	            	
	            	$file = array(
		                'filename' => $filename,
		                'fullUrl' => $url.'/files/'.$filename,
		                'thumbUrl' => $url.'/files/thumbs/'.$filename,
		                'extension' => $ext,
		                'isImage' => $isImage,
		                'size' => round(($size / 1024 / 1024), 2),
		                'width' => $width,
		                'height' => $height
		            );
		            
		            // push file to array
					array_push($arr, $file);
	    		}
	    		else{ 
	    		
	    			// list file if it is allowed
	    			if($imagesOnly == false){
	    		
						$isImage = false;
						
						$file = array(
			                'filename' => $filename,
			                'fullUrl' => $url.'/files/'.$filename,
			                'thumbUrl' => NULL,
			                'extension' => $ext,
			                'isImage' => $isImage,
							'size' => $size,
							'width' => NULL,
							'height' => NULL
			            );
			            
			            // push file to array
						array_push($arr, $file);
					}
					
	    		}
	    	
    		}
		
			
		}
		
		return $arr;
	}
	
	// gets the size in MB of files stored in /file
	public static function RetrieveFilesSize($site, $imagesOnly = false){
		
		$arr = array();
		
		// create AWS client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => S3_KEY,
		    'secret' => S3_SECRET
		));
		
		$bucket = $site['Bucket'];
		
		$prefix = 'files/';
	    $url = str_replace('{{bucket}}', $bucket, S3_URL);
		
		// list objects in a bucket
		$iterator = $client->getIterator('ListObjects', array(
		    'Bucket' => $bucket,
		    'Prefix' => $prefix
		));
		
		// totalsize
		$total_size = 0;
		
		// walk through objects
		foreach ($iterator as $object) {
			
			$filename = $object['Key'];
			$size = $object['Size'];
			
			$filename = str_replace($prefix, '', $filename);
			
    		// init
    		$file = array();
			
			// exclude thumbs and empty directories
			if(strpos($filename, 'thumbs/') === FALSE && $filename !== ''){
			
				$total_size += $size;
	    	
    		}
		
			
		}
		
		return round(($total_size / 1024 / 1024), 2);
	}
	
	// deploys the site to Amazon S3
	public static function DeploySite($siteId){
		
		// get a reference to the site
		$site = Site::GetBySiteId($siteId);
		
		// create AWS client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => S3_KEY,
		    'secret' => S3_SECRET
		));
		
		$bucket = $site['Bucket'];
		$bucket_www = 'www.'.$site['Bucket'];
		
		// create a bucket if it doesn't already exist
		S3::CreateBucket($bucket);
		
		// set local director
		$local_dir = SITES_LOCATION.'/'.$site['FriendlyId'];
		
		// prefix
		$keyPrefix = '';
		
		// set permissions
		$options = array(
		    'params'      => array('ACL' => 'public-read'),
		    'concurrency' => 20,
		    'debug'       => true
		);
		
		// sync folders, #ref: http://blogs.aws.amazon.com/php/post/Tx2W9JAA7RXVOXA/Syncing-Data-with-Amazon-S3
		$client->uploadDirectory($local_dir, $bucket, $keyPrefix, $options);
		
		// get json for the site
		$json = json_encode(Publish::CreateSiteJSON($site, 'S3'));
		
		// deploy an updated site.json
		$result = $client->putObject(array(
		    'Bucket'       => $bucket,
		    'Key'          => 'data/site.json',
		    'Body'   	   => $json,
		    'ContentType'  => 'application/json',
		    'ACL'          => 'public-read',
		    'StorageClass' => 'REDUCED_REDUNDANCY'
		));
		
		/*
		// #support for S3 ANAME   
		// #ref: http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_createBucket
		$result = $client->createBucket(array(
		    'Bucket' => $bucket_www,
		    'ACL'	 => 'public-read'		
		));
		
		// enable hosting for the bucket
		$result = $client->putBucketWebsite(array(
		    // Bucket is required
		    'Bucket' => $bucket_www,
		    'RedirectAllRequestsTo' => array(
		        'HostName' => $bucket
		    )));
		*/
		
	}
	
	// deploys the directory to Amazon S3
	public static function DeployDirectory($site, $local_dir, $keyPrefix){
		
		// create AWS client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => S3_KEY,
		    'secret' => S3_SECRET
		));
		
		$bucket = $site['Bucket'];
		
		// create a bucket if it doesn't already exist
		S3::CreateBucket($bucket);
		
		// set permissions
		$options = array(
		    'params'      => array('ACL' => 'public-read'),
		    'concurrency' => 20,
		    'debug'       => true
		);
		
		// sync folders, #ref: http://blogs.aws.amazon.com/php/post/Tx2W9JAA7RXVOXA/Syncing-Data-with-Amazon-S3
		$client->uploadDirectory($local_dir, $bucket, $keyPrefix, $options);
	
	}
	
}
