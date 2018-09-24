<?php

require_once 'Database.php';

if ($argc > 1) {
    if (defined('STDIN')) {
        $providerID = $argv[1];
    }
} else {
    print("Enter <providerID> as 1st arguement");
    exit();
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

	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "select distinct(page_id) from pages where provider_id = ".$providerID;
	$result = pg_query($db_conn, $qry);

	if($result >0) {
		print_r("Total ID : ".pg_num_rows($result).PHP_EOL);
		if($write_into_file==TRUE){
		// write into file
		write2file($result, array('page_id'), "data/statistics/".$providerID."_pages_all_check.csv");
		}
	}




	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "select distinct(p.page_id)
		from pages p 
		where p.provider_id = ".$providerID." and p.page_id in (
		select distinct(pd.page_id)
		from 
		pages_details pd inner join pages_dataobjects ob on pd.page_id = ob.page_id

		)";
	$result = pg_query($db_conn, $qry);

	if($result >0) {
		print_r("PAGES WITH DATAOBJS : ".pg_num_rows($result).PHP_EOL);
		if($write_into_file==TRUE){
		// write into file
		write2file($result, array('page_id'), "data/statistics/".$providerID."_pages_with_dataobjs_check.csv");
		}
	}



	
	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "(select distinct(p.page_id) 
		from pages p 
		where p.provider_id = ".$providerID." and p.page_id in (
		select distinct(pd.page_id)
		from 
		pages_details pd 
		))
		EXCEPT
		(select distinct(p.page_id) 
		from pages p 
		where p.provider_id = ".$providerID." and p.page_id in (
		select distinct(pd.page_id)
		from 
		pages_details pd inner join pages_dataobjects ob on pd.page_id = ob.page_id
		))";
	$result = pg_query($db_conn, $qry);

	if($result >0) {
		print_r("PAGES WITH NO DETAILS : ".pg_num_rows($result).PHP_EOL);
		if($write_into_file==TRUE){
		// write into file
		write2file($result, array('page_id'), "data/statistics/".$providerID."_pages_with_no_details_check.csv");
		}
	}




	
	$db = new Database();
	$db_conn = $db->get_connection();
	$qry = "select obj.page_id , count(obj.page_id) as dataobj_count
		from pages_dataobjects obj
		group by obj.page_id
		having obj.page_id in (
		select distinct(p.page_id) 
		from pages p 
		where p.provider_id = ".$providerID." and p.page_id in (
		select distinct(pd.page_id)
		from 
		pages_details pd inner join pages_dataobjects ob on pd.page_id = ob.page_id
		)
		) order by dataobj_count desc";
	$result = pg_query($db_conn, $qry);

	if($result >0) {
		print_r("PAGE ID AND DATAOBJ COUNTS : ".pg_num_rows($result).PHP_EOL);
		if($write_into_file==TRUE){
		// write into file
		write2file($result, array('page_id', 'dataobj_count'), "data/statistics/".$providerID."_dataob_counts_check.csv");
		}
	}




}  catch (Exception $e) {
	echo '{"result":"FALSE","message":"Caught exception: ' .$e->getMessage() . ' ~' . $DATA_DIR.$FILENAME . '"}';
}






?>
