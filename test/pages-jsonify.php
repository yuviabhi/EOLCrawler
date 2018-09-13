<?php

// $pages_json = file_get_contents('/pages.json');
$data = array();

$handle = fopen(__DIR__ . "/pages5.json", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        array_push($data, json_decode($line, true));
    }
    
    fclose($handle);
    if (file_put_contents(__DIR__ . '/pages-jsonify.json', json_encode($data))) {
        echo "\nData saved.";
    } else {
        echo "\nError saving.";
    }
} else {
    echo ("error opening the file.\n");
    
} 


?>