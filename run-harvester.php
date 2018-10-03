<?php

require_once 'Database.php';
require_once 'eol-utils.php';


if ($argc > 1) {
    if (defined('STDIN')) {
        $providerID = $argv[1];
    }
} else {
    print("Enter <providerID> as 1st arguement");
    exit();
}

$DATA_DIR = __DIR__ . '/data/pages/providerid_'. $providerID. '/';

if(!file_exists($DATA_DIR)) {
	print('No files available');
	exit();
}



$dir_iter = new DirectoryIterator($DATA_DIR);
$FILENAMES = array();
foreach ($dir_iter as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $F = $fileinfo->getFilename();
        array_push($FILENAMES, $F);
    }
}


natsort($FILENAMES);
$FILENAMES = array_reverse($FILENAMES, false);
//print_r($FILENAMES);


foreach ($FILENAMES as $FILENAME) {
    print($providerID. "->" .$FILENAME."\n");
    $JSON_DATA = file_get_contents($DATA_DIR.$FILENAME);
	//print_r($JSON_DATA);
	
	
	try {
	
		$data_chunk = json_decode($JSON_DATA, true);
		//print_r($data_chunk);
	
		foreach($data_chunk as $key => $value) {
		
		$page_id = sanitize($key);
		$scientificName = sanitize((isset($value['scientificName']) ? $value['scientificName'] : ''));
		$richness_score = sanitize((isset($value['richness_score']) ? $value['richness_score'] : ''));
		$synonyms = json_encode(sanitize((isset($value['synonyms']) ? $value['synonyms'] : '' )));
		$vernacularNames = json_encode(sanitize((isset($value['vernacularNames']) ? $value['vernacularNames'] : '')));
		$referencess = to_pg_array(sanitize((isset($value['references']) ? $value['references'] : '')));
		$taxonConcepts = json_encode(sanitize((isset($value['taxonConcepts']) ? $value['taxonConcepts'] : '')));
		
		/* INSERT INTO 'PAGES_DETAILS' TABLE */
        $db = new Database();
        $db_conn = $db->get_connection();
        /*$qry = 'INSERT INTO pages_details (page_id, scientificName, richness_score, synonyms, vernacularNames, referencess) 
        VALUES('.$page_id.', \''.$scientificName.'\' , '.$richness_score.', \''.$synonyms.'\', \''.$vernacularNames.'\', \''.$referencess.'\');';
        //print("\n\n". $qry. "\n\n");
        $result = pg_query($db_conn, $qry);
        */
        
        $query_params = array();
        if(isset($providerID)){
			$query_params["provider_id"] = $providerID;
		}
        if(isset($page_id)){
			$query_params["page_id"] = $page_id;
		}
		if(isset($scientificName)){
			$query_params["scientificname"] = $scientificName;
		}
		if(isset($richness_score)){
			$query_params["richness_score"] = $richness_score;
		}
		if(isset($synonyms)){
			$query_params["synonyms"] = $synonyms;
		}
		if(isset($vernacularNames)){
			$query_params["vernacularnames"] = $vernacularNames;
		}
		if(isset($referencess)){
			$query_params["referencess"] = $referencess;
		}
		if(isset($taxonConcepts)){
			$query_params["taxonconcepts"] = $taxonConcepts;
		}
		
		
		$status = pg_insert ( $db_conn, "pages_details", $query_params );
        
        if($status){
        	//print_r("SAVED :: pages_details \t\t ID : ".$page_id);
        } else {
        	print_r("NOT SAVED :: pages_details \t\t ID : ".$page_id);	        	
        }
        $db->close_database();
		
		
		
		
		
		// #####################################################
		
		
		
		
		$dataObjects = sanitize($value['dataObjects']);
		foreach ($dataObjects as $obj) {
			// print_r($obj);
			
			$query_params = array();
			if(isset($page_id)){
				$query_params["page_id"] = $page_id;
			}
			if(isset($providerID)){
				$query_params["provider_id"] = $providerID;
			}
			$unknown = array();
			foreach ($obj as $key => $value){
				// print_r ($key . ' => ' . $value."\n");
				
				if (sanitize((isset($value) ? $value : '')) != null) {
					switch($key) {					
						case 'identifier':
							$query_params["identifier"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'dataObjectVersionID':
							$query_params["dataobjectversionid"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'dataType':
							$query_params["datatype"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'dataSubtype':
							$query_params["datasubtype"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'vettedStatus':
							$query_params["vettedstatus"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'dataRating':
							$query_params["datarating"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'subject':
							$query_params["subject"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'mimeType':
							$query_params["mimetype"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'title':
							$query_params["title"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'language':
							$query_params["language"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'license':
							$query_params["license"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'rightsHolder':
							$query_params["rightsholder"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'bibliographicCitation':
							$query_params["bibliographiccitation"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'audience':
							$query_params["audience"] = to_pg_array(sanitize((isset($value) ? $value : '')));
							break;
						case 'source':
							$query_params["source"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'description':
							$query_params["description"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'agents':
							$query_params["agents"] = to_pg_array(sanitize((isset($value) ? $value : '')));
							break;
						case 'references':
							$query_params["referencess"] = to_pg_array(sanitize((isset($value) ? $value : '')));
							break;
						case 'created':
							$query_params["created"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'modified':
							$query_params["modified"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'mediaURL':
							$query_params["mediaurl"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'eolMediaURL':
							$query_params["eolmediaurl"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'eolThumbnailURL':
							$query_params["eolthumbnailurl"] = sanitize((isset($value) ? $value : ''));
							break;
						case 'location':
							$query_params["location"] = sanitize((isset($value) ? $value : ''));
							break;						
						default:
							array_push($unknown, sanitize((isset($value) ? $key.':'.json_encode($value) : '')));
							break;						
					}
				}
				
									
			}
			$query_params["unknown_"] = json_encode($unknown);
			
			/* INSERT INTO 'PAGES_DATAOBJECTS' TABLE */
	        $db = new Database();
	        $db_conn = $db->get_connection();
	        /*
	        $qry = 'INSERT INTO pages_dataobjects (page_id, identifier, dataobjectversionid, datatype, datasubtype, vettedstatus, datarating, subject, mimetype, title, language, license, rightsholder, bibliographiccitation, audience, source, description, agents, referencess, created, modified, mediaurl, eolmediaurl, eolthumbnailurl, location) 
	        VALUES('.$page_id.', \''.$identifier.'\' , '.$dataobjectversionid.', \''.$datatype.'\' , \''.$datasubtype.'\' , \''.$vettedstatus.'\' ,
	         '.$datarating.', \''.$subject.'\' , \''.$mimetype.'\' , \''.$title.'\' , \''.$language.'\' , \''.$license.'\' , \''.$rightsholder.'\' ,
	         \''.$bibliographiccitation.'\' , \''.$audience.'\', \''.$source.'\' , \''.$description.'\' , \''.$agents.'\', \''.$referencess.'\',
	         \''.$created.'\' , \''.$modified.'\' , \''.$mediaurl.'\' , \''.$eolmediaurl.'\' , \''.$eolthumbnailurl.'\' ,  \''.$location.'\' );';
	         
	        //print("\n\n". $qry. "\n\n");
	        $result = pg_query($db_conn, $qry);
	        */
	        
	        
			
			$status = pg_insert ( $db_conn, "pages_dataobjects", $query_params );

	        if($status){
	        	//print_r("\nSAVED :: pages_dataobjects \t ID : ".$page_id);
	        } else {
	        	print_r("\nNOT SAVED :: pages_dataobjects \t ID : ".$page_id);
	        }
	        
	        $db->close_database();
	        
	        //break; // for a single dataobject in dataObjects
			
		}
		
		
		//break; //for single row in a chunk
	}
	
	//break; //for a single file
	
	}  catch (Exception $e) {
		echo '{"result":"FALSE","message":"Caught exception: ' .$e->getMessage() . ' ~' . $DATA_DIR.$FILENAME . '"}';
	}
	
	
        
    
}

?>
