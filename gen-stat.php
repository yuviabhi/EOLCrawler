<?php
/**
 * Generate statistics of the crawled data
 * @param providerID
 * @return prints statistics
 * 
 * @author abhisek
 */
require_once 'Database.php';

if ($argc > 1) {
    if (defined('STDIN')) {
        $providerID = $argv[1];
    }
} else {
    print("Enter <providerID> as 1st arguement");
    exit(0);
}

$write_into_file = FALSE;
echo "Want to write into file ? (y/n) ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
if(trim($line) == 'Y' or trim($line) == 'y'){
    $write_into_file = TRUE;
} 
fclose($handle);


function write2file($result, $header, $filename) {
	
	$fileHandle = fopen($filename, "w");
    fwrite ($fileHandle, implode(",",$header) . "\r\n");
    while ($row = pg_fetch_row($result)) {
        fwrite ($fileHandle, implode(",",$row) . "\r\n");
    }
    fclose ($fileHandle);
}


try {
    
    // Total Available Pages
	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "select distinct(page_id)
            from pages 
            where provider_id = ".$providerID;
	$result = pg_query($db_conn, $qry);

	if($result >0) {
		print_r("Total Available Pages : ".pg_num_rows($result).PHP_EOL);
		if($write_into_file==TRUE){
    		write2file($result, array('page_id'), "data/statistics/".$providerID."_pages_all.csv");
		}
	}


	// Pages with Initial details atleast
	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "select distinct(page_id)
            from pages_details  
            where provider_id = ".$providerID;
	$result = pg_query($db_conn, $qry);
	
	if($result >0) {
	    print_r("Pages with Initial details atleast : ".pg_num_rows($result).PHP_EOL);
	    if($write_into_file==TRUE){
	        write2file($result, array('page_id'), "data/statistics/".$providerID."_pages_with_init_details_atleast.csv");
	    }
	}
	
	// Pages with no details at all
	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "(
            select distinct(page_id)
            from pages 
            where provider_id = ".$providerID."
            )
            EXCEPT
            (
            select distinct(page_id)
            from pages_details  
            where provider_id = ".$providerID."
            )";
	$result = pg_query($db_conn, $qry);
	
	if($result >0) {
	    print_r("Pages with no details at all : ".pg_num_rows($result).PHP_EOL);
	    if($write_into_file==TRUE){
	        write2file($result, array('page_id'), "data/statistics/".$providerID."_pages_with_no_details_atall.csv");
	    }
	}

	//Pages with dataobjects
	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "select distinct(ob.page_id)
            from pages_dataobjects ob 
            where ob.provider_id = ".$providerID." 
            order by ob.page_id
            ";
	$result = pg_query($db_conn, $qry);

	if($result >0) {
		print_r("Pages with dataobjects : ".pg_num_rows($result).PHP_EOL);
		if($write_into_file==TRUE){
        	write2file($result, array('page_id'), "data/statistics/".$providerID."_pages_with_dataobjs.csv");
		}
	}



	// Pages with no dataobjects 
	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "(
            select distinct(pd.page_id)
            from pages_details pd 
            where pd.provider_id = ".$providerID." 
            order by pd.page_id
            )
            EXCEPT
            (
            select distinct(ob.page_id)
            from pages_dataobjects ob 
            where ob.provider_id = ".$providerID."  
            order by ob.page_id
            )";
	$result = pg_query($db_conn, $qry);

	if($result >0) {
		print_r("Pages with no dataobjects : ".pg_num_rows($result).PHP_EOL);
		if($write_into_file==TRUE){
    		write2file($result, array('page_id'), "data/statistics/".$providerID."_pages_with_no_dataobjs.csv");
		}
	}



	//	PAGE ID AND DATAOBJ COUNTS
	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "select obj.page_id , count(obj.page_id) as dataobj_count
            from pages_dataobjects obj
            where obj.provider_id = ".$providerID." 
            group by obj.page_id
            order by dataobj_count desc, page_id asc";
	$result = pg_query($db_conn, $qry);

	if($result >0) {
		print_r("PAGE ID AND DATAOBJ COUNTS : ".pg_num_rows($result).PHP_EOL);
		if($write_into_file==TRUE){
    		write2file($result, array('page_id', 'dataobj_count'), "data/statistics/".$providerID."_dataob_counts.csv");
		}
	}




}  catch (Exception $e) {
    
    echo '{"result":"FALSE","message":"Caught exception: ' .$e->getMessage() .'"}';
    
  
}



?>
