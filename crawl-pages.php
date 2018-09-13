<?php
require_once 'Database.php';

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



$CHUNK_SIZE = 25;
// $data = array();
$db = new Database();

/* COUNT NO OF DISTINCT PAGES IN THE TABLE */
// $db_conn = $db->get_connection();
// $qry = 'SELECT COUNT(DISTINCT(page_id)) FROM pages;';
// $result = pg_query($db_conn, $qry);
// print_r(pg_fetch_array($result));
// $db->close_database();



/* FETCH ALL DISTINCT PAGES IN THE TABLE */
$db_conn = $db->get_connection();
$qry = 'SELECT DISTINCT(page_id) FROM pages ORDER BY page_id;';
$result = pg_query($db_conn, $qry);
$results = pg_fetch_all($result);

if (pg_num_rows($result) > 0) {     
    $chunks = array_chunk($results, $CHUNK_SIZE, true);
    foreach ($chunks as $chunk){
        $page_ids_comma_separated = '';
        foreach($chunk as $item){
            $page_id = $item['page_id'];
            $page_ids_comma_separated .= $page_id . ",";            
//             break;
        }
        $page_ids_comma_separated = substr($page_ids_comma_separated, 0, -1);

        /* CRAWL BATCH-WISE */
        $url = 'http://eol.org/api/pages/1.0.json?batch=true&id='.$page_ids_comma_separated.'&images_per_page=1&images_page=1&videos_per_page=1&videos_page=1&sounds_per_page=1&sounds_page=1&maps_per_page=1&maps_page=1&texts_per_page=2&texts_page=1&subjects=overview&licenses=all&details=true&common_names=true&synonyms=true&references=true&taxonomy=true&vetted=0&cache_ttl=&language=en';
        $pages_json = getData($url);
//         array_push($data, json_decode($pages_json, true));
        
        if (file_put_contents(__DIR__ . 'data/pages/pages.json', $pages_json . "\r\n", FILE_APPEND)) {
            echo "\nData saving for page-ids : ". $page_ids_comma_separated;
        } else {
            echo "\nError saving for page-ids : ". $page_ids_comma_separated;
        }
            
        
        
        
//         break;
            
    }
        
//     print_r(json_encode($data));
            

    
} else  {
    echo ("No result found in the table\n");
}
$db->close_database();


// print_r(json_encode($data));
?>