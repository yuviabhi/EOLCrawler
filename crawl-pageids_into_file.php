<?php
//require_once 'Database.php';



function getData($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, "http://172.16.2.30:8080");
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function crawl_recursively($taxonID, $providerID, $filename) {
    $url_hierarchy_entries = 'http://eol.org/api/hierarchy_entries/1.0/' . $taxonID . '.json';
    
    $path_save_api_hits = __DIR__ . "/data/hierarchies/".$providerID;

    $filename_api_hits = $path_save_api_hits."/".$taxonID.".json";
    if(!file_exists($filename_api_hits)){
    	$hierarchy_entries = json_decode(getData($url_hierarchy_entries));
    	file_put_contents($filename_api_hits, json_encode($hierarchy_entries));
    } else {
		$hierarchy_entries = json_decode(file_get_contents($filename_api_hits));
		
    }    
    
    
    $children = $hierarchy_entries->children;
    if(sizeof($children)==0){
        return ;
    }
    else {
        //recursion
        foreach ($children as $child) {
            $taxonID = $child->taxonID;
            $pageID = $child->taxonConceptID;
            $parentID = $child->parentNameUsageID;
            print_r($taxonID . ", " . $parentID . ", " . $pageID . ", " . $providerID . "\n");
            crawl_recursively($taxonID, $providerID, $filename);
            
            if(file_exists($filename)){
            	$line = $taxonID . ", " . $parentID . ", " . $pageID . ", " . $providerID;
				file_put_contents($filename, $line . PHP_EOL, FILE_APPEND);				
		    }
		    
            /* INSERT INTO PAGES IN THE TABLE */
            //$db = new Database();
            //$db_conn = $db->get_connection();
            //$qry = 'INSERT INTO pages(taxon_id, parent_id, page_id, provider_id) VALUES('.$taxonID.', '.$parentID.' , '.$pageID.', '.$providerID.' )';
            //$result = pg_query($db_conn, $qry);
            //$row = pg_fetch_row($result);
            //$db->close_database();
        }        
    }
    
}

// manual run
// crawl_recursively("62852883");



//$url_providers = 'http://eol.org/api/provider_hierarchies/1.0.json';
//$providers = json_decode(getData($url_providers));

//foreach ($providers as $p) {
    // $providerID = $p->id;
    // $label = $p->label;
    //print_r($providerID . " " . $label . "\n");

    if($argc>1) {
        if (defined('STDIN')) {
            $providerID = $argv[1];
        }    
    } else {
        print("Enter <providerID> as 1st arguement");
        exit();
    }
    
    $filename = __DIR__ . "/#page_ids_".$providerID.".csv";
    echo $filename.PHP_EOL; //exit(0);
    
    $url_hierarchy = 'http://eol.org/api/hierarchies/1.0/' . $providerID . '.json??&language=en';
    $path_save_api_hits = __DIR__ . "/data/hierarchies/".$providerID;
    if (!file_exists($path_save_api_hits)) {
            mkdir($path_save_api_hits, 0777, true);
        }
    $filename_api_hits = $path_save_api_hits."/".$providerID.".json";
    if(!file_exists($filename_api_hits)){
    	$hierarchy = json_decode(getData($url_hierarchy));
    	file_put_contents($filename_api_hits, json_encode($hierarchy));
    } else {
		$hierarchy = json_decode(file_get_contents($filename_api_hits));    
    }
    

    
    //print_r($hierarchy->title);
    $roots = $hierarchy->roots;
    
    foreach ($roots as $root) {
        $taxonID = $root->taxonID;
        $pageID = $root->taxonConceptID;
        $parentID = $root->parentNameUsageID;
        
        print_r($taxonID . ", " . $parentID . ", " . $pageID . ", " .$providerID ."\n");
                
    	$line = $taxonID . ", " . $parentID . ", " . $pageID . ", " . $providerID;
		file_put_contents($filename, $line . PHP_EOL, FILE_APPEND);
                
		crawl_recursively($taxonID, $providerID, $filename);        
        
        /* INSERT INTO PAGES IN THE TABLE */
        //$db = new Database();
        //$db_conn = $db->get_connection();
        //$qry = 'INSERT INTO pages(taxon_id, parent_id, page_id, provider_id) VALUES('.$taxonID.', '.$parentID.' , '.$pageID.', '.$providerID.' )';
        //$result = pg_query($db_conn, $qry);
        //if($result){
        //    crawl_recursively($taxonID, $providerID);
        //}
        //$db->close_database();
        
        // break;
        
        
    }
//}


?>
